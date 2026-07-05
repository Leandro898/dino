<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Log;
use Livewire\Component;

class OrderNotificationListener extends Component
{
    #[\Livewire\Attributes\On('refresh-orders')]
    public function refreshOrders()
    {
        Log::info('OrderNotificationListener: refreshOrders called');
        $this->dispatch('refresh-orders');
    }

    #[\Livewire\Attributes\On('play-sound')]
    public function playSound()
    {
        Log::info('OrderNotificationListener: playSound called');
        $this->dispatch('play-notification-sound');
    }

    public function render()
    {
        return view('livewire.order-notification-listener');
    }
}
