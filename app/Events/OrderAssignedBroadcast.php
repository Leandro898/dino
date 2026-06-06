<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class OrderAssignedBroadcast implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Order $order;
    public int $vendor_id;

    public function __construct(Order $order, int $vendor_id)
    {
        $this->order = $order;
        $this->vendor_id = $vendor_id;
        
        // Store in cache so SSE endpoint can send it immediately
        $eventData = [
            'order_id' => $this->order->id,
            'order_number' => $this->order->id,
            'vendor_id' => $this->vendor_id,
            'status' => $this->order->status,
            'total' => $this->order->total,
            'name' => $this->order->name,
        ];
        
        $cacheKey = "vendor_orders_pending:{$vendor_id}";
        $pendingOrders = Cache::get($cacheKey, []);
        $pendingOrders[] = $eventData;
        Cache::put($cacheKey, $pendingOrders, now()->addMinutes(5));
        
        error_log("💾 Cached order event for vendor {$vendor_id}: {$this->order->id}");
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('vendor.' . $this->vendor_id),
        ];
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith(): array
    {
        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->id,
            'vendor_id' => $this->vendor_id,
            'status' => $this->order->status,
            'total' => $this->order->total,
            'name' => $this->order->name,
        ];
    }

    /**
     * The name of the broadcast event.
     */
    public function broadcastAs(): string
    {
        return 'order-assigned';
    }
}
