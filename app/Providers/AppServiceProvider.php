<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;

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
    }
}
