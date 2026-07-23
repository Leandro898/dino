<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderTrackingController extends Controller
{
    /**
     * Vista de seguimiento para usuarios autenticados (admin/vendor/delivery/owner).
     */
    public function show(Request $request, Order $order): View
    {
        $user = $request->user();
        $allowed = $user->role === 'admin'
            || ($user->role === 'vendor' && (int) $user->id === (int) $order->vendor_id)
            || ($user->role === 'delivery' && (int) $user->id === (int) $order->delivery_user_id)
            || ((int) $user->id === (int) $order->user_id);

        if (!$allowed) {
            abort(403, 'No tienes permiso para ver este seguimiento.');
        }

        return $this->renderTracking($order);
    }

    /**
     * Vista de seguimiento pública para el cliente (accesible via URL firmada, sin login).
     */
    public function showPublic(Request $request, Order $order): View
    {
        // La validación de la firma la hace el middleware 'signed' en la ruta
        return $this->renderTracking($order);
    }

    /**
     * Genera la URL firmada de tracking para un pedido.
     */
    public static function trackingUrl(Order $order): string
    {
        return \Illuminate\Support\Facades\URL::signedRoute('orders.tracking.public', [
            'order' => $order->id,
        ]);
    }

    private function renderTracking(Order $order): View
    {
        $order->load(['vendor', 'deliveryRider']);

        return view('order.tracking', [
            'order'  => $order,
            'vendor' => $order->vendor,
            'rider'  => $order->deliveryRider,
        ]);
    }
}
