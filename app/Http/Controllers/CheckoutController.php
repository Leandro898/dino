<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use App\Services\OrderEmailService;
use App\Services\OrderNotificationService;
use App\Services\ZoneDetectionService;
use App\Models\ShippingZone;
use App\Models\Product;
// Importaciones de Mercado Pago SDK v3
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Client\Payment\PaymentClient;

class CheckoutController extends Controller
{
    private const FREE_SHIPPING_RAFFLE_SLUG = 'sorteo-helado-rapa-nui-1kg';

    public function index()
    {
        $cart = session()->get('cart', []);

        return view('checkout.index', [
            'shippingZones' => $this->shippingZones(),
            'raffleOnlyMercadoPago' => $this->cartContainsRaffle($cart),
            'freeShippingForSpecificRaffle' => $this->isSpecificRaffleFreeShippingCart($cart),
        ]);
    }

    public function process(Request $request)
    {
        $cart = session()->get('cart', []);
        \Log::info('Cart contents: ' . json_encode($cart));
        if (empty($cart)) return redirect()->back()->with('error', 'El carrito está vacío');

        $raffleOnlyMercadoPago = $this->cartContainsRaffle($cart);
        $freeShippingForSpecificRaffle = $this->isSpecificRaffleFreeShippingCart($cart);
        $shippingZones = $this->shippingZones();
        $allowedPaymentMethods = $raffleOnlyMercadoPago
            ? ['mercadopago']
            : ['mercadopago', 'efectivo', 'transferencia'];

        $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|max:255',
            'street_name'   => $raffleOnlyMercadoPago ? 'nullable|string|max:255' : 'required|string|max:255',
            'street_number' => $raffleOnlyMercadoPago ? 'nullable|integer|min:1' : 'required|integer|min:1',
            'phone'         => 'required',
            'payment_method' => ['required', Rule::in($allowedPaymentMethods)],
            'shipping_zone' => $freeShippingForSpecificRaffle
                ? ['nullable', 'string']
                : ['nullable', 'string', Rule::in(array_keys($shippingZones))],
        ]);

        if ($raffleOnlyMercadoPago && $request->string('payment_method')->toString() !== 'mercadopago') {
            return redirect()->back()->withErrors([
                'payment_method' => 'Para productos de sorteo solo esta disponible Mercado Pago.',
            ])->withInput();
        }

        // Componer dirección completa (no aplica para sorteos)
        if (!$raffleOnlyMercadoPago) {
            if (empty($request->address)) {
                $request->merge([
                    'address' => trim($request->street_name . ' ' . $request->street_number),
                ]);
            }

            // Si el frontend no pudo setear la zona, intentamos detectarla del lado servidor.
            if (!$freeShippingForSpecificRaffle && empty($request->shipping_zone)) {
                $detectedZone = app(ZoneDetectionService::class)->detect(
                    $request->string('street_name')->toString(),
                    (int) $request->input('street_number')
                );

                if ($detectedZone) {
                    $request->merge(['shipping_zone' => $detectedZone]);
                }
            }
        }

        $paymentMethod = $request->string('payment_method')->toString();
        $shippingZone = $request->string('shipping_zone')->toString();
        $shippingZoneData = $freeShippingForSpecificRaffle
            ? ['label' => 'Sorteo sin costo de envio', 'price' => 0]
            : ($shippingZones[$shippingZone] ?? null);

        if (!$freeShippingForSpecificRaffle && !$shippingZoneData) {
            return redirect()->back()->withErrors([
                'shipping_zone' => 'No pudimos detectar la zona con esa calle y altura. Revisá la dirección.',
            ])->withInput();
        }

        try {
            DB::beginTransaction();

            $productsSubtotal = 0;
            $itemsMP = []; // Array para Mercado Pago

            $productIds = collect($cart)
                ->map(fn (array $details, string|int $key) => (int) ($details['product_id'] ?? $key))
                ->filter(fn (int $id) => $id > 0)
                ->unique()
                ->values();

            $products = Product::query()
                ->whereIn('id', $productIds)
                ->get()
                ->keyBy('id');

            foreach ($cart as $id => $details) {
                $productId = (int) ($details['product_id'] ?? $id);
                $product = $products->get($productId);

                if (!$product) {
                    throw ValidationException::withMessages([
                        'cart' => 'Hay un producto en el carrito que ya no existe.',
                    ]);
                }

                $quantity = (int) $details['quantity'];
                $raffleNumber = isset($details['raffle_number'])
                    ? $this->normalizeRaffleNumber((string) $details['raffle_number'])
                    : null;

                if ($product->isRaffle()) {
                    if ($raffleNumber === null) {
                        throw ValidationException::withMessages([
                            'cart' => "El producto {$product->name} requiere un numero de sorteo valido (000-099).",
                        ]);
                    }

                    if ($quantity !== 1) {
                        throw ValidationException::withMessages([
                            'cart' => "El numero {$raffleNumber} del sorteo {$product->name} solo puede comprarse en cantidad 1.",
                        ]);
                    }

                    $alreadySold = OrderItem::query()
                        ->where('product_id', $productId)
                        ->where('raffle_number', $raffleNumber)
                        ->lockForUpdate()
                        ->exists();

                    if ($alreadySold) {
                        throw ValidationException::withMessages([
                            'cart' => "El numero {$raffleNumber} ya fue vendido. Eliminalo del carrito y elegi otro.",
                        ]);
                    }
                }

                $subtotal = $details['price'] * $details['quantity'];
                $productsSubtotal += $subtotal;

                // Preparar items para MP
                $itemsMP[] = [
                    "id" => (string) $productId,
                    "title" => $details['name'] ?? "Producto $id", // Asegúrate de tener el nombre en el carro
                    "quantity" => $quantity,
                    "unit_price" => (float) $details['price'],
                    "currency_id" => "ARS"
                ];
            }

            $shippingCost = $freeShippingForSpecificRaffle
                ? 0.0
                : (float) ($shippingZoneData['price'] ?? 0);
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
                $productId = (int) ($details['product_id'] ?? $id);
                $product = $products->get($productId);
                $raffleNumber = isset($details['raffle_number'])
                    ? $this->normalizeRaffleNumber((string) $details['raffle_number'])
                    : null;

                OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $productId,
                    'price'      => $details['price'],
                    'quantity'   => $details['quantity'],
                    'subtotal'   => $details['price'] * $details['quantity'],
                    'raffle_number' => $product && $product->isRaffle() ? $raffleNumber : null,
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

    private function normalizeRaffleNumber(string $value): ?string
    {
        $digits = preg_replace('/\D+/', '', $value);

        if ($digits === null || $digits === '') {
            return null;
        }

        if (!ctype_digit($digits)) {
            return null;
        }

        $number = (int) $digits;
        if ($number < 0 || $number > 99) {
            return null;
        }

        return str_pad((string) $number, 3, '0', STR_PAD_LEFT);
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
        $hasRaffleItems = $order->items()
            ->whereNotNull('raffle_number')
            ->exists();

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
            'has_raffle' => $hasRaffleItems,
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

    private function cartContainsRaffle(array $cart): bool
    {
        if (empty($cart)) {
            return false;
        }

        foreach ($cart as $details) {
            if (!empty($details['is_raffle'])) {
                return true;
            }
        }

        $productIds = collect($cart)
            ->map(fn (array $details, string|int $key) => (int) ($details['product_id'] ?? $key))
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values();

        if ($productIds->isEmpty()) {
            return false;
        }

        return Product::query()
            ->whereIn('id', $productIds)
            ->where('is_raffle', true)
            ->exists();
    }

    private function isSpecificRaffleFreeShippingCart(array $cart): bool
    {
        if (empty($cart)) {
            return false;
        }

        $productIds = collect($cart)
            ->map(fn (array $details, string|int $key) => (int) ($details['product_id'] ?? $key))
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values();

        if ($productIds->count() !== 1) {
            return false;
        }

        $product = Product::query()
            ->where('id', $productIds->first())
            ->first(['slug', 'is_raffle']);

        return (bool) $product
            && (bool) $product->is_raffle
            && $product->slug === self::FREE_SHIPPING_RAFFLE_SLUG;
    }
}
