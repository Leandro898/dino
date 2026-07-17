<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class CategoryController extends Controller
{
    public function show($slug)
    {
        $categories = [
            'comida' => 'Comida',
            'regalos' => 'Regalos y más',
            'super-hogar' => 'Super y hogar',
            'farmacia' => 'Farmacia',
            'lo-que-sea' => 'Lo que sea',
            'retira-envia' => 'Retirá y envía',
        ];

        $categoryName = $categories[$slug] ?? null;
        if (!$categoryName) {
            abort(404);
        }

        $categoryKeywords = [
            'comida' => ['comida', 'pan', 'pizza', 'empanada', 'hamburguesa', 'pollo', 'pastel', 'snack'],
            'regalos' => ['regalo', 'torta', 'cumpleaños', 'detalle', 'confitería'],
            'super-hogar' => ['super', 'hogar', 'limpieza', 'almacen', 'leche', 'cafe', 'azucar', 'aceite'],
            'farmacia' => ['farmacia', 'medicina', 'vitamina', 'analgésico', 'ibuprofeno', 'paracetamol'],
            'lo-que-sea' => [],
            'retira-envia' => ['envío', 'retiro'],
        ];

        $keywords = $categoryKeywords[$slug] ?? [];
        $query = Product::where('is_active', true);

        if (!empty($keywords)) {
            $query->where(function ($q) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $q->orWhere('name', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('description', 'LIKE', '%' . $keyword . '%');
                }
            });
        }

        $products = $query->latest()->paginate(12);

        return view('category', compact('slug', 'categoryName', 'products'));
    }
}
