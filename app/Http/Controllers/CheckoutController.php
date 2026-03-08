<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CheckoutController extends Controller
{
    public function index()
    {
        return view('checkout.index');
    }

    public function process(Request $request)
    {
        // 1. Validación estricta
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'address' => 'required',
            'phone' => 'required',
        ]);

        $cart = session()->get('cart', []);
        if (empty($cart)) return redirect()->back()->with('error', 'El carrito está vacío');

        try {
            DB::beginTransaction();

            // 2. Calcular total general
            $totalGeneral = 0;
            foreach ($cart as $item) {
                $totalGeneral += $item['price'] * $item['quantity'];
            }

            // 3. Crear la Orden (usando tus campos de Order.php)
            $order = Order::create([
                'user_id' => Auth::id(), // Puede ser null si no está logueado
                'name' => $request->name,
                'email' => $request->email,
                'address' => $request->address,
                'phone' => $request->phone,
                'total' => $totalGeneral,
                'status' => 'pending',
                'payment_method' => 'test_manual',
            ]);

            // 4. Crear los Items (usando tus campos de OrderItem.php)
            foreach ($cart as $id => $details) {
                $subtotalItem = $details['price'] * $details['quantity'];

                OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $id,
                    'price'      => $details['price'],
                    'quantity'   => $details['quantity'],
                    'subtotal'   => $subtotalItem, // Este campo es OBLIGATORIO en tu modelo
                ]);
            }

            DB::commit();
            session()->forget('cart');

            // Redirigir a la nueva ruta de agradecimiento
            return redirect()->route('checkout.thankyou');

        } catch (\Exception $e) {
            DB::rollBack();
            // Esto detendrá la ejecución y te mostrará el error exacto de la base de datos
            dd("Error en la base de datos: " . $e->getMessage());
        }
    }

    public function thankyou()
    {
        return view('checkout.thankyou');
    }
}
