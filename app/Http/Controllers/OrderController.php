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
                $oldStatus = $order->status;
                $order->status = $request->input('status');
                $order->save();
            }
            
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
}
