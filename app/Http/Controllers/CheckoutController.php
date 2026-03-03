<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function index()
    {
        // Aquí podrías pasar los productos del carrito a la vista
        return view('checkout.index');
    }

    public function process(Request $request)
    {
        // Aquí procesarás los datos del formulario y crearás el pago en Mercado Pago
    }
}
