<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class VendorOrders extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationLabel = 'Pedidos';

    protected static string $view = 'filament.pages.vendor-orders';

    protected static ?string $title = 'Pedidos';

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();
        return $user && $user->role === 'vendor';
    }

    public static function getNavigationSort(): ?int
    {
        return 1;
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('create')
                ->label('Nuevo Pedido')
                ->icon('heroicon-o-plus')
                ->url(\App\Filament\Resources\OrderResource::getUrl('create')),
        ];
    }
}
