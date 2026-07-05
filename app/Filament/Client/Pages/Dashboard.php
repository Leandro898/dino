<?php

namespace App\Filament\Client\Pages;

use Filament\Pages\Page;
use App\Filament\Client\Resources\ClientOrderResource;

class Dashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static string $view = 'filament.client.pages.dashboard';

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();
        // Solo admin ve el dashboard, los vendors no
        return $user && $user->role === 'admin';
    }

    public function getHeading(): string
    {
        return 'Panel de Vendedor';
    }
}
