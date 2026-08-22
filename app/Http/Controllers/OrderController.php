<?php

namespace App\Http\Controllers;

use App\Events\OrderStatusUpdated;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Verificar autenticación
        if (!auth()->check()) {
            return response()->json(['error' => 'No autenticado'], 401);
        }
        
        // Verificar permisos de admin
        if (auth()->user()->role !== 'admin') {
            return response()->json(['error' => 'No tienes permisos'], 403);
        }
        
        try {
            $order = Order::find($id);
            
            if (!$order) {
                return response()->json(['error' => 'Orden no encontrada'], 404);
            }
            
            // Actualizar estado si se proporciona
            if ($request->has('status')) {
                $order->status = $request->input('status');
            }

            // Actualizar repartidor si se proporciona
            if ($request->has('delivery_user_id')) {
                $ineligibleStatuses = ['completed', 'cancelled'];
                if (in_array($order->status, $ineligibleStatuses)) {
                    return response()->json([
                        'error' => 'No se puede asignar un repartidor a un pedido completado o cancelado.'
                    ], 422);
                }
                
                $newDeliveryUserId = $request->input('delivery_user_id') ?: null;
                
                if ($order->delivery_user_id !== $newDeliveryUserId) {
                    $order->delivery_user_id = $newDeliveryUserId;
                    $order->is_accepted_by_rider = false; // Resetear aceptación para el nuevo rider
                    
                    // Si el pedido estaba en pending, lo pasamos a assigned automáticamente
                    if ($newDeliveryUserId && $order->status === 'pending') {
                        $order->status = 'assigned';
                    }
                }
            }
            
            $order->save();
            
            return response()->json(['message' => 'Orden actualizada correctamente', 'order' => $order]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al actualizar la orden: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Verificar autenticación
        if (!auth()->check()) {
            return response()->json(['error' => 'No autenticado'], 401);
        }
        
        // Verificar permisos de admin
        if (auth()->user()->role !== 'admin') {
            return response()->json(['error' => 'No tienes permisos'], 403);
        }
        
        try {
            $order = Order::find($id);
            
            if (!$order) {
                return response()->json(['error' => 'Orden no encontrada'], 404);
            }
            
            // Eliminar items de la orden
            $order->items()->delete();
            
            // Eliminar la orden
            $order->delete();
            
            return response()->json(['message' => 'Orden eliminada correctamente', 'order_id' => $id]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al eliminar la orden: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Print the specified order ticket (comanda).
     */
    public function printTicket(Order $order)
    {
        $user = auth()->user();
        if (!$user) {
            abort(401);
        }

        if ($user->role !== 'admin') {
            if ($user->role === 'vendor') {
                $isRelated = ($order->vendor_id === $user->id) || $order->items()->whereHas('product', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                })->exists();

                if (!$isRelated) {
                    abort(403, 'No tienes acceso a este pedido.');
                }
            } else {
                abort(403, 'Acceso denegado.');
            }
        }

        $order->load(['items.product', 'vendor']);

        return view('orders.print', compact('order'));
    }
}
