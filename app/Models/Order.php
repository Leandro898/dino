<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'vendor_id',
        'delivery_user_id',
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

    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function deliveryRider()
    {
        return $this->belongsTo(User::class, 'delivery_user_id');
    }

    protected static function booted()
    {
        static::updating(function ($order) {
            \Illuminate\Support\Facades\Log::info("MODELO ORDER: [updating] Intento de actualización", [
                'order_id' => $order->id,
                'isDirty_status' => $order->isDirty('status'),
                'old_status' => $order->getOriginal('status'),
                'new_status' => $order->status,
                'request_url' => request()->url(),
                'user' => auth()->id()
            ]);
        });

        static::updated(function ($order) {
            \Illuminate\Support\Facades\Log::info("MODELO ORDER: [updated] Guardado exitoso", ['order_id' => $order->id]);
            
            if ($order->wasChanged('status')) {
                $oldStatus = $order->getOriginal('status');
                \Illuminate\Support\Facades\Log::info("MODELO ORDER: Status cambiado, emitiendo OrderStatusUpdated", [
                    'order_id' => $order->id,
                    'old' => $oldStatus,
                    'new' => $order->status
                ]);
                broadcast(new \App\Events\OrderStatusUpdated($order, $oldStatus));
            }

            if ($order->wasChanged('delivery_user_id')) {
                $oldRiderId = $order->getOriginal('delivery_user_id');
                $newRiderId = $order->delivery_user_id;

                if ($newRiderId) {
                    broadcast(new \App\Events\OrderUpdatedForRider($order, true));
                }

                if ($oldRiderId && $oldRiderId != $newRiderId) {
                    broadcast(new \App\Events\OrderUnassignedFromRider((int)$oldRiderId, (int)$order->id));
                }
            }

            if ($order->wasChanged('status') && $order->delivery_user_id) {
                broadcast(new \App\Events\OrderUpdatedForRider($order, false));
            }
        });
    }
}
