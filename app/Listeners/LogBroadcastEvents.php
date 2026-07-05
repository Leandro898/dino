<?php

namespace App\Listeners;

use Illuminate\Broadcasting\Events\BroadcastingPrivateChannel;
use Illuminate\Broadcasting\Broadcasters\Broadcaster;

class LogBroadcastEvents
{
    public function handle($event)
    {
        \Log::info('BROADCAST EVENT DETECTED', [
            'event_class' => get_class($event),
            'event_data' => $event,
        ]);
    }
}
