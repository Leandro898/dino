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
                error_log('🚀 ORDER ASSIGNED: ' . $order->id);

                try {
                    // Get vendors (users who own products in this order)
                    $vendors = $order->items
                        ->load('product')
                        ->map->product
                        ->pluck('user')
                        ->unique('id');

                    error_log('📦 Found ' . count($vendors) . ' vendors');

                    foreach ($vendors as $vendor) {
                        error_log('📤 Dispatching event to vendor: ' . $vendor->id);
                        error_log('📤 Channel will be: vendor.' . $vendor->id);
                        error_log('📤 Event name will be: order-assigned');
                        
                        // Dispatch broadcast event (real-time to vendor's browser)
                        \App\Events\OrderAssignedBroadcast::dispatch($order, $vendor->id);

                        error_log('📬 Event dispatched to Reverb');

                        // Send database notification (updates bell icon)
                        $vendor->notify(new \App\Notifications\OrderAssignedNotification($order));
                        
                        error_log('🔔 Notification sent to vendor: ' . $vendor->id);
                    }
                    
                    error_log('✅ All events dispatched successfully');
                } catch (\Throwable $e) {
                    error_log('❌ ERROR: ' . $e->getMessage());
                    error_log('❌ TRACE: ' . $e->getTraceAsString());
                }
            }
        });
    }
}
