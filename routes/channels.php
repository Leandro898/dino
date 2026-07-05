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
