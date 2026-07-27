<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;

class CatalogService
{
    /**
     * Detect a product category from its name using keyword matching.
     */
    public function detectCategory(string $name): string
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

    /**
     * Determine the category label for a product, taking into account external sources.
     */
    public function determineCategoryLabel(Product $product): string
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

    /**
     * Build a query for the massive catalog, optionally filtering for beverages.
     */
    public function buildMasivoCatalogQuery(bool $beverages = false): Builder
    {
        $keywords = config('beverages.keywords', []);

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
}
