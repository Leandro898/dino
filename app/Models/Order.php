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
            \Illuminate\Support\Facades\Log::info('Order updated', [
                'order_id' => $order->id,
                'status' => $order->status,
                'changed_status' => $order->wasChanged('status'),
            ]);

            if ($order->wasChanged('status')) {
                \Illuminate\Support\Facades\Log::info('Order status changed', [
                    'order_id' => $order->id,
                    'old_status' => $order->getOriginal('status'),
                    'new_status' => $order->status,
                ]);

                if ($order->status === 'assigned') {
                    \Illuminate\Support\Facades\Log::info('Order status is ASSIGNED - triggering vendor notification');
                    try {
                        app(\App\Services\OrderNotificationService::class)->notifyVendorOrderAssigned($order);
                        \Illuminate\Support\Facades\Log::info('Vendor notification service completed successfully');
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::error('Error in vendor notification', [
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ]);
                    }
                }
            }
        });
    }
}
