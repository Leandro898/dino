<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class NewOrderCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    public int $order_id;
    public ?string $name;
    public ?string $email;
    public float $total;
    public ?string $customer_name;
    public ?string $payment_method;
    public ?string $created_at;
    public ?string $phone;
    public ?string $address;
    public float $shipping_cost;
    public ?string $status;

    public function __construct(Order $order)
    {
        $this->order_id = $order->id;
        $this->name = $order->name;
        $this->email = $order->email;
        $this->customer_name = $order->name; // For JavaScript compatibility
        $this->total = $order->total;
        $this->payment_method = $order->payment_method;
        $this->created_at = $order->created_at?->toIso8601String();
        $this->phone = $order->phone;
        $this->address = $order->address;
        $this->shipping_cost = $order->shipping_cost ?? 0;
        $this->status = $order->status;
    }

    public function broadcastOn(): array
    {
        return [new Channel('orders')];
    }

    public function broadcastAs(): string
    {
        return 'new-order-created';
    }
}