<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Livewire\Attributes\On;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected static ?string $title = 'Pedidos';

    protected static ?string $breadcrumb = 'Pedidos';

    #[On('refresh-orders-table')]
    public function refreshTable(): void
    {
        \Illuminate\Support\Facades\Log::info('refreshTable called - resetting page to trigger table refresh');
        
        // In Filament v3 with Livewire 3, we need to reset the table state
        // This forces the table to re-query the database
        $this->resetPage('ordersTablePage');
        
        \Illuminate\Support\Facades\Log::info('refreshTable completed - page reset');
    }

    #[On('tableColumnStateUpdated')]
    public function onTableColumnStateUpdated($rowId, $columnName, $state): void
    {
        error_log("🔄 Table column updated: row={$rowId}, column={$columnName}, state={$state}");

        if ($columnName === 'status') {
            $order = Order::find($rowId);
            if ($order) {
                error_log("📝 Status column changed for order {$order->id}: {$order->status} → {$state}");
                
                // Manually dispatch the event if status changed to 'assigned'
                if ($state === 'assigned' && $order->status !== 'assigned') {
                    error_log("🚀 ORDER ASSIGNED via SelectColumn: {$order->id}");
                    
                    // Dispatch the event manually
                    $vendors = $order->items
                        ->load('product')
                        ->map->product
                        ->pluck('user')
                        ->unique('id');

                    error_log('📦 Found ' . count($vendors) . ' vendors');

                    foreach ($vendors as $vendor) {
                        error_log('📤 Dispatching event to vendor: ' . $vendor->id);
                        \App\Events\OrderAssignedBroadcast::dispatch($order, $vendor->id);
                        $vendor->notify(new \App\Notifications\OrderAssignedNotification($order));
                        error_log('🔔 Notification sent to vendor: ' . $vendor->id);
                    }
                    
                    error_log('✅ All events dispatched successfully');
                }
            }
        }
    }

    protected function getHeaderActions(): array
    {
        $user = auth()->user();
        if ($user && $user->role === 'admin') {
            return [
                Actions\CreateAction::make(),
            ];
        }
        return [];
    }
}
