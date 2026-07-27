<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class DeliveryAppController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(function (Request $request, $next) {
                $role = optional($request->user())->role;
                if (!in_array($role, ['admin', 'vendor', 'delivery'], true)) {
                    abort(403);
                }
                return $next($request);
            }),
        ];
    }

    public function index(Request $request): View
    {
        if ($request->user()->role === 'delivery' && !$request->user()->is_approved) {
            return view('delivery.pending-approval');
        }

        return view('delivery.app');
    }
}
