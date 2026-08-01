<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\DeliveryService;
use App\Http\Requests\UpdateLocationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class DeliveryApiController extends Controller implements HasMiddleware
{
    protected DeliveryService $deliveryService;

    public function __construct(DeliveryService $deliveryService)
    {
        $this->deliveryService = $deliveryService;
    }

    public static function middleware(): array
    {
        return [
            new Middleware(function (Request $request, $next) {
                $role = optional($request->user())->role;
                if (!in_array($role, ['admin', 'vendor', 'delivery'], true)) {
                    abort(403);
                }
                if ($role === 'delivery' && !$request->is('repartidor/estado*')) {
                    \Illuminate\Support\Facades\Cache::put('rider_online_' . $request->user()->id, true, now()->addMinutes(2));
                }
                return $next($request);
            }),
        ];
    }

    public function latest(Request $request): JsonResponse
    {
        if ($request->user()->role === 'delivery' && !$request->user()->is_approved) {
            return response()->json(['has_order' => false]);
        }

        $latestOrder = $this->deliveryService->getLatestOrderForRider($request->user());

        if (!$latestOrder) {
            return response()->json(['has_order' => false]);
        }

        return response()->json([
            'has_order' => true,
            'id' => $latestOrder->id,
            'status' => $latestOrder->status,
            'created_at' => optional($latestOrder->created_at)?->toIso8601String(),
            'customer_name' => $latestOrder->name,
            'total' => (float) $latestOrder->total,
            'address' => $latestOrder->address,
            'latitude' => $latestOrder->latitude ? (float) $latestOrder->latitude : null,
            'longitude' => $latestOrder->longitude ? (float) $latestOrder->longitude : null,
            'vendor_name' => optional($latestOrder->vendor)->name ?? 'Comercio',
            'vendor_address' => optional($latestOrder->vendor)->address ?? 'Sin dirección de comercio',
            'vendor_latitude' => optional($latestOrder->vendor)->latitude ? (float) $latestOrder->vendor->latitude : null,
            'vendor_longitude' => optional($latestOrder->vendor)->longitude ? (float) $latestOrder->vendor->longitude : null,
            'is_accepted' => (bool) $latestOrder->is_accepted_by_rider,
            'shipping_cost' => (float) $latestOrder->shipping_cost,
            'rider_earnings' => max(0, (float) $latestOrder->shipping_cost - 1000),
            'payment_method' => $latestOrder->payment_method,
        ]);
    }

    public function acceptOrder(Request $request, Order $order): JsonResponse
    {
        $accepted = $this->deliveryService->acceptOrder($order, $request->user());

        if (!$accepted) {
            return response()->json(['error' => 'No tienes asignado este pedido.'], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Pedido aceptado correctamente.',
        ]);
    }

    public function rejectOrder(Request $request, Order $order): JsonResponse
    {
        $rejected = $this->deliveryService->rejectOrder($order, $request->user());

        if (!$rejected) {
            return response()->json(['error' => 'No tienes asignado este pedido.'], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Pedido rechazado correctamente.',
        ]);
    }

    public function getSupportMessages(Request $request): JsonResponse
    {
        $messages = $this->deliveryService->getMessagesForRider($request->user());
        return response()->json($messages);
    }

    public function sendSupportMessage(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|min:1',
        ]);

        $message = $this->deliveryService->sendSupportMessage($request->user(), $request->input('message'));

        return response()->json($message);
    }

    public function updateLocation(UpdateLocationRequest $request): JsonResponse
    {
        $this->deliveryService->updateLocationAndBroadcast(
            $request->user(),
            (float) $request->input('latitude'),
            (float) $request->input('longitude')
        );

        return response()->json(['success' => true]);
    }

    public function markAsPickedUp(Request $request, Order $order): JsonResponse
    {
        $updated = $this->deliveryService->updateOrderStatus($order, $request->user(), 'shipped');

        if (!$updated) {
            return response()->json(['error' => 'No tienes asignado este pedido.'], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Pedido marcado como retirado.',
        ]);
    }

    public function markAsDelivered(Request $request, Order $order): JsonResponse
    {
        $updated = $this->deliveryService->updateOrderStatus($order, $request->user(), 'completed');

        if (!$updated) {
            return response()->json(['error' => 'No tienes asignado este pedido.'], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Pedido marcado como entregado.',
        ]);
    }

    public function updateStatus(Request $request): JsonResponse
    {
        $user = $request->user();
        $isOnline = filter_var($request->input('is_online', true), FILTER_VALIDATE_BOOLEAN);
        $wasOnline = \Illuminate\Support\Facades\Cache::has('rider_online_' . $user->id);

        if ($isOnline) {
            \Illuminate\Support\Facades\Cache::put('rider_online_' . $user->id, true, now()->addMinutes(2));
        } else {
            \Illuminate\Support\Facades\Cache::forget('rider_online_' . $user->id);
        }

        if ($wasOnline !== $isOnline) {
            try {
                broadcast(new \App\Events\RiderStatusUpdated($user->id, $isOnline))->toOthers();
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Error broadcasting rider status:', ['error' => $e->getMessage()]);
            }
        }

        return response()->json(['success' => true]);
    }
}
