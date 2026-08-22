<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withCommands([
        __DIR__ . '/../app/Console/Commands',
    ])
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->validateCsrfTokens(except: [
            'mercadopago/webhook',
            'nave/webhook',
            'broadcasting/auth',  // Exclude broadcast authentication from CSRF verification
        ]);

        // Registrar visitas reales de usuarios (filtra bots automáticamente)
        $middleware->appendToGroup('web', \App\Http\Middleware\TrackPageView::class);
        $middleware->appendToGroup('web', \App\Http\Middleware\RedirectIfDelivery::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
