<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DeliveryAppController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($this->canAccessDeliveryApp($request), 403);

        return view('delivery.app');
    }

    public function latest(Request $request): JsonResponse
    {
        abort_unless($this->canAccessDeliveryApp($request), 403);

        $latestOrder = Order::query()
            ->whereIn('status', ['pending', 'pending_transfer', 'proof_sent'])
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
        ]);
    }

    protected function canAccessDeliveryApp(Request $request): bool
    {
        $role = (string) optional($request->user())->role;

        return in_array($role, ['admin', 'vendor', 'delivery'], true);
    }
}
