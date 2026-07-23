<?php

use App\Http\Controllers\ProfileController;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\PublicProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ShippingZoneController;
use App\Http\Controllers\FoodVendorController;
use App\Http\Controllers\DeliveryAppController;
use App\Http\Controllers\OrderController;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Str;
use App\Http\Controllers\PushNotificationController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\VoiceOrderController;

// Broadcasting auth - usa el endpoint estándar de Laravel que genera la firma Pusher correcta
// La lógica de autorización está en routes/channels.php
Broadcast::routes(['middleware' => ['web', 'auth']]);

$mainHost = parse_url(config('app.url'), PHP_URL_HOST);
if ($mainHost) {
    // Subdominio para la app de repartidores
    Route::domain('repartidor.' . $mainHost)->group(function () {
        Route::get('/', function () {
            return redirect()->route('delivery.app');
        });
    });
}


// Evita MethodNotAllowed cuando ngrok muestra su interstitial y envía POST a rutas de login.
// Route::post('/admin/login', fn() => redirect()->to(url('/admin/login')));
// Route::post('/panel/login', fn() => redirect()->to(url('/panel/login'))); // ¡NO ACTIVAR! Esto rompe el login de Filament Panel

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Rutas para órdenes en el admin
    Route::patch('/api/custom/orders/{orderId}', [OrderController::class, 'update'])->name('orders.update.status');
    Route::delete('/api/custom/orders/{orderId}', [OrderController::class, 'destroy'])->name('orders.destroy.custom');
    Route::get('/orders/{order}/print', [OrderController::class, 'printTicket'])->name('orders.print');

    Route::post('/push-subscribe', [PushNotificationController::class, 'subscribe'])->name('push.subscribe');
});

// Seguimiento público del pedido (URL firmada, accesible sin login)
Route::get('/pedidos/{order}/seguimiento', [\App\Http\Controllers\OrderTrackingController::class, 'showPublic'])
    ->name('orders.tracking.public')
    ->middleware('signed');

Route::post('/guest-push-subscribe', [PushNotificationController::class, 'guestSubscribe'])->name('guest-push.subscribe');


// Home principal: vista tipo app con accesos rápidos
Route::view('/', 'home')->name('home');
Route::get('/home/buscar-productos', [PublicProductController::class, 'homeSearchProducts'])
    ->name('home.search.products');

// Redirecciones por compatibilidad de rutas antiguas
Route::redirect('/categoria/almacen/bebidas', '/bebidas', 301);
Route::redirect('/categoria/almacen', '/almacen', 301);
Route::redirect('/categoria/super-hogar', '/almacen', 301);
Route::redirect('/categoria/farmacia', '/farmacia', 301);
Route::redirect('/categoria/bebidas', '/bebidas', 301);


// Sección de Comidas - Vendedores y menú
Route::get('/comidas', [FoodVendorController::class, 'index'])->name('food-vendors.index');
Route::get('/comidas/{user}', [FoodVendorController::class, 'show'])
    ->where('user', '[0-9]+')
    ->name('food-vendors.show');

// API para búsqueda de categorías
Route::get('/api/categoria/{slug}', [CategoryController::class, 'apiSearch'])->name('category.api.search');
Route::get('/pedido-voz', [VoiceOrderController::class, 'index'])->name('voice.order.result');

// Acceso a la ruta de los productos con URL amigable
Route::get('/productos/{product:slug}', [PublicProductController::class, 'show'])->name('products.show');


// Rutas para el carrito de compras
Route::get('/carrito', [CartController::class, 'index'])->name('cart.index');
Route::post('/carrito/add/{product:id}', [CartController::class, 'add'])->name('cart.add');
Route::post('/carrito/update/{id}', [CartController::class, 'update'])->name('cart.update');
Route::post('/carrito/remove/{id}', [CartController::class, 'remove'])->name('cart.remove');

// Rutas para el proceso de checkout
// Detección automática de zona por calle/altura
Route::get('/shipping/detect-zone', [ShippingZoneController::class, 'detect'])->name('shipping.detect-zone');
Route::get('/shipping/street-suggestions', [ShippingZoneController::class, 'suggestions'])->name('shipping.street-suggestions');
Route::get('/shipping/reverse-geocode', [ShippingZoneController::class, 'reverseGeocode'])->name('shipping.reverse-geocode');

// Rate limiting para rutas de compra críticas
Route::middleware(['throttle:30,1'])->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout/process', [CheckoutController::class, 'process'])->name('checkout.process');
});

//Ruta para la página de agradecimiento después del checkout
Route::get('/gracias', [CheckoutController::class, 'thankyou'])->name('checkout.success');

// Ruta para recibir el callback de Mercado Pago
Route::get('/mercadopago/callback', [CheckoutController::class, 'handleMercadoPagoCallback'])->name('mercadopago.callback');

// Ruta para recibir el webhook de Mercado Pago (sin rate limiting para webhooks)
Route::post('/mercadopago/webhook', [CheckoutController::class, 'handleWebhook'])->name('mercadopago.webhook');

// Rutas para NAVE
// Esta es la que va en el panel de configuración de Nave
Route::post('/nave/webhook', [CheckoutController::class, 'handleNaveWebhook'])->name('nave.webhook');

// Esta es a la que vuelve el usuario después de pagar
Route::get('/nave/callback', [CheckoutController::class, 'handleNaveCallback'])->name('nave.callback');

// App de repartidores (MVP instalable tipo PWA)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/repartidor/app', [DeliveryAppController::class, 'index'])
        ->name('delivery.app');

    Route::get('/repartidor/pedidos/ultimo', [DeliveryAppController::class, 'latest'])
        ->name('delivery.orders.latest');
    Route::post('/repartidor/pedidos/{order}/aceptar', [DeliveryAppController::class, 'acceptOrder'])->name('delivery.orders.accept');
    Route::post('/repartidor/pedidos/{order}/rechazar', [DeliveryAppController::class, 'rejectOrder'])->name('delivery.orders.reject');
    Route::post('/repartidor/pedidos/{order}/retirado', [DeliveryAppController::class, 'markAsPickedUp'])->name('delivery.orders.pickedup');
    Route::post('/repartidor/pedidos/{order}/entregado', [DeliveryAppController::class, 'markAsDelivered'])->name('delivery.orders.delivered');

    Route::get('/repartidor/soporte/mensajes', [DeliveryAppController::class, 'getSupportMessages'])
        ->name('delivery.support.messages');
    Route::post('/repartidor/soporte/mensajes', [DeliveryAppController::class, 'sendSupportMessage'])
        ->name('delivery.support.send');

    // Nueva ruta para actualizar ubicación en tiempo real
    Route::post('/repartidor/ubicacion', [DeliveryAppController::class, 'updateLocation'])
        ->name('delivery.location.update');
});

require __DIR__ . '/auth.php';

// Catch-all route para las categorías limpias (debe ir al final)
Route::get('/{slug}', [CategoryController::class, 'show'])->name('category.show');
