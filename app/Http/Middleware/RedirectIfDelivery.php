<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfDelivery
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->role === 'delivery') {
            // Permitir rutas de la app de repartidor, logout y auth de websockets
            if (!$request->routeIs('delivery.*') && !$request->routeIs('logout') && !$request->is('broadcasting/auth')) {
                return redirect()->route('delivery.app');
            }
        }

        return $next($request);
    }
}
