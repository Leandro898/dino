<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
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

    private function detectCategory(string $name): string
    {
        $normalized = mb_strtolower($name);
        $rules = [
            'Cigarrillos' => ['cigarr', 'marlboro', 'box', '20', 'parisiennes', 'camel', 'philip morris'],
            'Bebidas' => ['coca', 'gaseosa', 'fernet', 'cerveza', 'vino', 'agua', 'sprite', 'pepsi', 'gin', 'terma', 'combo'],
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

    private function almacenBeverageKeywords(): array
    {
        return config('beverages.keywords', []);
    }

    private function determineCategoryLabel(Product $product): string
    {
        if ($product->external_source === 'carrefour' && filled($product->external_category)) {
            return match ($product->external_category) {
                'almacen' => 'Supermercados',
                'desayuno-y-merienda' => 'Desayuno y merienda',
                default => 'Otros',
            };
        }

        return $this->detectCategory($product->name);
    }

    private function masivoCatalogQuery(bool $beverages): Builder
    {
        $keywords = $this->almacenBeverageKeywords();

        return Product::query()
            ->whereHas('user', function ($q) {
                $q->where('is_masivo', true);
            })
            ->where('is_active', true)
            ->where(function (Builder $query) use ($keywords, $beverages) {
                if ($beverages) {
                    $query->whereIn('external_category', ['bebidas', 'drinks']);

                    foreach ($keywords as $keyword) {
                        $pattern = '%' . mb_strtolower($keyword) . '%';

                        $query->orWhere(function (Builder $keywordQuery) use ($pattern) {
                            $keywordQuery->whereRaw('LOWER(COALESCE(name, "")) LIKE ?', [$pattern])
                                ->orWhereRaw('LOWER(COALESCE(description, "")) LIKE ?', [$pattern]);
                        });
                    }

                    return;
                }

                $query->where(function (Builder $categoryQuery) {
                    $categoryQuery->whereNull('external_category')
                        ->orWhereNotIn('external_category', ['bebidas', 'drinks']);
                });

                foreach ($keywords as $keyword) {
                    $pattern = '%' . mb_strtolower($keyword) . '%';

                    $query->where(function (Builder $keywordQuery) use ($pattern) {
                        $keywordQuery->whereRaw('LOWER(COALESCE(name, "")) NOT LIKE ?', [$pattern])
                            ->whereRaw('LOWER(COALESCE(description, "")) NOT LIKE ?', [$pattern]);
                    });
                }
            });
    }

    public function show(Product $product)
    {
        return view('products.show', compact('product'));
    }
}
