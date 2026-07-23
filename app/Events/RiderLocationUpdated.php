<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class RiderLocationUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public int $orderId;
    public float $latitude;
    public float $longitude;
    public int $riderId;

    public function __construct(int $orderId, float $latitude, float $longitude, int $riderId)
    {
        $this->orderId = $orderId;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->riderId = $riderId;
    }

    public function broadcastOn(): array
    {
        return [new \Illuminate\Broadcasting\Channel('order-tracking.' . $this->orderId)];
    }

    public function broadcastAs(): string
    {
        return 'rider-location-updated';
    }
}
