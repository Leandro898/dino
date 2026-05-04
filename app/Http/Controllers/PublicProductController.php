<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicProductController extends Controller
{
    public function index(): View
    {
         // Traemos solo los productos activos y los mas recientes primero
         $products = Product::where('is_active', true)->latest()->get();

         $raffleProduct = Product::query()
             ->where('is_active', true)
             ->where('slug', 'sorteo-helado-rapa-nui-1kg')
             ->first();

         if (!$raffleProduct) {
             $raffleProduct = Product::query()
                 ->where('is_active', true)
                 ->where('is_raffle', true)
                 ->latest()
                 ->first();
         }

         $categoryOrder = [
             'Cigarrillos',
             'Bebidas',
             'Accesorios',
             'Snacks',
             'Otros',
         ];

         $categorizedProducts = $products
             ->groupBy(fn (Product $product) => $this->detectCategory($product->name))
             ->sortBy(fn ($_, $category) => array_search($category, $categoryOrder, true) !== false
                 ? array_search($category, $categoryOrder, true)
                 : 999)
             ->sortKeysUsing(function ($a, $b) use ($categoryOrder) {
                 $indexA = array_search($a, $categoryOrder, true);
                 $indexB = array_search($b, $categoryOrder, true);
                 $indexA = $indexA === false ? 999 : $indexA;
                 $indexB = $indexB === false ? 999 : $indexB;
                 return $indexA <=> $indexB;
             });

            return view('welcome', compact('products', 'categorizedProducts', 'raffleProduct'));
    }

    private function detectCategory(string $name): string
    {
        $normalized = mb_strtolower($name);

        $rules = [
            'Cigarrillos' => ['cigarr', 'marlboro', 'box', '20', 'parisiennes', 'camel', 'philip morris'],
            'Bebidas' => ['coca', 'gaseosa', 'fernet', 'cerveza', 'vino', 'agua', 'sprite', 'pepsi', 'combo'],
            'Accesorios' => ['encendedor', 'filtro', 'sedas', 'papelillo', 'pouch', 'boquilla', 'hielo'],
            'Snacks' => ['snack', 'papas', 'chocolate', 'caramelo', 'gallet', 'alfajor'],
        ];

        foreach ($rules as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($normalized, $keyword)) {
                    return $category;
                }
            }
        }

        return 'Otros';
    }

    public function show(Product $product)
    {
        // Esto cargará automáticamente el producto por su ID gracias al Route Model Binding
        return view('products.show', compact('product'));
    }

    public function raffleAvailability(Request $request, Product $product): JsonResponse
    {
        if (!$product->isRaffle()) {
            return response()->json([
                'ok' => false,
                'message' => 'Este producto no es un sorteo por numero.',
            ], 422);
        }

        $normalizedNumber = $this->normalizeRaffleNumber((string) $request->input('raffle_number', ''));
        if ($normalizedNumber === null) {
            return response()->json([
                'ok' => false,
                'available' => false,
                'message' => 'Ingresa un numero valido entre 000 y 099.',
            ], 422);
        }

        $sold = OrderItem::query()
            ->where('product_id', $product->id)
            ->where('raffle_number', $normalizedNumber)
            ->exists();

        return response()->json([
            'ok' => true,
            'raffle_number' => $normalizedNumber,
            'available' => !$sold,
            'message' => $sold
                ? "El numero {$normalizedNumber} ya fue elegido."
                : "El numero {$normalizedNumber} esta disponible.",
        ]);
    }

    private function normalizeRaffleNumber(string $value): ?string
    {
        $digits = preg_replace('/\D+/', '', $value);

        if ($digits === null || $digits === '' || !ctype_digit($digits)) {
            return null;
        }

        $number = (int) $digits;
        if ($number < 0 || $number > 99) {
            return null;
        }

        return str_pad((string) $number, 3, '0', STR_PAD_LEFT);
    }
}
