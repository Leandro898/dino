<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        $appUrl = (string) config('app.url', '');
        $appPath = trim((string) parse_url($appUrl, PHP_URL_PATH), '/');

        if (config('app.env') === 'production' || str_starts_with($appUrl, 'https://')) {
            URL::forceScheme('https');
        }

        if ($appPath !== '') {
            $prefixedPath = '/' . $appPath;

            Livewire::setScriptRoute(function ($handle) use ($prefixedPath) {
                return Route::get($prefixedPath . '/livewire/livewire.js', $handle);
            });

            Livewire::setUpdateRoute(function ($handle) use ($prefixedPath) {
                return Route::post($prefixedPath . '/livewire/update', $handle);
            });
        }

        // Register Order Observer
        \App\Models\Order::observe(\App\Observers\OrderObserver::class);

        // Customize email verification
        VerifyEmail::toMailUsing(function (object $notifiable, string $url) {
            if ($notifiable->role === 'delivery') {
                return (new MailMessage)
                    ->subject('Verifica tu correo como Repartidor en BariTienda')
                    ->greeting('¡Hola ' . $notifiable->name . '!')
                    ->line('Gracias por unirte al equipo de BariTienda como repartidor. Para continuar y poder ver los pedidos, primero debes verificar tu correo.')
                    ->action('Verificar Correo Electrónico', $url)
                    ->line('Si no solicitaste crear una cuenta con nosotros, puedes ignorar este correo.')
                    ->salutation('Saludos, el equipo de BariTienda');
            }

            return (new MailMessage)
                ->subject('Verifica tu cuenta en BariTienda')
                ->greeting('¡Hola ' . $notifiable->name . '!')
                ->line('Por favor, haz clic en el botón de abajo para verificar tu dirección de correo electrónico y acceder a tu cuenta.')
                ->action('Verificar Correo Electrónico', $url)
                ->line('Si no creaste una cuenta, puedes ignorar este correo.')
                ->salutation('Saludos, BariTienda');
        });
    }
}
