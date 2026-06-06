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
        error_log('👢 Order::booted() CALLED');
        
        // Use 'saving' which fires before EVERY save, regardless of method
        static::saving(function ($order) {
            error_log('💾 Order.saving() hook triggered for order: ' . $order->id . ', status: ' . $order->status);
        });

        // Also monitor 'saved' which fires after every save
        static::saved(function ($order) {
            error_log('✅ Order.saved() hook triggered for order: ' . $order->id . ', status: ' . $order->status);
            
            // Check if status was changed
            if ($order->wasChanged('status')) {
                error_log('🔀 Status changed! Was: ' . $order->getOriginal('status') . ', Now: ' . $order->status);
                
                if ($order->status === 'assigned') {
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
                            
                            // Dispatch broadcast event (real-time to vendor's browser)
                            \App\Events\OrderAssignedBroadcast::dispatch($order, $vendor->id);

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
            }
        });
    }
}
