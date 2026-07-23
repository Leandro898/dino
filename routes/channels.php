<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;

// 🔌 Canal público para notificaciones de nuevas órdenes (Admin Panel)
Broadcast::channel('orders', function () {
    return true;
});

// 🏢 Canal de presencia de Filament (indicador de usuario online en el admin)
// Requerido por Filament para suscribirse a private-App.Models.User.{id}
Broadcast::channel('App.Models.User.{id}', function (User $user, int $id): bool {
    return (int) $user->id === $id;
});

// 📦 Canal privado para notificaciones del vendor
Broadcast::channel('vendor.{vendorId}', function (User $user, int $vendorId): bool {
    Log::info('Broadcast auth vendor channel', [
        'user_id'   => $user->id,
        'user_role' => $user->role ?? 'unknown',
        'vendor_id' => $vendorId,
    ]);

    $allowed = $user->role === 'admin' || ($user->role === 'vendor' && (int) $user->id === $vendorId);

    Log::info('Broadcast auth vendor result', [
        'allowed' => $allowed,
    ]);

    return $allowed;
});

// 🚴 Canal privado para seguimiento de la ubicación del rider de una orden
Broadcast::channel('order.{orderId}', function (User $user, int $orderId): bool {
    $order = \App\Models\Order::find($orderId);
    if (!$order) {
        return false;
    }
    
    // El repartidor asignado, el vendedor asociado, el administrador o el cliente dueño del pedido
    return $user->role === 'admin'
        || ($user->role === 'vendor' && (int) $user->id === (int) $order->vendor_id)
        || ($user->role === 'delivery' && (int) $user->id === (int) $order->delivery_user_id)
        || ((int) $user->id === (int) $order->user_id)
        || ($user->name === $order->name || $user->email === $order->email);
});
