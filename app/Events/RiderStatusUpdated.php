<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RiderStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $riderId;
    public bool $isOnline;
    public ?string $name;
    public ?float $latitude;
    public ?float $longitude;

    /**
     * Create a new event instance.
     */
    public function __construct(int $riderId, bool $isOnline, ?string $name = null, ?float $latitude = null, ?float $longitude = null)
    {
        $this->riderId = $riderId;
        $this->isOnline = $isOnline;
        $this->name = $name;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('orders'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'rider.status.updated';
    }
}
