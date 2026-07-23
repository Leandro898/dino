<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class DeliveryAppController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(function (Request $request, $next) {
                $role = optional($request->user())->role;
                if (!in_array($role, ['admin', 'vendor', 'delivery'], true)) {
                    abort(403);
                }
                return $next($request);
            }),
        ];
    }

    public function index(Request $request): View
    {
        if ($request->user()->role === 'delivery' && !$request->user()->is_approved) {
            return view('delivery.pending-approval');
        }

        return view('delivery.app');
    }

    public function latest(Request $request): JsonResponse
    {
        if ($request->user()->role === 'delivery' && !$request->user()->is_approved) {
            return response()->json([
                'has_order' => false,
            ]);
        }

        $latestOrder = Order::query()
            ->with('vendor')
            ->where('delivery_user_id', $request->user()->id)
            ->whereIn('status', ['pending', 'assigned', 'processing', 'shipped'])
            ->latest('id')
            ->first();

        if (!$latestOrder) {
            return response()->json([
                'has_order' => false,
            ]);
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
        ]);
    }

    public function acceptOrder(Request $request, Order $order): JsonResponse
    {
        if ($order->delivery_user_id !== $request->user()->id) {
            return response()->json(['error' => 'No tienes asignado este pedido.'], 403);
        }

        $order->update([
            'is_accepted_by_rider' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pedido aceptado correctamente.',
        ]);
    }

    public function rejectOrder(Request $request, Order $order): JsonResponse
    {
        if ($order->delivery_user_id !== $request->user()->id) {
            return response()->json(['error' => 'No tienes asignado este pedido.'], 403);
        }

        $order->update([
            'delivery_user_id' => null,
            'is_accepted_by_rider' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pedido rechazado correctamente.',
        ]);
    }

    public function getSupportMessages(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        // Marcar los mensajes del admin como leídos
        \App\Models\SupportMessage::where('delivery_user_id', $userId)
            ->where('sender_id', '!=', $userId)
            ->where('is_read_by_delivery', false)
            ->update(['is_read_by_delivery' => true]);

        $messages = \App\Models\SupportMessage::with('sender')
            ->where('delivery_user_id', $userId)
            ->oldest()
            ->get();

        return response()->json($messages);
    }

    public function sendSupportMessage(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|min:1',
        ]);

        $userId = $request->user()->id;

        $message = \App\Models\SupportMessage::create([
            'delivery_user_id' => $userId,
            'sender_id' => $userId,
            'message' => $request->input('message'),
            'is_read_by_admin' => false,
            'is_read_by_delivery' => true,
        ]);

        $message->load('sender');

        try {
            broadcast(new \App\Events\SupportMessageSent($message))->toOthers();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error broadcasting support message:', ['error' => $e->getMessage()]);
        }

        return response()->json($message);
    }

    public function updateLocation(Request $request): JsonResponse
    {
        $request->validate([
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $rider = $request->user();

        // Actualizar coordenadas del rider en la tabla users
        $rider->update([
            'latitude'  => $request->input('latitude'),
            'longitude' => $request->input('longitude'),
        ]);

        // Buscar la orden activa asignada a este rider (en tránsito)
        $activeOrder = Order::query()
            ->where('delivery_user_id', $rider->id)
            ->whereIn('status', ['shipped', 'processing', 'assigned'])
            ->orderByDesc('id')
            ->first();

        if ($activeOrder) {
            try {
                broadcast(new \App\Events\RiderLocationUpdated(
                    $activeOrder->id,
                    (float) $request->input('latitude'),
                    (float) $request->input('longitude'),
                    $rider->id
                ));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Error broadcasting rider location:', ['error' => $e->getMessage()]);
            }
        }

        return response()->json(['success' => true]);
    }

    public function markAsPickedUp(Request $request, Order $order): JsonResponse
    {
        if ($order->delivery_user_id !== $request->user()->id) {
            return response()->json(['error' => 'No tienes asignado este pedido.'], 403);
        }

        $order->update([
            'status' => 'shipped',
        ]);

        try {
            broadcast(new \App\Events\OrderStatusUpdated($order, 'shipped'));
        } catch (\Exception $e) {}

        return response()->json([
            'success' => true,
            'message' => 'Pedido marcado como retirado.',
        ]);
    }

    public function markAsDelivered(Request $request, Order $order): JsonResponse
    {
        if ($order->delivery_user_id !== $request->user()->id) {
            return response()->json(['error' => 'No tienes asignado este pedido.'], 403);
        }

        $order->update([
            'status' => 'completed',
        ]);

        try {
            broadcast(new \App\Events\OrderStatusUpdated($order, 'completed'));
        } catch (\Exception $e) {}

        return response()->json([
            'success' => true,
            'message' => 'Pedido marcado como entregado.',
        ]);
    }
}
