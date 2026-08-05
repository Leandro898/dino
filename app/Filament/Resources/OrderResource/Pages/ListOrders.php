<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected $listeners = [
        'echo:orders,.rider.status.updated' => 'handleStatusUpdate',
        'echo:orders,.new-order-created' => 'handleStatusUpdate',
        'echo:orders,.order-status-updated' => 'handleStatusUpdate',
        'echo:orders,.order.updated.rider' => 'handleStatusUpdate',
    ];

    public function handleStatusUpdate()
    {
        $this->dispatch('$refresh');
    }

    protected static ?string $title = 'Pedidos';

    protected static ?string $breadcrumb = 'Pedidos';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
