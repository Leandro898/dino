<?php

namespace App\Livewire;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;

class AdminOrdersTable extends Component
{
    use WithPagination;

    public $perPage = 10;
    public $search = '';
    public $filterStatus = '';
    public $selectedOrders = [];
    public $selectAll = false;

    #[\Livewire\Attributes\On('order-updated')]
    public function refreshOrders()
    {
        $this->resetPage();
    }

    #[\Livewire\Attributes\On('new-order-created')]
    public function onNewOrder()
    {
        Log::info('🔔 [LIVEWIRE] Evento new-order-created recibido en AdminOrdersTable');
        $this->resetPage();
    }

    public function updateOrderStatus($orderId, $newStatus)
    {
        $order = Order::findOrFail($orderId);
        $order->update(['status' => $newStatus]);
        
        $this->dispatch('order-updated');
    }

    public function deleteOrder($orderId)
    {
        try {
            $order = Order::findOrFail($orderId);
            $orderNumber = $order->id;
            
            // Delete related order items first (if there's a foreign key constraint)
            $order->items()->delete();
            
            // Delete the order
            $order->delete();
            
            Log::info('🗑️ [DELETE] Orden #' . $orderNumber . ' eliminada');
            session()->flash('success', '✅ Orden #' . $orderNumber . ' eliminada correctamente');
            $this->resetPage();
        } catch (\Exception $e) {
            Log::error('❌ [DELETE ERROR] ' . $e->getMessage());
            session()->flash('error', '❌ Error al eliminar la orden: ' . $e->getMessage());
        }
    }

    public function toggleSelectAll()
    {
        if ($this->selectAll) {
            // Get all order IDs from current page
            $query = Order::query()
                ->with(['items.product', 'user'])
                ->orderBy('created_at', 'desc');

            if ($this->search) {
                $query->where('id', 'like', "%{$this->search}%")
                    ->orWhere('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%");
            }

            if ($this->filterStatus) {
                $query->where('status', $this->filterStatus);
            }

            $orders = $query->paginate($this->perPage);
            $this->selectedOrders = array_map(fn($order) => $order->id, $orders->items());
        } else {
            $this->selectedOrders = [];
        }
    }

    public function deselectAll()
    {
        $this->selectedOrders = [];
        $this->selectAll = false;
    }

    public function deleteSelectedOrders()
    {
        if (empty($this->selectedOrders)) {
            session()->flash('error', '❌ No hay órdenes seleccionadas');
            return;
        }

        try {
            $count = count($this->selectedOrders);
            $orderIds = implode(', #', $this->selectedOrders);
            
            foreach ($this->selectedOrders as $orderId) {
                $order = Order::find($orderId);
                if ($order) {
                    $order->items()->delete();
                    $order->delete();
                }
            }
            
            Log::info('🗑️ [DELETE MULTIPLE] ' . $count . ' órdenes eliminadas: #' . $orderIds);
            session()->flash('success', '✅ ' . $count . ' orden(es) eliminada(s) correctamente');
            $this->selectedOrders = [];
            $this->selectAll = false;
            $this->resetPage();
        } catch (\Exception $e) {
            Log::error('❌ [DELETE MULTIPLE ERROR] ' . $e->getMessage());
            session()->flash('error', '❌ Error al eliminar órdenes: ' . $e->getMessage());
        }
    }

    public function render()
    {
        Log::debug('📊 [RENDER] AdminOrdersTable renderizado');
        
        $user = Auth::user();
        
        if (!$user || $user->role !== 'admin') {
            return view('livewire.admin-orders-table', [
                'orders' => collect(),
            ]);
        }

        $query = Order::query()
            ->with(['items.product', 'user'])
            ->orderBy('created_at', 'desc');

        if ($this->search) {
            $query->where('id', 'like', "%{$this->search}%")
                ->orWhere('name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%");
        }

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        $orders = $query->paginate($this->perPage);
        Log::debug('📊 [RENDER] Órdenes encontradas: ' . $orders->count());

        return view('livewire.admin-orders-table', [
            'orders' => $orders,
        ]);
    }
}
