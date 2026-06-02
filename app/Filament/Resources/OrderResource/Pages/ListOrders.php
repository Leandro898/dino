<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Livewire\Attributes\On;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    #[On('refresh-orders-table')]
    public function refreshTable(): void
    {
        // Esta función se dispara por el evento de JS y refresca Livewire
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
