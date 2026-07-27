<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicProductController extends Controller
{
    public function supermarkets(Request $request): View
    {
        $search = trim((string) $request->string('q'));

        $products = Product::query()
            ->where('external_source', 'carrefour')
            ->whereIn('external_category', ['almacen', 'desayuno-y-merienda'])
            ->where('is_active', true)
            ->when($search !== '', function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            })
            ->orderBy('name')
            ->paginate(24)
            ->withQueryString();

        return view('categories.supermarkets', [
            'categoryTitle' => 'Supermercados',
            'categorySlug' => 'almacen',
            'products' => $products,
            'search' => $search,
        ]);
    }

    public function homeSearchProducts(Request $request): JsonResponse
    {
        $search = trim((string) $request->string('q'));

        if (mb_strlen($search) < 2) {
            return response()->json(['products' => []]);
        }

        $products = Product::query()
            ->where('is_active', true)
            ->where(function ($query) {
                // Solo incluir Farmacia, Comidas (vendedores locales) y productos propios (sin external_source)
                $query->where(function ($pharmacy) {
                    $pharmacy
                        ->where('external_source', 'pedidosya')
                        ->where('external_category', 'farmacia');
                })->orWhereHas('user', function ($vendor) {
                    $vendor->where('role', 'vendor')->where('is_masivo', false);
                })->orWhereNull('external_source');
            })
            ->where('name', 'like', '%' . $search . '%')
            ->orderBy('name')
            ->limit(24)
            ->get();

        $payload = $products->map(function (Product $product) {
            return [
                'name' => $product->name,
                'price' => '$' . number_format((float) $product->adjusted_price, 0, ',', '.'),
                'image' => $product->image_src,
                'url' => route('products.show', ['product' => $product->slug]),
                'category' => $product->external_category === 'farmacia' ? 'Farmacia' : 'Comidas',
            ];
        });

        return response()->json([
            'products' => $payload,
        ]);
    }

    public function show(Product $product)
    {
        return view('products.show', compact('product'));
    }
}
