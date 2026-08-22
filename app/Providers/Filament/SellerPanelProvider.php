<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
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

class SellerPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $mainHost = parse_url(config('app.url'), PHP_URL_HOST) ?? 'localhost';
        
        return $panel
            ->id('seller')
            ->domain('vendedor.' . $mainHost)
            ->path('') // Sirve en la raíz del subdominio
            ->login()
            ->registration(\App\Filament\Pages\Auth\VendorRegistration::class)
            ->favicon(asset('favicon-arg.svg'))
            ->databaseNotifications()
            ->colors([
                'primary' => Color::Indigo, // Un color diferente para el vendedor
            ])
            ->pages([
                \App\Filament\Pages\VendorDashboard::class,
                \App\Filament\Pages\VendorOrders::class,
            ])
            ->resources([
                \App\Filament\Resources\ProductResource::class,
                \App\Filament\Resources\CustomRequestResource::class,
                \App\Filament\Resources\OrderResource::class,
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
                    '<link rel="manifest" href="/vendedor-manifest.json">' .
                    '<meta name="theme-color" content="#4F46E5">' .
                    '<meta name="vapid-public-key" content="' . config('webpush.vapid.public_key') . '">' .
                    '<script>window.authUserRole = "{{ auth()->user()?->role ?? \'guest\' }}";</script>' .
                    '<script>' .
                    'window.ReverbConfig = {' .
                        'key: "' . config('broadcasting.connections.reverb.key') . '",' .
                        'wsPort: ' . config('broadcasting.connections.reverb.options.port', 8080) . ',' .
                        'wssPort: 443,' .
                        'forceTLS: ' . (config('broadcasting.connections.reverb.options.scheme') === 'https' ? 'true' : 'false') .
                    '};' .
                    '</script>'
                ),
            )
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn (): string => Blade::render(
                    '<script src="/js/pwa.js"></script>' .
                    '<script>
                        window.addEventListener("beforeinstallprompt", (e) => e.preventDefault());
                        if ("serviceWorker" in navigator) {
                            window.addEventListener("load", function() {
                                navigator.serviceWorker.register("/vendedor-sw.js").then(function(registration) {
                                    // Silencioso
                                }, function(err) {
                                    // Silencioso
                                });
                            });
                        }
                    </script>'
                )
            );
    }
}
