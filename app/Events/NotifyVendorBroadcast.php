<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotifyVendorBroadcast implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Order $order,
        public int $vendorId,
        public string $title = 'Nuevo Pedido Asignado',
        public ?string $message = null,
    ) {
        $this->message ??= "Se te ha asignado un nuevo pedido: {$order->name}";
    }

    public function broadcastOn()
    {
        return new PrivateChannel('App.Models.User.' . $this->vendorId);
    }

    public function broadcastWith()
    {
        return [
            'type' => 'notification',
            'title' => $this->title,
            'message' => $this->message,
            'order_id' => $this->order->id,
            'icon' => 'heroicon-o-shopping-bag',
            'color' => 'success',
            'status' => 'success',
            'duration' => 'persistent',
        ];
    }

    public function broadcastAs()
    {
        return 'database-notifications.sent';
    }
}
