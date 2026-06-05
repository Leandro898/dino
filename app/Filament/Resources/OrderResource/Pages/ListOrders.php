<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
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
        // In Filament v3 with Livewire 3, we need to reset the table state
        // This forces the table to re-query the database
        $this->resetPage('ordersTablePage');
        \Illuminate\Support\Facades\Log::info('refreshTable called - table should refresh now');
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
