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
        });
    }
}
