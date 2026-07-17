<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class VendorDashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static string $view = 'filament.pages.vendor-dashboard';

    protected static ?string $navigationLabel = 'Escritorio';
    protected static ?string $title = 'Escritorio';

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();
        return $user && $user->role === 'vendor';
    }

    public function getHeading(): string
    {
        return '';
    }

    public function mount()
    {
        // Only vendors can access this page
        if (auth()->user()?->role !== 'vendor') {
            abort(403, 'Acceso denegado. Esta sección es exclusiva para vendedores.');
        }
    }
}
