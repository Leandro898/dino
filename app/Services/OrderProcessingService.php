<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Events\NewOrderCreated;
use Illuminate\Http\Request;

class OrderProcessingService
{
    protected OrderEmailService $orderEmailService;
    protected OrderNotificationService $orderNotificationService;

    public function __construct(OrderEmailService $orderEmailService, OrderNotificationService $orderNotificationService)
    {
        $this->orderEmailService = $orderEmailService;
        $this->orderNotificationService = $orderNotificationService;
    }

    public function createOrder(Request $request, array $cart, array $shippingZoneData, array $preparedItems, float $productsSubtotal, ?int $vendorId): Order
    {
        $paymentMethod = $request->string('payment_method')->toString();
        $shippingZone = $request->string('shipping_zone')->toString();
        $shippingCost = (float) ($shippingZoneData['price'] ?? 0);
        $totalGeneral = $productsSubtotal + $shippingCost;
        $orderStatus = 'pending';

        Log::info('Creando orden...', [
            'total' => $totalGeneral,
            'status' => $orderStatus,
            'payment_method' => $paymentMethod,
            'cart_items_count' => count($preparedItems)
        ]);

        $lat = $request->input('lat');
        $lng = $request->input('lng');

        if (blank($lat) || blank($lng)) {
            $coords = app(ZoneDetectionService::class)->getCoordinates($request->address);
            if ($coords) {
                $lat = $coords['lat'];
                $lng = $coords['lng'];
            }
        }

        $order = Order::create([
            'user_id' => Auth::id(),
            'vendor_id' => $vendorId,
            'name' => $request->name,
            'email' => $this->resolveOrderEmail($request),
            'address' => $request->address,
            'latitude' => $lat,
            'longitude' => $lng,
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

        return $order;
    }

    public function finalizeManualOrder(Order $order, string $paymentMethod): void
    {
        DB::commit();

        $order->loadMissing('items.product');

        Log::info('📡 About to broadcast NewOrderCreated event (no Mercado Pago)', ['order_id' => $order->id]);
        broadcast(new NewOrderCreated($order))->toOthers();
        Log::info('✅ Broadcast sent for order (no Mercado Pago)', ['order_id' => $order->id]);

        $this->orderEmailService->sendOrderConfirmation($order);

        try {
            $this->orderNotificationService->notifyNewOrder($order);
        } catch (\Throwable $e) {
            Log::error('Error notifying admin about offline payment order.', [
                'order_id' => $order->id,
                'payment_method' => $paymentMethod,
                'error' => $e->getMessage(),
            ]);
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
}
