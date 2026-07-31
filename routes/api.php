<?php
use Illuminate\Support\Facades\Route;
use App\Models\Order;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\Api\WhatsAppWebhookController;
use Illuminate\Http\Request;

Route::middleware('auth:sanctum')->get('/vendor/new-orders', function (Request $request) {
    $user = $request->user();
    if ($user->role === 'admin') {
        // Para admin, opcional: mostrar todos los pedidos nuevos (puedes ajustar si lo necesitas)
        $count = Order::count();
        return ['assigned_orders_count' => $count];
    }
    // Vendedor: contar todos los pedidos ASIGNADOS de sus productos
    $count = Order::where('status', 'assigned')
        ->whereHas('items.product', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })
        ->count();
    return ['assigned_orders_count' => $count];
});

// Webhook para recibir mensajes de WhatsApp (Evolution API, Twilio, UltraMsg, etc.)
Route::post('/whatsapp/webhook', [WhatsAppWebhookController::class, 'handle']);
