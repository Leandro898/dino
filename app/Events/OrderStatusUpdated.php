<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $order_id;
    public string $old_status;
    public string $new_status;
    public ?string $name;
    public ?string $customer_name;
    public float $total;
    public ?string $payment_method;
    public array $vendor_ids;

    public function __construct(Order $order, string $oldStatus)
    {
        $this->order_id = $order->id;
        $this->old_status = $oldStatus;
        $this->new_status = $order->status;
        $this->name = $order->name;
        $this->customer_name = $order->name;
        $this->total = $order->total;
        $this->payment_method = $order->payment_method;

        // Obtener IDs únicos de vendors que tienen productos en este pedido
        $this->vendor_ids = $order->items()
            ->with('product:id,user_id')
            ->get()
            ->pluck('product.user_id')
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Transmitir en los canales privados de cada vendor afectado
     * Y en el canal público "orders" para que el admin también lo reciba.
     */
    public function broadcastOn(): array
    {
        $channels = array_map(
            fn(int $vendorId) => new PrivateChannel("vendor.{$vendorId}"),
            $this->vendor_ids
        );

        // También notificar al admin en el canal público
        $channels[] = new Channel('orders');

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'order-status-updated';
    }
}
