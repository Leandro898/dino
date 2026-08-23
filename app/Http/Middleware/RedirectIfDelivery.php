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
            // Permitir rutas de la app de repartidor, logout, auth de websockets y rutas de verificacion de email
            $allowedRoutes = ['delivery.*', 'logout', 'verification.*'];
            
            $isAllowed = collect($allowedRoutes)->contains(fn ($route) => $request->routeIs($route)) 
                || $request->is('broadcasting/auth');

            if (!$isAllowed) {
                return redirect()->route('delivery.app');
            }
        }

        return $next($request);
    }
}
