<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;
use NotificationChannels\WebPush\WebPushChannel;
use Illuminate\Queue\SerializesModels;

class NewCustomRequestReplyNotification extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;
    public $message;

    public function __construct($message)
    {
        $this->message = $message;
    }

    public function via(object $notifiable): array
    {
        return [WebPushChannel::class];
    }

    public function toWebPush($notifiable, $notification)
    {
        $bodyText = $this->message->is_system_message ? 
            $this->message->message : 
            str()->limit($this->message->message ?? '', 50);

        return (new WebPushMessage)
            ->title('Bari Tienda - Pedido Especial')
            ->icon('/favicon-arg.svg')
            ->body($bodyText)
            ->action('Ver respuesta', 'view_chat')
            ->data(['url' => url('/')]); // Client-side URL is the home page
    }
}
