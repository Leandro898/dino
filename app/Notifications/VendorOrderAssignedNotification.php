<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class VendorOrderAssignedNotification extends Notification
{
    use Queueable;

    public function __construct(public Order $order)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): DatabaseMessage
    {
        return new DatabaseMessage([
            'title' => 'Nuevo Pedido Asignado',
            'message' => 'Se te ha asignado un nuevo pedido: ' . $this->order->name,
            'order_id' => $this->order->id,
            'icon' => 'heroicon-o-shopping-bag',
        ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Nuevo Pedido Asignado',
            'message' => 'Se te ha asignado un nuevo pedido: ' . $this->order->name,
            'order_id' => $this->order->id,
        ];
    }
}
