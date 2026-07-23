<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Str;

class VoiceOrderController extends Controller
{
    public function index(Request $request)
    {
        $pedido = (string) $request->input('pedido', '');
        $normalize = function (string $value): string {
            return Str::of($value)
                ->lower()
                ->ascii()
                ->replaceMatches('/[^a-z0-9\s]/', ' ')
                ->replaceMatches('/\s+/', ' ')
                ->trim()
                ->value();
        };

        $normalizedPedido = $normalize($pedido);
        $pedidoTokens = array_values(array_filter(
            preg_split('/\s+/', $normalizedPedido) ?: [],
            fn(string $token) => mb_strlen($token) >= 3 && !in_array($token, [
                'que',
                'con',
                'para',
                'por',
                'del',
                'las',
                'los',
                'una',
                'uno',
                'unos',
                'unas',
                'quiero',
                'necesito',
                'dame',
            ], true)
        ));

        $categories = \App\Models\Category::where('is_active', true)->get();
        $categoryHints = [];
        foreach ($categories as $category) {
            // Reemplazamos guiones por espacios para la búsqueda del slug en la frase (ej: super-hogar a super hogar)
            $normalizedSlug = str_replace('-', ' ', $category->slug);
            $categoryHints[$normalizedSlug] = $category->keywords ?? [];
        }

        $mentionedCategories = collect($categoryHints)
            ->filter(function (array $keywords, string $category) use ($normalizedPedido, $pedidoTokens) {
                if (str_contains($normalizedPedido, $category)) {
                    return true;
                }

                foreach ($keywords as $keyword) {
                    foreach ($pedidoTokens as $token) {
                        if ($token === $keyword || str_contains($token, $keyword) || str_contains($keyword, $token)) {
                            return true;
                        }
                    }
                }

                return false;
            })
            ->keys()
            ->values()
            ->all();

        $suggestedProducts = Product::query()
            ->where('is_active', true)
            ->latest()
            ->get()
            ->map(function (Product $product) use ($normalize, $normalizedPedido, $pedidoTokens, $mentionedCategories, $categoryHints) {
                $normalizedName = $normalize($product->name);
                $nameTokens = array_values(array_filter(preg_split('/\s+/', $normalizedName) ?: []));
                $score = 0;

                if ($normalizedPedido !== '' && str_contains($normalizedName, $normalizedPedido)) {
                    $score += 8;
                }

                foreach ($pedidoTokens as $token) {
                    if (str_contains($normalizedName, $token)) {
                        $score += 4;
                        continue;
                    }

                    if (mb_strlen($token) >= 4) {
                        foreach ($nameTokens as $nameToken) {
                            $distance = levenshtein($token, $nameToken);
                            if ($distance <= 1) {
                                $score += 2;
                                break;
                            }
                        }
                    }
                }

                foreach ($mentionedCategories as $category) {
                    $keywords = $categoryHints[$category] ?? [];
                    foreach ($keywords as $keyword) {
                        if (str_contains($normalizedName, $keyword)) {
                            $score += 3;
                            break;
                        }
                    }
                }

                return [
                    'product' => $product,
                    'score' => $score,
                ];
            })
            ->filter(fn(array $item) => $item['score'] > 0)
            ->sortByDesc('score')
            ->take(6)
            ->pluck('product')
            ->values();

        if ($suggestedProducts->isEmpty()) {
            $suggestedProducts = Product::query()
                ->where('is_active', true)
                ->latest()
                ->take(6)
                ->get();
        }

        return view('voice-order-result', compact('pedido', 'suggestedProducts'));
    }
}
