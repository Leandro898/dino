<?php

namespace App\Filament\Pages;

use App\Models\User;
use Filament\Pages\Page;

class RiderMapPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-map';
    
    protected static ?string $navigationLabel = 'Mapa de Repartidores';
    
    protected static ?string $title = 'Seguimiento en Tiempo Real';

    protected static string $view = 'filament.pages.rider-map-page';

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && in_array($user->role, ['admin', 'manager']);
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();
        return $user && in_array($user->role, ['admin', 'manager']);
    }

    public array $riders = [];

    public function mount()
    {
        $this->riders = User::query()
            ->where('role', 'delivery')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get()
            ->filter(fn($user) => $user->isOnline())
            ->map(function ($rider) {
                return [
                    'id' => $rider->id,
                    'name' => $rider->name,
                    'lat' => $rider->latitude,
                    'lng' => $rider->longitude,
                ];
            })
            ->values()
            ->toArray();
    }
}
