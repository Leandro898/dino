<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SupportMessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $deliveryUserId;
    public $messageId;
    public $messageText;
    public $senderId;
    public $senderName;
    public $createdAt;

    /**
     * Create a new event instance.
     */
    public function __construct($message)
    {
        $this->deliveryUserId = $message->delivery_user_id;
        $this->messageId = $message->id;
        $this->messageText = $message->message;
        $this->senderId = $message->sender_id;
        $this->senderName = $message->sender->name ?? 'Repartidor';
        $this->createdAt = $message->created_at ? $message->created_at->toIso8601String() : now()->toIso8601String();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('support.' . $this->deliveryUserId),
            new Channel('orders'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'support-message.sent';
    }
}
