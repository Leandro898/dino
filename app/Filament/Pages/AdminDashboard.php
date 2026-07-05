<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class AdminDashboard extends BaseDashboard
{
    protected static ?string $title = 'Escritorio';

    public static function canAccess(): bool
    {
        return auth()->user()?->role === 'admin';
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();
        return $user && $user->role === 'admin';
    }

    public function mount()
    {
        // Redirect vendor users to their own dashboard
        if (auth()->user()?->role !== 'admin') {
            return redirect()->to(VendorDashboard::getUrl());
        }
    }

    public function getWidgets(): array
    {
        if (auth()->user()?->role !== 'admin') {
            return [];
        }
        return parent::getWidgets();
    }
}
