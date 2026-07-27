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
use App\Services\PaymentService;
use App\Services\OrderProcessingService;
use App\Services\WhatsAppService;
use App\Http\Requests\ProcessCheckoutRequest;
use App\Models\ShippingZone;
use App\Models\Product;
use Illuminate\Support\Facades\Log;
use App\Events\NewOrderCreated;

class CheckoutController extends Controller
{
    protected PaymentService $paymentService;
    protected OrderProcessingService $orderProcessingService;
    protected WhatsAppService $whatsAppService;

    public function __construct(
        PaymentService $paymentService,
        OrderProcessingService $orderProcessingService,
        WhatsAppService $whatsAppService
    ) {
        $this->paymentService = $paymentService;
        $this->orderProcessingService = $orderProcessingService;
        $this->whatsAppService = $whatsAppService;
    }

    public function index()
    {
        $cart = Product::syncCartPrices(session()->get('cart', []));
        session()->put('cart', $cart);

        if (empty($cart)) {
            return redirect()->route('home')->with('error', 'Tu carrito está vacío. Agrega algunos productos para continuar.');
        }

        $manualWhatsAppPaymentEnabled = true; // $this->cartBelongsToMasivo($cart);
        $onlyMercadoPago = false;

        return view('checkout.index', [
            'shippingZones' => ShippingZone::getActiveWithPrices(),
            'manualWhatsAppPaymentEnabled' => $manualWhatsAppPaymentEnabled,
            'onlyMercadoPago' => $onlyMercadoPago,
            'googleMapsApiKey' => config('services.google_maps.key'),
        ]);
    }

    public function process(ProcessCheckoutRequest $request)
    {
        Log::emergency('🚀🚀🚀 CHECKOUT PROCESS CALLED - INICIO ABSOLUTO DEL MÉTODO 🚀🚀🚀');
        Log::info('=== CHECKOUT PROCESS START ===');
        
        $cart = Product::syncCartPrices(session()->get('cart', []));
        session()->put('cart', $cart);

        Log::info('Checkout process iniciado', ['cart_count' => count($cart)]);

        if (empty($cart)) {
            Log::warning('Carrito vacío');
            return redirect()->back()->with('error', 'El carrito está vacío');
        }

        $manualWhatsAppPaymentEnabled = true; // $this->cartBelongsToMasivo($cart);
        $onlyMercadoPago = false;
        $shippingZones = ShippingZone::getActiveWithPrices();

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
        $request->merge([
            'address' => trim($request->street_name . ' ' . $request->street_number),
        ]);


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

            $preparedData = $this->orderProcessingService->prepareCartItems($cart, $shippingZoneData, $shippingZone);

            // Uso del nuevo servicio para crear la orden
            $order = $this->orderProcessingService->createOrder(
                $request,
                $cart,
                $shippingZoneData,
                $preparedData['preparedItems'],
                $preparedData['productsSubtotal'],
                $preparedData['vendorId']
            );

            if ($paymentMethod !== 'mercadopago') {
                $this->orderProcessingService->finalizeManualOrder($order, $paymentMethod);
                
                session()->forget('cart');
                $this->flashCheckoutSuccess($order);

                if ($paymentMethod === 'transferencia') {
                    $whatsAppUrl = $this->whatsAppService->buildManualPaymentUrl($order);
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

            // Uso del nuevo servicio de pagos
            $preference = $this->paymentService->createPreference(
                $preparedData['itemsMP'],
                (string) $order->id,
                url(route('mercadopago.callback')),
                url(route('mercadopago.callback')),
                url(route('mercadopago.callback')),
                route('mercadopago.webhook')
            );

            $order->mercadopago_preference_id = $preference->id;
            $order->save();

            DB::commit();

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

    public function handleMercadoPagoCallback(Request $request)
    {
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
        if (($checkout['payment_method'] ?? null) === 'transferencia' && !empty($checkout['order_id'])) {
            $whatsAppUrl = $this->whatsAppService->buildManualPaymentUrl($checkout);
        }

        // Generar URL firmada de seguimiento del pedido
        $trackingUrl = null;
        if (isset($order) && $order) {
            $trackingUrl = \App\Http\Controllers\OrderTrackingController::trackingUrl($order);
        }

        return view('checkout.thankyou', [
            'checkout' => $checkout,
            'orderItems' => $orderItems,
            'bankTransfer' => $bankTransfer,
            'whatsAppUrl' => $whatsAppUrl,
            'trackingUrl' => $trackingUrl,
        ]);
    }

    public function handleWebhook(Request $request, OrderEmailService $orderEmailService)
    {
        $paymentId = $request->input('data.id') ?? $request->input('id');

        if (!$paymentId) {
            Log::warning('Webhook de Mercado Pago recibido sin payment ID.');
            return response()->json(['status' => 'ok'], 200);
        }

        try {
            $payment = $this->paymentService->getPaymentDetails((int) $paymentId);
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

            Log::info('📡 About to broadcast NewOrderCreated event from Webhook (Mercado Pago)', ['order_id' => $order->id]);
            broadcast(new NewOrderCreated($order));
            Log::info('✅ Broadcast sent for order from Webhook (Mercado Pago)', ['order_id' => $order->id]);
        }

        if ($status !== 'approved' && $order->status === 'pending') {
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

    private function shippingZoneLabel(?string $shippingZone): ?string
    {
        if (!$shippingZone) {
            return null;
        }

        return ShippingZone::getActiveWithPrices()[$shippingZone]['label'] ?? $shippingZone;
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
        $productIds = collect($cart)->pluck('product_id')->filter()->toArray();

        if (empty($productIds)) {
            $productIds = collect($cart)->keys()->filter(fn($key) => is_numeric($key))->toArray();
        }

        if (empty($productIds)) {
            return false;
        }

        $masivoUser = \App\Models\User::where('is_masivo', true)->first();
        if (!$masivoUser) {
            return false;
        }

        $count = Product::whereIn('id', $productIds)
            ->where('user_id', $masivoUser->id)
            ->count();

        return $count > 0;
    }
}
