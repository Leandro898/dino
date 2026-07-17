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
use Illuminate\Support\Facades\Log;
use App\Events\NewOrderCreated;
// Importaciones de Mercado Pago SDK v3
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Client\Payment\PaymentClient;
use App\Events\NewOrderForVendor;

class CheckoutController extends Controller
{
    public function index()
    {
        $cart = $this->syncCartPrices(session()->get('cart', []));
        session()->put('cart', $cart);

        if (empty($cart)) {
            return redirect()->route('home')->with('error', 'Tu carrito está vacío. Agrega algunos productos para continuar.');
        }

        $manualWhatsAppPaymentEnabled = $this->cartBelongsToMasivo($cart);
        $onlyMercadoPago = !$manualWhatsAppPaymentEnabled;

        return view('checkout.index', [
            'shippingZones' => $this->shippingZones(),
            'manualWhatsAppPaymentEnabled' => $manualWhatsAppPaymentEnabled,
            'onlyMercadoPago' => $onlyMercadoPago,
            'googleMapsApiKey' => config('services.google_maps.key'),
        ]);
    }

    public function process(Request $request)
    {
        Log::emergency('🚀🚀🚀 CHECKOUT PROCESS CALLED - INICIO ABSOLUTO DEL MÉTODO 🚀🚀🚀');
        
        Log::info('=== CHECKOUT PROCESS START ===');
        
        $cart = $this->syncCartPrices(session()->get('cart', []));
        session()->put('cart', $cart);

        Log::info('Checkout process iniciado', ['cart_count' => count($cart)]);

        if (empty($cart)) {
            Log::warning('Carrito vacío');
            return redirect()->back()->with('error', 'El carrito está vacío');
        }

        $manualWhatsAppPaymentEnabled = $this->cartBelongsToMasivo($cart);
        $onlyMercadoPago = !$manualWhatsAppPaymentEnabled;
        $shippingZones = $this->shippingZones();
        $allowedPaymentMethods = $manualWhatsAppPaymentEnabled ? ['mercadopago', 'transferencia'] : ['mercadopago'];

        $request->validate([
            'name'          => 'required|string|max:255|min:3',
            'email'         => 'nullable|email|max:255',
            'street_name'   => 'required|string|max:255|min:3',
            'street_number' => 'required|integer|min:1|max:99999',
            'phone'         => 'required|regex:/^(\+?\d{1,3}[-\.\s]?)?(\d{3})?[-\.\s]?\d{3}[-\.\s]?\d{4}$/|min:10|max:20',
            'payment_method' => ['required', Rule::in($allowedPaymentMethods)],
            'shipping_zone' => ['nullable', 'string', Rule::in(array_keys($shippingZones))],
        ], [
            'name.required' => 'El nombre es obligatorio',
            'name.min' => 'El nombre debe tener al menos 3 caracteres',
            'name.max' => 'El nombre no puede exceder 255 caracteres',
            'email.email' => 'El email no es válido',
            'phone.required' => 'El teléfono es obligatorio',
            'phone.regex' => 'El formato del teléfono no es válido',
            'phone.min' => 'El teléfono debe tener al menos 10 dígitos',
            'street_name.min' => 'La calle debe tener al menos 3 caracteres',
            'street_number.max' => 'El número no puede ser mayor a 99999',
            'payment_method.in' => 'Método de pago no válido',
        ]);

        Log::info('Validación exitosa', [
            'name' => $request->name,
            'payment_method' => $request->payment_method,
        ]);

        if ($onlyMercadoPago && $request->string('payment_method')->toString() !== 'mercadopago') {
            return redirect()->back()->withErrors([
                'payment_method' => 'Por el momento solo esta disponible Mercado Pago para este pedido.',
            ])->withInput();
        }

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

        $paymentMethod = $request->string('payment_method')->toString();
        $shippingZone = $request->string('shipping_zone')->toString();
        $shippingZoneData = $shippingZones[$shippingZone] ?? null;

        if (!$shippingZoneData) {
            return redirect()->back()->withErrors([
                'shipping_zone' => 'No pudimos detectar la zona con esa calle y altura. Revisá la dirección.',
            ])->withInput();
        }

        try {
            Log::info('Iniciando transacción...');
            DB::beginTransaction();

            $productsSubtotal = 0;
            $itemsMP = []; // Array para Mercado Pago
            $preparedItems = [];

            $productIds = collect($cart)
                ->map(fn(array $details, string|int $key) => (int) ($details['product_id'] ?? $key))
                ->filter(fn(int $id) => $id > 0)
                ->unique()
                ->values();

            $products = Product::query()
                ->whereIn('id', $productIds)
                ->get()
                ->keyBy('id');

            $vendorId = null;
            if ($products->isNotEmpty()) {
                $vendorId = $products->first()->user_id;
            }

            foreach ($cart as $id => $details) {
                $productId = (int) ($details['product_id'] ?? $id);
                $product = $products->get($productId);

                if (!$product) {
                    throw ValidationException::withMessages([
                        'cart' => 'Hay un producto en el carrito que ya no existe.',
                    ]);
                }

                $quantity = (int) $details['quantity'];

                $unitPrice = (float) $product->adjusted_price;
                $subtotal = $unitPrice * $quantity;
                $productsSubtotal += $subtotal;
                $preparedItems[$id] = [
                    'unit_price' => $unitPrice,
                    'quantity' => $quantity,
                    'subtotal' => $subtotal,
                ];

                // Preparar items para MP
                $itemsMP[] = [
                    "id" => (string) $productId,
                    "title" => $details['name'] ?? $product->name ?? "Producto $id",
                    "quantity" => $quantity,
                    "unit_price" => $unitPrice,
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

            // Todos los pedidos nuevos quedan como 'pending' (solo admin los ve)
            $orderStatus = 'pending';

            Log::info('Creando orden...', [
                'total' => $totalGeneral,
                'status' => $orderStatus,
                'payment_method' => $paymentMethod,
                'cart_items_count' => count($preparedItems)
            ]);

            $order = Order::create([
                'user_id' => Auth::id(),
                'vendor_id' => $vendorId,
                'name' => $request->name,
                'email' => $this->resolveOrderEmail($request),
                'address' => $request->address,
                'phone' => $request->phone,
                'total' => $totalGeneral,
                'status' => $orderStatus,
                'payment_method' => $paymentMethod,
                'shipping_zone' => $shippingZone,
                'shipping_cost' => $shippingCost,
            ]);

            Log::info('✅ Orden creada', ['order_id' => $order->id]);

            foreach ($cart as $id => $details) {
                $productId = (int) ($details['product_id'] ?? $id);
                $preparedItem = $preparedItems[$id] ?? null;

                if (!$preparedItem) {
                    continue;
                }

                OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $productId,
                    'price'      => $preparedItem['unit_price'],
                    'quantity'   => $preparedItem['quantity'],
                    'subtotal'   => $preparedItem['subtotal'],
                ]);
            }

            if ($paymentMethod !== 'mercadopago') {
                DB::commit();

                $order->loadMissing('items.product');

                Log::info('📡 About to broadcast NewOrderCreated event (no Mercado Pago)', ['order_id' => $order->id]);
                broadcast(new NewOrderCreated($order))->toOthers();
                Log::info('✅ Broadcast sent for order (no Mercado Pago)', ['order_id' => $order->id]);

                app(OrderEmailService::class)->sendOrderConfirmation($order);

                try {
                    app(OrderNotificationService::class)->notifyNewOrder($order);
                } catch (\Throwable $e) {
                    Log::error('Error notifying admin about offline payment order.', [
                        'order_id' => $order->id,
                        'payment_method' => $paymentMethod,
                        'error' => $e->getMessage(),
                    ]);
                }

                session()->forget('cart');
                $this->flashCheckoutSuccess($order);

                if ($paymentMethod === 'transferencia') {
                    $whatsAppUrl = $this->buildManualPaymentWhatsAppUrl($order);
                    if ($whatsAppUrl) {
                        session()->put('whatsAppUrl', $whatsAppUrl);
                    }
                }

                Log::info('✅ Orden creada exitosamente (pago manual)', [
                    'order_id' => $order->id,
                    'payment_method' => $paymentMethod,
                    'total' => $order->total
                ]);

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

            // La notificación en tiempo real para Mercado Pago ahora se envía en el Webhook al confirmarse el pago.
            Log::info('✅ Orden completada - Mercado Pago', [
                'order_id' => $order->id,
                'preference_id' => $preference->id,
                'total' => $order->total
            ]);

            return view('checkout.mercadopago', [
                'preferenceId' => $preference->id,
                'order' => $order
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('❌ CHECKOUT ERROR', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if (method_exists($e, 'getApiResponse')) {
                $response = $e->getApiResponse();
                Log::error('Mercado Pago API Response Status: ' . $response->getStatusCode());
                Log::error('Mercado Pago API Response Content: ' . json_encode($response->getContent()));
            }
            
            return back()->with('error', 'Error al procesar: ' . $e->getMessage());
        }
    }


    private function resolveOrderEmail(Request $request): string
    {
        $email = trim((string) $request->input('email', ''));

        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return mb_strtolower($email);
        }

        return 'pedido-' . now()->timestamp . '-' . random_int(100, 999) . '@baritienda.local';
    }

    private function syncCartPrices(array $cart): array
    {
        $productIds = collect($cart)
            ->map(fn(array $item, string|int $key) => (int) ($item['product_id'] ?? $key))
            ->filter(fn(int $id) => $id > 0)
            ->unique()
            ->values();

        if ($productIds->isEmpty()) {
            return $cart;
        }

        $products = Product::query()
            ->whereIn('id', $productIds)
            ->get()
            ->keyBy('id');

        foreach ($cart as $key => $item) {
            $productId = (int) ($item['product_id'] ?? $key);
            $product = $products->get($productId);

            if (!$product) {
                continue;
            }

            $cart[$key]['price'] = $product->adjusted_price;
        }

        return $cart;
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
        $supportWhatsApp = preg_replace('/\D+/', '', (string) (config('services.support.whatsapp_number') ?: ''));
        $orderItems = [];

        if (!empty($checkout['order_id'])) {
            $order = Order::query()
                ->with(['items.product:id,name'])
                ->find($checkout['order_id']);

            if ($order) {
                $checkout = $this->checkoutSuccessPayload($order, $checkout['status'] ?? null);
                $orderItems = $order->items
                    ->map(function (OrderItem $item) {
                        return [
                            'name' => $item->product?->name ?? 'Producto',
                            'quantity' => (int) $item->quantity,
                            'price' => (float) $item->price,
                            'subtotal' => (float) $item->subtotal,
                        ];
                    })
                    ->values()
                    ->all();
            }
        }

        $whatsAppUrl = null;
        $whatsAppNumber = preg_replace('/\D+/', '', (string) ($bankTransfer['whatsapp_number'] ?? '')) ?: $supportWhatsApp;

        if (($checkout['payment_method'] ?? null) === 'transferencia' && !empty($checkout['order_id']) && !empty($whatsAppNumber)) {
            $customerName = $checkout['name'] ?? 'Cliente';
            $customerEmail = $checkout['email'] ?? 'sin-email';
            $customerAddress = $checkout['address'] ?? 'sin direccion';
            $total = isset($checkout['total']) ? number_format((float) $checkout['total'], 0, ',', '.') : '0';

            $message = sprintf(
                "Hola, envio el comprobante de transferencia del pedido #%s por $%s. Nombre: %s. Email: %s. Direccion: %s. Por favor confirmen la recepcion del pago.",
                $checkout['order_id'],
                $total,
                $customerName,
                $customerEmail,
                $customerAddress,
            );

            $whatsAppUrl = 'https://wa.me/' . $whatsAppNumber . '?text=' . urlencode($message);
        }

        return view('checkout.thankyou', [
            'checkout' => $checkout,
            'orderItems' => $orderItems,
            'bankTransfer' => $bankTransfer,
            'whatsAppUrl' => $whatsAppUrl,
        ]);
    }

    private function configureMercadoPago(): void
    {
        MercadoPagoConfig::setAccessToken(config('mercadopago.access_token') ?? '');

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
            Log::warning('Webhook de Mercado Pago recibido sin payment ID.');
            return response()->json(['status' => 'ok'], 200);
        }

        $this->configureMercadoPago();
        $paymentClient = new PaymentClient();

        try {
            $payment = $paymentClient->get((int) $paymentId);
        } catch (\Exception $e) {
            Log::error('Error al consultar el pago de Mercado Pago desde el webhook.', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['status' => 'error'], 500);
        }

        $externalReference = $payment->external_reference ?? null;
        if (!$externalReference) {
            Log::warning('Pago de Mercado Pago sin external_reference.', [
                'payment_id' => $paymentId,
            ]);
            return response()->json(['status' => 'ok'], 200);
        }

        $order = Order::where('id', $externalReference)->first();
        if (!$order) {
            Log::warning('Orden no encontrada para el webhook de Mercado Pago.', [
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
            Log::info('Orden marcada como completada y email enviado.', [
                'order_id' => $order->id,
                'payment_id' => $paymentId,
            ]);

            try {
                app(OrderNotificationService::class)->notifyMercadoPagoApprovedPayment($order);
            } catch (\Throwable $e) {
                Log::error('Error notifying Telegram bot about approved Mercado Pago payment.', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
            }

            // Emitir evento de WebSocket para notificar en el panel en tiempo real
            Log::info('📡 About to broadcast NewOrderCreated event from Webhook (Mercado Pago)', ['order_id' => $order->id]);
            broadcast(new NewOrderCreated($order));
            Log::info('✅ Broadcast sent for order from Webhook (Mercado Pago)', ['order_id' => $order->id]);
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
        session()->put('checkout', $this->checkoutSuccessPayload($order, $paymentStatus));
    }

    private function checkoutSuccessPayload(Order $order, ?string $paymentStatus = null): array
    {
        return [
            'order_id' => $order->id,
            'name' => $order->name,
            'email' => $order->email,
            'address' => $order->address,
            'payment_method' => $order->payment_method,
            'payment_method_label' => $this->paymentMethodLabel($order->payment_method),
            'status' => $paymentStatus ?? $order->status,
            'shipping_zone' => $order->shipping_zone,
            'shipping_zone_label' => $this->shippingZoneLabel($order->shipping_zone),
            'shipping_cost' => (float) $order->shipping_cost,
            'subtotal_products' => (float) $order->total - (float) $order->shipping_cost,
            'total' => (float) $order->total,
        ];
    }

    private function shippingZones(): array
    {
        $zones = ShippingZone::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['zone_key', 'label', 'price'])
            ->mapWithKeys(fn($zone) => [
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
            'transferencia' => 'Efectivo o transferencia por WhatsApp',
            default => 'No informado',
        };
    }

    private function cartBelongsToMasivo(array $cart): bool
    {
        // Ahora habilitado para todos los vendedores
        return true;
    }

    private function buildManualPaymentWhatsAppUrl(Order $order): ?string
    {
        $supportWhatsApp = preg_replace('/\D+/', '', (string) (config('services.support.whatsapp_number') ?: ''));
        $bankTransferWhatsApp = preg_replace('/\D+/', '', (string) (config('services.bank_transfer.whatsapp_number') ?: ''));
        $whatsAppNumber = $bankTransferWhatsApp ?: $supportWhatsApp;

        if (empty($whatsAppNumber)) {
            return null;
        }

        $order->loadMissing('items.product');

        $lines = [
            'Hola! Quiero confirmar mi pedido #' . $order->id . '.',
            'Quiero coordinar el pago por efectivo o transferencia.',
            '',
            'Detalle del carrito:',
        ];

        foreach ($order->items as $item) {
            $name = $item->product?->name ?? 'Producto';
            $quantity = (int) $item->quantity;
            $subtotal = number_format((float) $item->subtotal, 0, ',', '.');
            $lines[] = '- ' . $name . ' x' . $quantity . ' ($' . $subtotal . ')';
        }

        $subtotalProducts = number_format((float) $order->total - (float) $order->shipping_cost, 0, ',', '.');
        $shippingCost = number_format((float) $order->shipping_cost, 0, ',', '.');
        $total = number_format((float) $order->total, 0, ',', '.');

        $lines[] = '';
        $lines[] = 'Subtotal productos: $' . $subtotalProducts;
        $lines[] = 'Envio: $' . $shippingCost;
        $lines[] = 'Total: $' . $total;
        $lines[] = '';
        $lines[] = 'Nombre: ' . $order->name;
        $lines[] = 'Telefono: ' . $order->phone;
        $lines[] = 'Direccion: ' . ($order->address ?: 'Sin direccion');

        return 'https://wa.me/' . $whatsAppNumber . '?text=' . urlencode(implode("\n", $lines));
    }


}
