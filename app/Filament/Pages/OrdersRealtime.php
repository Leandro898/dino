<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class OrdersRealtime extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Pedidos (Tiempo Real)';
    protected static ?string $title = 'Panel de Pedidos en Tiempo Real';
    protected static ?int $navigationSort = 1;
    
    protected static string $view = 'filament.pages.orders-realtime';

    public function getHeading(): string
    {
        return 'Panel de Pedidos con WebSockets';
    }

    protected function getViewData(): array
    {
        return [];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->role === 'admin';
    }

    public static function getRoutePath(): string
    {
        return 'orders-realtime';
    }
}
