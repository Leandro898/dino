<?php

namespace App\Livewire;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NotificationBell extends Component
{
    public $unreadCount = 0;
    public $isOpen = false;

    #[\Livewire\Attributes\On('new-order-assigned')]
    public function onNewOrderAssigned($data)
    {
        $this->unreadCount++;
        $this->dispatch('bell-updated', count: $this->unreadCount);
    }

    #[\Livewire\Attributes\On('cleanup-order-tracking')]
    public function onCleanupTracking($disappearedIds)
    {
        if ($this->unreadCount > 0) {
            $this->unreadCount--;
        }
        $this->dispatch('bell-updated', count: $this->unreadCount);
    }

    public function toggleOpen()
    {
        $this->isOpen = !$this->isOpen;
    }

    public function clearNotifications()
    {
        $this->unreadCount = 0;
        $this->isOpen = false;
    }

    public function render()
    {
        $user = Auth::user();
        
        if (!$user || $user->role !== 'vendor') {
            return view('livewire.notification-bell', [
                'unreadCount' => 0,
            ]);
        }

        // Obtener total de pedidos activos
        $totalOrders = Order::query()
            ->where('status', '!=', 'pending')
            ->whereHas('items.product', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->count();

        return view('livewire.notification-bell', [
            'unreadCount' => $this->unreadCount,
            'totalOrders' => $totalOrders,
        ]);
    }
}
