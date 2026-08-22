<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default() // El panel admin ahora es default, login y dashboard en /admin
            ->id('admin')
            ->path('admin')
            ->login()
            ->favicon(asset('favicon-arg.svg'))
            ->databaseNotifications()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->pages([
                \App\Filament\Pages\AdminDashboard::class,
                \App\Filament\Pages\OrdersRealtime::class,
                \App\Filament\Pages\AdminOrders::class,
                \App\Filament\Pages\PriceControl::class,
                \App\Filament\Pages\ProjectBoardPage::class,
                \App\Filament\Pages\RiderMapPage::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => Blade::render('@vite([\'resources/js/filament-echo.js\'])' .
                    '<link rel="manifest" href="/manifest.json">' .
                    '<meta name="theme-color" content="#8b5cf6">' .
                    '<meta name="vapid-public-key" content="' . config('webpush.vapid.public_key') . '">' .
                    '<link rel="apple-touch-icon" href="https://ui-avatars.com/api/?name=B&size=192&background=8b5cf6&color=fff">' .
                    '<script>window.authUserRole = "{{ auth()->user()?->role ?? \'guest\' }}";</script>'
                ),
            )
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn (): string => Blade::render('<script src="/js/pwa.js"></script>')
            );
    }
}
