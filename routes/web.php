<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ShippingZoneController;

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Cambiamos la ruta '/' para que use nuestro controlador
Route::get('/', [PublicProductController::class, 'index'])->name('home');

// Acceso a la ruta de los productos con URL amigable
Route::get('/productos/{product:slug}', [PublicProductController::class, 'show'])->name('products.show');
Route::get('/productos/{product:slug}/disponibilidad-sorteo', [PublicProductController::class, 'raffleAvailability'])
    ->name('products.raffle.availability');

// Rutas para el carrito de compras
Route::get('/carrito', [CartController::class, 'index'])->name('cart.index');
Route::post('/carrito/add/{product:id}', [CartController::class, 'add'])->name('cart.add');
Route::post('/carrito/update/{id}', [CartController::class, 'update'])->name('cart.update');
Route::post('/carrito/remove/{id}', [CartController::class, 'remove'])->name('cart.remove');

// Rutas para el proceso de checkout
// Detección automática de zona por calle/altura
Route::get('/shipping/detect-zone', [ShippingZoneController::class, 'detect'])->name('shipping.detect-zone');
Route::get('/shipping/street-suggestions', [ShippingZoneController::class, 'suggestions'])->name('shipping.street-suggestions');

Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout/process', [CheckoutController::class, 'process'])->name('checkout.process');

//Ruta para la página de agradecimiento después del checkout
Route::get('/gracias', [CheckoutController::class, 'thankyou'])->name('checkout.success');

// Ruta para recibir el callback de Mercado Pago
Route::get('/mercadopago/callback', [CheckoutController::class, 'handleMercadoPagoCallback'])->name('mercadopago.callback');

// Ruta para recibir el webhook de Mercado Pago
Route::post('/mercadopago/webhook', [CheckoutController::class, 'handleWebhook'])->name('mercadopago.webhook');

// Rutas para NAVE
// Esta es la que va en el panel de configuración de Nave
Route::post('/nave/webhook', [CheckoutController::class, 'handleNaveWebhook'])->name('nave.webhook');

// Esta es a la que vuelve el usuario después de pagar
Route::get('/nave/callback', [CheckoutController::class, 'handleNaveCallback'])->name('nave.callback');

require __DIR__ . '/auth.php';
