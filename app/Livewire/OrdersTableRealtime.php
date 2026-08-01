<?php

namespace App\Livewire;

use App\Events\OrderStatusUpdated;
use App\Models\Order;
use Livewire\Component;
use Livewire\WithPagination;

class OrdersTableRealtime extends Component
{
    use WithPagination;

    public $search = '';
    public $filterStatus = '';
    public $perPage = 10;
    public $selectedOrders = [];
    public $selectAll = false;
    public $viewingOrder = null;

    public function viewOrder($orderId)
    {
        $this->viewingOrder = Order::with('items.product', 'user')->find($orderId);
    }

    public function closeViewOrder()
    {
        $this->viewingOrder = null;
    }

    #[\Livewire\Attributes\On('new-order-created')]
    public function onNewOrder()
    {
        $this->resetPage();
    }

    #[\Livewire\Attributes\On('echo:orders,.rider.status.updated')]
    public function onRiderStatusUpdated()
    {
        $this->dispatch('$refresh');
    }

    #[\Livewire\Attributes\On('order-updated')]
    public function refreshOrders()
    {
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedFilterStatus()
    {
        $this->resetPage();
    }

    public function toggleSelectAll()
    {
        if ($this->selectAll) {
            // Obtener solo los IDs de la página actual
            $this->selectedOrders = $this->orders->getCollection()->pluck('id')->map(fn($id) => (string)$id)->toArray();
        } else {
            $this->selectedOrders = [];
        }
    }

    public function deselectAll()
    {
        $this->selectedOrders = [];
        $this->selectAll = false;
    }

    public function deleteOrder($orderId)
    {
        $order = Order::find($orderId);
        if ($order) {
            $order->items()->delete();
            $order->delete();
            $this->dispatch('order-deleted', orderId: $orderId);
        }
    }

    public function deleteSelectedOrders()
    {
        Order::whereIn('id', $this->selectedOrders)->each(function ($order) {
            $order->items()->delete();
            $order->delete();
        });

        $this->selectedOrders = [];
        $this->selectAll = false;
        $this->dispatch('orders-deleted', count: count($this->selectedOrders));
    }

    public function updateOrderStatus($orderId, $newStatus)
    {
        \Illuminate\Support\Facades\Log::info("INTENTANDO ACTUALIZAR ESTADO", ['order_id' => $orderId, 'new_status' => $newStatus]);
        
        $order = Order::find($orderId);
        if ($order) {
            $oldStatus = $order->status;
            $order->update(['status' => $newStatus]);

            $this->dispatch('order-updated', orderId: $orderId, status: $newStatus);
        }
    }

    #[\Livewire\Attributes\Computed]
    public function orders()
    {
        return Order::query()
            ->with('items.product')
            ->when($this->search, fn($q) => $q->where('id', 'like', "%{$this->search}%")
                ->orWhere('name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%"))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->orderByDesc('created_at')
            ->paginate($this->perPage);
    }

    public function render()
    {
        $riders = \App\Models\User::query()
            ->where('role', 'delivery')
            ->where('is_approved', true)
            ->get();

        return view('livewire.orders-table-realtime', [
            'orders' => $this->orders,
            'riders' => $riders,
        ]);
    }
}
