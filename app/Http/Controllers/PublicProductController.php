<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\View\View;
use Illuminate\Http\Request;

class PublicProductController extends Controller
{
    public function index(): View
    {
         // Traemos solo los productos activos y los mas recientes primero
         $products = Product::where('is_active', true)->latest()->get();

         return view('welcome', compact('products'));
    }
}
