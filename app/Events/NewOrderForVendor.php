<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewOrderForVendor implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $order;
    public $vendorId;

    public function __construct(Order $order, int $vendorId)
    {
        $this->order = $order;
        $this->vendorId = $vendorId;
        
        \Illuminate\Support\Facades\Log::info('NewOrderForVendor event created', [
            'order_id' => $order->id,
            'vendor_id' => $vendorId,
            'channel' => 'vendor.' . $vendorId,
            'event_name' => 'new-order',
        ]);
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
