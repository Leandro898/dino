<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class GlobalRiderLocationUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public int $riderId;
    public string $riderName;
    public float $latitude;
    public float $longitude;

    public function __construct(int $riderId, string $riderName, float $latitude, float $longitude)
    {
        $this->riderId = $riderId;
        $this->riderName = $riderName;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('admin.map')
        ];
    }

    public function broadcastAs(): string
    {
        return 'global-rider-location-updated';
    }
}
