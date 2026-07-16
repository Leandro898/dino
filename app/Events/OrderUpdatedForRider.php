<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderUpdatedForRider implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $order_id;
    public string $status;
    public string $customer_name;
    public float $total;
    public int $rider_id;
    public bool $is_new_assignment;

    /**
     * Create a new event instance.
     */
    public function __construct(Order $order, bool $isNewAssignment = false)
    {
        $this->order_id = $order->id;
        $this->status = $order->status;
        $this->customer_name = $order->name;
        $this->total = (float) $order->total;
        $this->rider_id = (int) $order->delivery_user_id;
        $this->is_new_assignment = $isNewAssignment;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("App.Models.User.{$this->rider_id}")
        ];
    }

    /**
     * Get the broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'order-updated-for-rider';
    }
}
