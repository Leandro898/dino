<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;

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

// Acceso a la ruta de los productos
Route::get('/productos/{product}', [PublicProductController::class, 'show'])->name('products.show');

// Rutas para el carrito de compras
Route::get('/carrito', [CartController::class, 'index'])->name('cart.index');
Route::post('/carrito/add/{product}', [CartController::class, 'add'])->name('cart.add');
Route::post('/carrito/remove/{id}', [CartController::class, 'remove'])->name('cart.remove');

// Rutas para el proceso de checkout
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout/process', [CheckoutController::class, 'process'])->name('checkout.process');

//Ruta para la página de agradecimiento después del checkout
Route::get('/gracias', [CheckoutController::class, 'thankyou'])->name('checkout.thankyou');

require __DIR__.'/auth.php';
