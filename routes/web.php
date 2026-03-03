<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicProductController;

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

require __DIR__.'/auth.php';
