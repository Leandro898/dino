<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected $listeners = [
        'echo:orders,.rider.status.updated' => '$refresh',
    ];

    protected static ?string $title = 'Pedidos';

    protected static ?string $breadcrumb = 'Pedidos';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
