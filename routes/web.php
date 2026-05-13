<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ShippingZoneController;
use App\Models\Product;
use Illuminate\Support\Str;

// Evita MethodNotAllowed cuando ngrok muestra su interstitial y envía POST a rutas de login.
Route::post('/admin/login', fn() => redirect()->to(url('/admin/login')));
Route::post('/panel/login', fn() => redirect()->to(url('/panel/login')));

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Home principal con catalogo completo (disponible en /catalogo)
Route::get('/catalogo', [PublicProductController::class, 'index'])->name('catalog');

// Home activa: home-mic
Route::view('/', 'home-mic')->name('home');
Route::get('/home/buscar-productos', [PublicProductController::class, 'homeSearchProducts'])
    ->name('home.search.products');

// Categoria interna: Supermercados (productos importados desde Carrefour Almacen)
Route::get('/categoria/supermercados', [PublicProductController::class, 'supermarkets'])
    ->name('categories.supermarkets');

// Categoria interna: Farmacia (productos importados desde PedidosYa)
Route::get('/categoria/farmacia', [PublicProductController::class, 'pharmacy'])
    ->name('categories.pharmacy');

Route::get('/categoria/{categorySlug}', [PublicProductController::class, 'carrefourCategory'])
    ->whereIn('categorySlug', ['almacen', 'desayuno-y-merienda'])
    ->name('categories.carrefour');

// Home paralela estilo menu mobile (prueba)
Route::view('/home-paralela', 'home-preview-glovo')->name('home.parallel');
Route::view('/home-mic', 'home-mic')->name('home.mic');
// Categorías desde home-mic
Route::get('/categoria/{slug}', function ($slug) {
    $categories = [
        'comida' => 'Comida',
        'regalos' => 'Regalos y más',
        'super-hogar' => 'Super y hogar',
        'farmacia' => 'Farmacia',
        'lo-que-sea' => 'Lo que sea',
        'retira-envia' => 'Retirá y envía',
    ];

    $categoryName = $categories[$slug] ?? null;
    if (!$categoryName) {
        abort(404);
    }

    $categoryKeywords = [
        'comida' => ['comida', 'pan', 'pizza', 'empanada', 'hamburguesa', 'pollo', 'pastel', 'snack'],
        'regalos' => ['regalo', 'torta', 'cumpleaños', 'detalle', 'confitería'],
        'super-hogar' => ['super', 'hogar', 'limpieza', 'almacén', 'leche', 'café', 'azúcar', 'aceite'],
        'farmacia' => ['farmacia', 'medicina', 'vitamina', 'analgésico', 'ibuprofeno', 'paracetamol'],
        'lo-que-sea' => [],
        'retira-envia' => ['envío', 'retiro'],
    ];

    $keywords = $categoryKeywords[$slug] ?? [];
    $query = Product::where('is_active', true);

    if (!empty($keywords)) {
        $query->where(function ($q) use ($keywords) {
            foreach ($keywords as $keyword) {
                $q->orWhere('name', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('description', 'LIKE', '%' . $keyword . '%');
            }
        });
    }

    $products = $query->latest()->paginate(12);

    return view('category', compact('slug', 'categoryName', 'products'));
})->name('category.show');
Route::get('/pedido-voz', function () {
    $pedido = (string) request('pedido', '');
    $normalize = function (string $value): string {
        return Str::of($value)
            ->lower()
            ->ascii()
            ->replaceMatches('/[^a-z0-9\s]/', ' ')
            ->replaceMatches('/\s+/', ' ')
            ->trim()
            ->value();
    };

    $normalizedPedido = $normalize($pedido);
    $pedidoTokens = array_values(array_filter(
        preg_split('/\s+/', $normalizedPedido) ?: [],
        fn(string $token) => mb_strlen($token) >= 3 && !in_array($token, [
            'que',
            'con',
            'para',
            'por',
            'del',
            'las',
            'los',
            'una',
            'uno',
            'unos',
            'unas',
            'quiero',
            'necesito',
            'dame',
        ], true)
    ));

    $categoryHints = [
        'comida' => ['comida', 'pan', 'pizz', 'empan', 'hamburg', 'pollo', 'pastel'],
        'regalos' => ['regalo', 'gift', 'torta', 'cumple', 'detalle'],
        'super hogar' => ['super', 'hogar', 'limpieza', 'almacen', 'leche', 'cafe', 'azucar'],
        'farmacia' => ['farmacia', 'medic', 'vitamina', 'analg', 'ibuprof', 'paracetam'],
        'bebidas' => ['coca', 'fanta', 'sprite', 'agua', 'cerveza', 'vino', 'fernet', 'bebida', 'gaseosa'],
        'snacks' => ['snack', 'papas', 'chocolate', 'alfajor', 'gallet', 'caramelo'],
    ];

    $mentionedCategories = collect($categoryHints)
        ->filter(function (array $keywords, string $category) use ($normalizedPedido, $pedidoTokens) {
            if (str_contains($normalizedPedido, $category)) {
                return true;
            }

            foreach ($keywords as $keyword) {
                foreach ($pedidoTokens as $token) {
                    if ($token === $keyword || str_contains($token, $keyword) || str_contains($keyword, $token)) {
                        return true;
                    }
                }
            }

            return false;
        })
        ->keys()
        ->values()
        ->all();

    $suggestedProducts = Product::query()
        ->where('is_active', true)
        ->latest()
        ->get()
        ->map(function (Product $product) use ($normalize, $normalizedPedido, $pedidoTokens, $mentionedCategories, $categoryHints) {
            $normalizedName = $normalize($product->name);
            $nameTokens = array_values(array_filter(preg_split('/\s+/', $normalizedName) ?: []));
            $score = 0;

            if ($normalizedPedido !== '' && str_contains($normalizedName, $normalizedPedido)) {
                $score += 8;
            }

            foreach ($pedidoTokens as $token) {
                if (str_contains($normalizedName, $token)) {
                    $score += 4;
                    continue;
                }

                if (mb_strlen($token) >= 4) {
                    foreach ($nameTokens as $nameToken) {
                        $distance = levenshtein($token, $nameToken);
                        if ($distance <= 1) {
                            $score += 2;
                            break;
                        }
                    }
                }
            }

            foreach ($mentionedCategories as $category) {
                $keywords = $categoryHints[$category] ?? [];
                foreach ($keywords as $keyword) {
                    if (str_contains($normalizedName, $keyword)) {
                        $score += 3;
                        break;
                    }
                }
            }

            return [
                'product' => $product,
                'score' => $score,
            ];
        })
        ->filter(fn(array $item) => $item['score'] > 0)
        ->sortByDesc('score')
        ->take(6)
        ->pluck('product')
        ->values();

    if ($suggestedProducts->isEmpty()) {
        $suggestedProducts = Product::query()
            ->where('is_active', true)
            ->latest()
            ->take(6)
            ->get();
    }

    return view('voice-order-result', compact('pedido', 'suggestedProducts'));
})->name('voice.order.result');

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
