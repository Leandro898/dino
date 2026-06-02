<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewOrderForVendor implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $order;
    public $vendorId;

    public function __construct(Order $order)
    {
        $this->order = $order;
        $this->vendorId = $order->user_id;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('vendor.' . $this->vendorId);
    }

    public function broadcastWith()
    {
        return [
            'order_id' => $this->order->id,
            'name' => $this->order->name,
            'total' => $this->order->total,
        ];
    }

    public function broadcastAs()
    {
        return 'new-order';
    }
}
