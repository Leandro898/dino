<?php

namespace App\Livewire;

use App\Events\OrderStatusUpdated;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;

class VendorOrdersTable extends Component
{
    use WithPagination;

    public $perPage = 10;

    #[\Livewire\Attributes\On('order-updated')]
    public function refreshOrders()
    {
        $this->resetPage();
    }

    public function updateOrderStatus($orderId, $newStatus)
    {
        $order = Order::findOrFail($orderId);
        $oldStatus = $order->status;
        $order->update(['status' => $newStatus]);

        Log::info('Order status updated by vendor', [
            'order_id' => $orderId,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'vendor_id' => Auth::id(),
        ]);

        $this->dispatch('order-updated');
    }

    public function render()
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'vendor') {
            return view('livewire.vendor-orders-table', [
                'orders' => collect(),
                'totalOrders' => 0,
            ]);
        }

        // Mostrar el historial de pedidos asignados o en proceso por el vendor (ignoramos 'pending')
        $orders = Order::query()
            ->with(['items.product.user', 'user', 'deliveryRider'])
            ->where('status', '!=', 'pending')
            ->where(function ($query) use ($user) {
                $query->whereHas('items.product', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                })->orWhere('vendor_id', $user->id);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        $totalOrders = Order::query()
            ->where('status', '!=', 'pending')
            ->where(function ($query) use ($user) {
                $query->whereHas('items.product', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                })->orWhere('vendor_id', $user->id);
            })
            ->count();

        return view('livewire.vendor-orders-table', [
            'orders' => $orders,
            'totalOrders' => $totalOrders,
        ]);
    }
}
