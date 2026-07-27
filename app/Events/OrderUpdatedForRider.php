<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderUpdatedForRider implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $order_id;
    public string $status;
    public string $customer_name;
    public float $total;
    public int $rider_id;
    public bool $is_new_assignment;
    public ?string $vendor_name;
    public ?string $vendor_address;
    public ?string $customer_address;

    public float $shipping_cost;
    public string $payment_method;

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
        $this->shipping_cost = (float) $order->shipping_cost;
        $this->payment_method = $order->payment_method ?? 'efectivo';
        
        $order->loadMissing('vendor');
        $this->vendor_name = optional($order->vendor)->name ?? 'Comercio';
        $this->vendor_address = optional($order->vendor)->address ?? 'Sin dirección de comercio';
        $this->customer_address = $order->address;
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
