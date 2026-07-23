<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailVerificationPromptController extends Controller
{
    /**
     * Display the email verification prompt.
     */
    public function __invoke(Request $request): RedirectResponse|View
    {
        $host = $request->getHost();
        $isDelivery = str_starts_with($host, 'repartidor.') 
            || $request->query('role') === 'delivery' 
            || $request->query('role') === 'repartidor'
            || ($request->user() && $request->user()->role === 'delivery');

        if ($request->user()->hasVerifiedEmail()) {
            return $request->user()->role === 'delivery'
                ? redirect()->intended(route('delivery.app', absolute: false))
                : redirect()->intended(route('dashboard', absolute: false));
        }

        return $isDelivery
            ? view('auth.verify-email-delivery')
            : view('auth.verify-email');
    }
}
