<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'email',
        'address',
        'phone',
        'total',
        'status', // 'pending', 'processing', 'completed'
        'payment_method',
        'shipping_zone',
        'shipping_cost',
        'mercadopago_preference_id',
        'mercadopago_payment_id',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted()
    {
        static::updated(function ($order) {
            if ($order->wasChanged('status') && $order->status === 'assigned') {
                \Illuminate\Support\Facades\Log::info('Order assigned - dispatching broadcast and notification', [
                    'order_id' => $order->id,
                ]);

                try {
                    // Get vendors (users who own products in this order)
                    $vendors = $order->items
                        ->load('product')
                        ->map->product
                        ->pluck('user')
                        ->unique('id');

                    foreach ($vendors as $vendor) {
                        // Dispatch broadcast event (real-time to vendor's browser)
                        \App\Events\OrderAssignedBroadcast::dispatch($order, $vendor->id);
                        \Illuminate\Support\Facades\Log::info('OrderAssignedBroadcast dispatched', [
                            'vendor_id' => $vendor->id,
                        ]);

                        // Send database notification (updates bell icon)
                        $vendor->notify(new \App\Notifications\OrderAssignedNotification($order));
                        \Illuminate\Support\Facades\Log::info('OrderAssignedNotification sent', [
                            'vendor_id' => $vendor->id,
                        ]);
                    }
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::error('Error dispatching order notifications', [
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        });
    }
}
