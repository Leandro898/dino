<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CustomRequestMessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $requestId;
    public $messageText;
    public $senderName;

    /**
     * Create a new event instance.
     */
    public function __construct($requestId)
    {
        $this->requestId = $requestId;

        try {
            $customRequest = \App\Models\CustomRequest::find($requestId);
            if ($customRequest) {
                $latestMessage = $customRequest->messages()
                    ->where('is_system_message', false)
                    ->latest()
                    ->first();
                if ($latestMessage) {
                    $this->messageText = str()->limit($latestMessage->message ?? '', 50);
                    $this->senderName = $latestMessage->sender_type === 'user' ? 'Cliente' : 'Admin';
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error building CustomRequestMessageSent broadcast data', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('custom-request.' . $this->requestId),
            new Channel('orders'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'message.sent';
    }
}
