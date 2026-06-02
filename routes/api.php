<?php
use Illuminate\Support\Facades\Route;
use App\Models\Order;
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
