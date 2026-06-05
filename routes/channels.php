<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

// TEMPORARY: Allow all for debugging
Broadcast::channel('vendor.{vendorId}', function (User $user, int $vendorId): bool {
    \Log::info('Broadcast auth attempt', [
        'user_id' => $user->id ?? 'null',
        'user_role' => $user->role ?? 'null',
        'vendor_id' => $vendorId,
        'user_exists' => !is_null($user),
    ]);
    
    if (!$user) {
        \Log::error('Broadcast auth: user is null');
        return false;
    }
    
    $allowed = $user->role === 'admin' || ($user->role === 'vendor' && (int) $user->id === $vendorId);
    
    \Log::info('Broadcast auth result', [
        'user_id' => $user->id,
        'user_role' => $user->role,
        'vendor_id' => $vendorId,
        'allowed' => $allowed,
    ]);
    
    return $allowed;
});
