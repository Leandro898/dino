<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withCommands([
        __DIR__ . '/../app/Console/Commands',
    ])
    ->withMiddleware(function (Middleware $middleware) {
        // Añadimos la excepción para que Mercado Pago pueda enviar el POST
        $middleware->validateCsrfTokens(except: [
            'mercadopago/webhook',
        ]);

        // Registrar visitas reales de usuarios (filtra bots automáticamente)
        $middleware->appendToGroup('web', \App\Http\Middleware\TrackPageView::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
