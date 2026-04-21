<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Services\OrderEmailService;
use App\Services\OrderNotificationService;
use App\Services\ZoneDetectionService;
use App\Models\ShippingZone;
// Importaciones de Mercado Pago SDK v3
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Client\Payment\PaymentClient;

class CheckoutController extends Controller
{
    public function index()
    {
        return view('checkout.index', [
            'shippingZones' => $this->shippingZones(),
        ]);
    }

    public function process(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'street_name'   => 'required|string|max:255',
            'street_number' => 'required|integer|min:1',
            'phone'         => 'required',
            'payment_method' => 'required|in:mercadopago,efectivo,transferencia',
            'shipping_zone' => ['nullable', 'string', Rule::in(array_keys($this->shippingZones()))],
        ]);

        // Componer dirección completa
        if (empty($request->address)) {
            $request->merge([
                'address' => trim($request->street_name . ' ' . $request->street_number),
            ]);
        }

        // Si el frontend no pudo setear la zona, intentamos detectarla del lado servidor.
        if (empty($request->shipping_zone)) {
            $detectedZone = app(ZoneDetectionService::class)->detect(
                $request->string('street_name')->toString(),
                (int) $request->input('street_number')
            );

            if ($detectedZone) {
                $request->merge(['shipping_zone' => $detectedZone]);
            }
        }

        $cart = session()->get('cart', []);
        \Log::info('Cart contents: ' . json_encode($cart));
        if (empty($cart)) return redirect()->back()->with('error', 'El carrito está vacío');

        $paymentMethod = $request->string('payment_method')->toString();
        $shippingZone = $request->string('shipping_zone')->toString();
        $shippingZones = $this->shippingZones();
        $shippingZoneData = $shippingZones[$shippingZone] ?? null;

        if (!$shippingZoneData) {
            return redirect()->back()->withErrors([
                'shipping_zone' => 'No pudimos detectar la zona con esa calle y altura. Revisá la dirección.',
            ])->withInput();
        }

        try {
            DB::beginTransaction();

            $productsSubtotal = 0;
            $itemsMP = []; // Array para Mercado Pago

            foreach ($cart as $id => $details) {
                $subtotal = $details['price'] * $details['quantity'];
                $productsSubtotal += $subtotal;

                // Preparar items para MP
                $itemsMP[] = [
                    "id" => $id,
                    "title" => $details['name'] ?? "Producto $id", // Asegúrate de tener el nombre en el carro
                    "quantity" => (int) $details['quantity'],
                    "unit_price" => (float) $details['price'],
                    "currency_id" => "ARS"
                ];
            }

            $shippingCost = (float) ($shippingZoneData['price'] ?? 0);
            $totalGeneral = $productsSubtotal + $shippingCost;

            if ($shippingCost > 0) {
                $itemsMP[] = [
                    'id' => 'shipping-' . $shippingZone,
                    'title' => 'Costo de envio - ' . ($shippingZoneData['label'] ?? 'Zona seleccionada'),
                    'quantity' => 1,
                    'unit_price' => $shippingCost,
                    'currency_id' => 'ARS',
                ];
            }

            // Crear la Orden
            $orderStatus = $paymentMethod === 'transferencia'
                ? 'pending_transfer'
                : 'pending';

            $order = Order::create([
                'user_id' => Auth::id(),
                'name' => $request->name,
                'email' => $request->email,
                'address' => $request->address,
                'phone' => $request->phone,
                'total' => $totalGeneral,
                'status' => $orderStatus,
                'payment_method' => $paymentMethod,
                'shipping_zone' => $shippingZone,
                'shipping_cost' => $shippingCost,
            ]);

            foreach ($cart as $id => $details) {
                OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $id,
                    'price'      => $details['price'],
                    'quantity'   => $details['quantity'],
                    'subtotal'   => $details['price'] * $details['quantity'],
                ]);
            }

            if ($paymentMethod !== 'mercadopago') {
                DB::commit();

                $order->loadMissing('items.product');

                app(OrderEmailService::class)->sendOrderConfirmation($order);

                try {
                    app(OrderNotificationService::class)->notifyNewOrder($order);
                } catch (\Throwable $e) {
                    \Log::error('Error notifying admin about offline payment order.', [
                        'order_id' => $order->id,
                        'payment_method' => $paymentMethod,
                        'error' => $e->getMessage(),
                    ]);
                }

                session()->forget('cart');
                $this->flashCheckoutSuccess($order);

                return redirect()->route('checkout.success');
            }

            $this->configureMercadoPago();

            $client = new PreferenceClient();

            $preference = $client->create([
                "items" => $itemsMP,
                "back_urls" => [
                    "success" => url(route('mercadopago.callback')),
                    "failure" => url(route('mercadopago.callback')),
                    "pending" => url(route('mercadopago.callback')),
                ],
                "external_reference" => (string) $order->id,
                "notification_url" => route('mercadopago.webhook'),
            ]);

            $order->mercadopago_preference_id = $preference->id;
            $order->save();

            DB::commit();

            return view('checkout.mercadopago', [
                'preferenceId' => $preference->id,
                'order' => $order
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Checkout error: ' . $e->getMessage());
            if (method_exists($e, 'getApiResponse')) {
                $response = $e->getApiResponse();
                \Log::error('Mercado Pago API Response Status: ' . $response->getStatusCode());
                \Log::error('Mercado Pago API Response Content: ' . json_encode($response->getContent()));
            }
            return back()->with('error', 'Error al procesar: ' . $e->getMessage());
        }
    }

    public function handleMercadoPagoCallback(Request $request)
    {
        // Este callback es la redirección del usuario después de pagar.
        // No debemos usarlo como única prueba de pago final porque su status puede venir del cliente.
        // La confirmación real se aplica en el webhook de Mercado Pago.
        if (!$request->has('external_reference')) {
            return redirect()->route('checkout.index')->with('error', 'No se pudo encontrar la referencia de la orden.');
        }

        $order = Order::where('id', $request->external_reference)->firstOrFail();

        if ($request->status === 'approved') {
            session()->forget('cart');
            $this->flashCheckoutSuccess($order, 'approved');
            return redirect()->route('checkout.success');
        }

        return redirect()->route('checkout.index')->with('error', 'El pago fue rechazado o cancelado. Por favor, intenta de nuevo.');
    }

    public function thankyou()
    {
        $checkout = session('checkout', []);
        $bankTransfer = config('services.bank_transfer');

        $whatsAppUrl = null;
        $whatsAppNumber = preg_replace('/\D+/', '', (string) ($bankTransfer['whatsapp_number'] ?? ''));

        if (($checkout['payment_method'] ?? null) === 'transferencia' && !empty($checkout['order_id']) && !empty($whatsAppNumber)) {
            $customerName = $checkout['name'] ?? 'Cliente';
            $customerEmail = $checkout['email'] ?? 'sin-email';
            $total = isset($checkout['total']) ? number_format((float) $checkout['total'], 0, ',', '.') : '0';

            $message = sprintf(
                "Hola, quiero confirmar el pedido #%s por $%s y coordinar el pago por transferencia. Nombre: %s. Email: %s.",
                $checkout['order_id'],
                $total,
                $customerName,
                $customerEmail,
            );

            $whatsAppUrl = 'https://wa.me/' . $whatsAppNumber . '?text=' . urlencode($message);
        }

        return view('checkout.thankyou', [
            'checkout' => $checkout,
            'bankTransfer' => $bankTransfer,
            'whatsAppUrl' => $whatsAppUrl,
        ]);
    }

    private function configureMercadoPago(): void
    {
        MercadoPagoConfig::setAccessToken(config('mercadopago.access_token'));

        // En desarrollo local usamos el modo LOCAL para evitar errores de certificado.
        if (app()->environment('local')) {
            MercadoPagoConfig::setRuntimeEnviroment(MercadoPagoConfig::LOCAL);
        }
    }

    // Este método es para manejar los webhooks de Mercado Pago (notificaciones IPN)
    public function handleWebhook(Request $request, OrderEmailService $orderEmailService)
    {
        $paymentId = $request->input('data.id') ?? $request->input('id');

        if (!$paymentId) {
            \Log::warning('Webhook de Mercado Pago recibido sin payment ID.');
            return response()->json(['status' => 'ok'], 200);
        }

        $this->configureMercadoPago();
        $paymentClient = new PaymentClient();

        try {
            $payment = $paymentClient->get((int) $paymentId);
        } catch (\Exception $e) {
            \Log::error('Error al consultar el pago de Mercado Pago desde el webhook.', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['status' => 'error'], 500);
        }

        $externalReference = $payment->external_reference ?? null;
        if (!$externalReference) {
            \Log::warning('Pago de Mercado Pago sin external_reference.', [
                'payment_id' => $paymentId,
            ]);
            return response()->json(['status' => 'ok'], 200);
        }

        $order = Order::where('id', $externalReference)->first();
        if (!$order) {
            \Log::warning('Orden no encontrada para el webhook de Mercado Pago.', [
                'payment_id' => $paymentId,
                'external_reference' => $externalReference,
            ]);
            return response()->json(['status' => 'ok'], 200);
        }

        $status = strtolower($payment->status ?? '');
        if ($status === 'approved' && $order->status !== 'completed') {
            $order->status = 'completed';
            $order->mercadopago_payment_id = $paymentId;
            $order->save();

            $orderEmailService->sendOrderConfirmation($order);
            \Log::info('Orden marcada como completada y email enviado.', [
                'order_id' => $order->id,
                'payment_id' => $paymentId,
            ]);

            try {
                app(OrderNotificationService::class)->notifyMercadoPagoApprovedPayment($order);
            } catch (\Throwable $e) {
                \Log::error('Error notifying Telegram bot about approved Mercado Pago payment.', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($status !== 'approved' && $order->status === 'pending') {
            // Guardamos el estado real del pago si no está aprobado.
            $order->status = $status;
            $order->save();
        }

        return response()->json(['status' => 'ok'], 200);
    }

    private function flashCheckoutSuccess(Order $order, ?string $paymentStatus = null): void
    {
        session()->put('checkout', [
            'order_id' => $order->id,
            'name' => $order->name,
            'email' => $order->email,
            'payment_method' => $order->payment_method,
            'payment_method_label' => $this->paymentMethodLabel($order->payment_method),
            'status' => $paymentStatus ?? $order->status,
            'shipping_zone' => $order->shipping_zone,
            'shipping_zone_label' => $this->shippingZoneLabel($order->shipping_zone),
            'shipping_cost' => (float) $order->shipping_cost,
            'subtotal_products' => (float) $order->total - (float) $order->shipping_cost,
            'total' => $order->total,
        ]);
    }

    private function shippingZones(): array
    {
        $zones = ShippingZone::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['zone_key', 'label', 'price'])
            ->mapWithKeys(fn ($zone) => [
                $zone->zone_key => [
                    'label' => $zone->label,
                    'price' => (int) $zone->price,
                ],
            ])
            ->toArray();

        return !empty($zones) ? $zones : config('shipping.zones', []);
    }

    private function shippingZoneLabel(?string $shippingZone): ?string
    {
        if (!$shippingZone) {
            return null;
        }

        return $this->shippingZones()[$shippingZone]['label'] ?? $shippingZone;
    }

    private function paymentMethodLabel(?string $paymentMethod): string
    {
        return match ($paymentMethod) {
            'mercadopago' => 'Mercado Pago',
            'efectivo' => 'Efectivo al entregar',
            'transferencia' => 'Transferencia bancaria',
            default => 'No informado',
        };
    }
}
