<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;
use NotificationChannels\WebPush\WebPushChannel;
use Illuminate\Queue\SerializesModels;

class NewCustomRequestMessagePushNotification extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;
    public $message;
    public $customRequest;

    /**
     * Create a new notification instance.
     */
    public function __construct($message, $customRequest)
    {
        $this->message = $message;
        $this->customRequest = $customRequest;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [WebPushChannel::class];
    }

    public function toWebPush($notifiable, $notification)
    {
        $senderName = $this->message->sender_type === 'user' ? 
            ($this->message->sender->name ?? 'Cliente') : 'Admin';
            
        $url = $notifiable->role === 'vendor' 
            ? 'https://vendedor.' . parse_url(config('app.url'), PHP_URL_HOST) . '/custom-requests'
            : url('/admin/custom-requests');

        return (new WebPushMessage)
            ->title('Mensaje de ' . $senderName)
            ->icon('/favicon.ico')
            ->body(str()->limit($this->message->message ?? '', 50))
            ->action('Ver mensaje', 'view_message')
            ->data(['url' => $url]);
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
