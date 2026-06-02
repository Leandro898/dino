<?php

namespace App\Filament\Client\Resources\ClientOrderResource\Pages;

use App\Filament\Client\Resources\ClientOrderResource;
use Filament\Resources\Pages\ListRecords;

class ListClientOrders extends ListRecords
{
    protected static string $resource = ClientOrderResource::class;

    protected function getHeaderWidgets(): array
    {
        // Inyecta el JS de polling y la variable de conteo
        \Filament\Facades\Filament::registerRenderHook(
            'head.end',
            function () {
                $recentOrders = \App\Models\Order::where('status', 'assigned')
                    ->whereHas('items.product', function ($q) {
                        $q->where('user_id', auth()->id());
                    })->get();
                return view('filament.widgets.vendor-order-polling-js', [
                    'recentOrders' => $recentOrders,
                ]);
            }
        );
        return parent::getHeaderWidgets();
    }
}
