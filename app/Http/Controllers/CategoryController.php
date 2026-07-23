<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class CategoryController extends Controller
{
    public function show($slug, \Illuminate\Http\Request $request)
    {
        $category = \App\Models\Category::where('slug', $slug)->where('is_active', true)->firstOrFail();

        $categoryName = $category->name;
        $keywords = $category->keywords ?? [];
        $search = trim((string) $request->string('q'));

        $query = Product::where('is_active', true);

        if (!empty($keywords)) {
            $query->where(function ($q) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $q->orWhere('name', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('description', 'LIKE', '%' . $keyword . '%');
                }
            });
        }

        if ($search !== '') {
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', '%' . $search . '%')
                  ->orWhere('description', 'LIKE', '%' . $search . '%');
            });
        }

        // Requerimiento del usuario: en las categorías (salvo comidas, que va por otro lado)
        // solo mostrar buscador y no mostrar ningún producto hasta que se busque.
        if ($search === '') {
            $products = app(\Illuminate\Pagination\LengthAwarePaginator::class, [
                'items' => collect([]),
                'total' => 0,
                'perPage' => 12,
                'currentPage' => 1,
                'options' => ['path' => $request->url(), 'query' => $request->query()]
            ]);
        } else {
            $products = $query->latest()->paginate(12)->withQueryString();
        }

        return view('category', compact('slug', 'categoryName', 'products', 'search'));
    }

    public function apiSearch($slug, Request $request)
    {
        $category = \App\Models\Category::where('slug', $slug)->where('is_active', true)->first();
        if (!$category) {
            return response()->json(['products' => []]);
        }

        $search = trim((string) $request->string('q'));
        if (mb_strlen($search) < 2) {
            return response()->json(['products' => []]);
        }

        $keywords = $category->keywords ?? [];
        $query = Product::where('is_active', true);

        if (!empty($keywords)) {
            $query->where(function ($q) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $q->orWhere('name', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('description', 'LIKE', '%' . $keyword . '%');
                }
            });
        }

        $query->where(function($q) use ($search) {
            $q->where('name', 'LIKE', '%' . $search . '%')
              ->orWhere('description', 'LIKE', '%' . $search . '%');
        });

        $products = $query->latest()->limit(24)->get();

        $payload = $products->map(function (Product $product) use ($category) {
            return [
                'name' => $product->name,
                'price' => '$' . number_format((float) $product->adjusted_price, 0, ',', '.'),
                'image' => $product->image_src,
                'url' => route('products.show', ['product' => $product->slug]),
                'category' => $category->name,
            ];
        });

        return response()->json(['products' => $payload]);
    }
}
