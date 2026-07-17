<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    public function index()
    {
        $cart = $this->syncCartPrices(session()->get('cart', []));
        session()->put('cart', $cart);

        if (empty($cart)) {
            return redirect()->route('home')->with('error', 'Tu carrito está vacío. Agrega algunos productos para continuar.');
        }

        // Redirigir directamente a checkout en lugar de mostrar la página de carrito
        return redirect()->route('checkout.index');
    }

    public function add(Product $product)
    {
        $cart = session()->get('cart', []);
        
        if (!empty($cart)) {
            $firstProductId = array_key_first($cart);
            $firstProductInCart = Product::find((int) $firstProductId);
            
            if ($firstProductInCart && $firstProductInCart->user_id !== $product->user_id) {
                if (request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Solo puedes pedir de un comercio a la vez. Vacía tu carrito para agregar este producto.',
                    ], 422);
                }
                return redirect()->back()->with('error', 'Solo puedes pedir de un comercio a la vez. Vacía tu carrito para agregar este producto.');
            }
        }

        $requestedQuantity = max(1, (int) request()->input('quantity', 1));

        $itemKey = (string) $product->id;

        if (isset($cart[$itemKey])) {
            $cart[$itemKey]['quantity'] += $requestedQuantity;
        } else {
            $cart[$itemKey] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'quantity' => $requestedQuantity,
                'price' => $product->adjusted_price,
                'image' => $product->image,
            ];
        }

        session()->put('cart', $cart);

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Producto agregado al carrito',
                'quantity' => $requestedQuantity,
            ]);
        }

        return redirect()->route('checkout.index');
    }

    public function update(Request $request, $id)
    {
        $cart = session()->get('cart', []);
        $quantity = (int) $request->input('quantity');

        if (isset($cart[$id]) && $quantity > 0) {
            $cart[$id]['quantity'] = $quantity;
            session()->put('cart', $cart);
        }

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Cantidad actualizada']);
        }

        return redirect()->back();
    }

    public function remove($id)
    {
        $cart = session()->get('cart', []);
        if (isset($cart[$id])) {
            unset($cart[$id]);
            session()->put('cart', $cart);
        }
        return redirect()->back();
    }



    private function errorResponse(string $message)
    {
        if (request()->ajax()) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 422);
        }

        return redirect()->back()->withErrors(['raffle_number' => $message]);
    }

    private function syncCartPrices(array $cart): array
    {
        $productIds = collect($cart)
            ->map(fn(array $item, string|int $key) => (int) ($item['product_id'] ?? $key))
            ->filter(fn(int $id) => $id > 0)
            ->unique()
            ->values();

        if ($productIds->isEmpty()) {
            return $cart;
        }

        $products = Product::query()
            ->whereIn('id', $productIds)
            ->get()
            ->keyBy('id');

        foreach ($cart as $key => $item) {
            $productId = (int) ($item['product_id'] ?? $key);
            $product = $products->get($productId);

            if (!$product) {
                continue;
            }

            $cart[$key]['price'] = $product->adjusted_price;
        }

        return $cart;
    }
}
