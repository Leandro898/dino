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

        return view('cart', compact('cart'));
    }

    public function add(Product $product)
    {
        $cart = session()->get('cart', []);
        $requestedQuantity = max(1, (int) request()->input('quantity', 1));

        if ($product->isRaffle()) {
            if (!(bool) config('raffle.sales_enabled', true)) {
                return $this->errorResponse('La venta de numeros del sorteo esta temporalmente cerrada.');
            }

            $raffleNumber = $this->normalizeRaffleNumber((string) request()->input('raffle_number', ''));

            if ($raffleNumber === null) {
                return $this->errorResponse('Debes ingresar un numero valido entre 000 y 099.');
            }

            if ($this->isRaffleNumberSold($product->id, $raffleNumber)) {
                return $this->errorResponse("El numero {$raffleNumber} ya fue vendido.");
            }

            $itemKey = $product->id . '-' . $raffleNumber;
            if (isset($cart[$itemKey])) {
                return $this->errorResponse("El numero {$raffleNumber} ya esta en tu carrito.");
            }

            $cart[$itemKey] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'quantity' => 1,
                'price' => $product->adjusted_price,
                'image' => $product->image,
                'is_raffle' => true,
                'raffle_number' => $raffleNumber,
            ];

            session()->put('cart', $cart);

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Numero {$raffleNumber} agregado al carrito",
                    'quantity' => 1,
                ]);
            }

            return redirect()->route('cart.index');
        }

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
                'is_raffle' => false,
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

        return redirect()->route('cart.index');
    }

    public function update(Request $request, $id)
    {
        $cart = session()->get('cart', []);
        $quantity = (int) $request->input('quantity');

        if (isset($cart[$id]) && $quantity > 0) {
            if (!empty($cart[$id]['is_raffle'])) {
                if ($request->ajax()) {
                    return response()->json(['success' => false, 'message' => 'Los numeros de sorteo no permiten cambiar cantidad.'], 422);
                }

                throw ValidationException::withMessages([
                    'quantity' => 'Los numeros de sorteo no permiten cambiar cantidad.',
                ]);
            }

            $cart[$id]['quantity'] = $quantity;
            session()->put('cart', $cart);
        }

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Cantidad actualizada']);
        }

        return redirect()->route('cart.index');
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

    private function normalizeRaffleNumber(string $value): ?string
    {
        $digits = preg_replace('/\D+/', '', $value);

        if ($digits === null || $digits === '') {
            return null;
        }

        if (!ctype_digit($digits)) {
            return null;
        }

        $number = (int) $digits;
        if ($number < 0 || $number > 99) {
            return null;
        }

        return str_pad((string) $number, 3, '0', STR_PAD_LEFT);
    }

    private function isRaffleNumberSold(int $productId, string $raffleNumber): bool
    {
        return OrderItem::query()
            ->where('product_id', $productId)
            ->where('raffle_number', $raffleNumber)
            ->exists();
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
