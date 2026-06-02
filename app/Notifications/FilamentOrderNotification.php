<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FilamentOrderNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    protected $order;

    public function __construct($order)
    {
        $this->order = $order;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return \Filament\Notifications\Notification::make()
            ->title('Nuevo pedido asignado')
            ->body("Has recibido el pedido #{$this->order->id} de {$this->order->name}")
            ->success()
            ->actions([
                \Filament\Notifications\Actions\Action::make('view')
                    ->label('Ver pedido')
                    ->url(\App\Filament\Resources\OrderResource::getUrl('view', ['record' => $this->order])),
            ])
            ->getDatabaseMessage();
    }
}
