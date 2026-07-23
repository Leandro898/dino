<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Product;
use Illuminate\View\View;

class FoodVendorController extends Controller
{
    public function index(): View
    {
        // Vendedores con rol 'vendor' que tengan al menos un producto activo
        // Excluye a Masivo (user_id 6) por ahora
        $vendors = User::query()
            ->where('role', 'vendor')
            ->where('is_masivo', false)
            ->whereHas('products', function ($query) {
                $query->where('is_active', true);
            })
            ->with(['products' => function ($query) {
                $query->where('is_active', true);
            }])
            ->latest()
            ->paginate(12);

        return view('food-vendors.index', [
            'vendors' => $vendors,
            'categoryName' => 'Comidas',
        ]);
    }

    public function show(User $user): View
    {
        // Verifica que sea vendedor y tenga productos
        abort_unless($user->role === 'vendor', 404);

        $products = Product::query()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->latest()
            ->paginate(12);

        abort_if($products->isEmpty() && $products->currentPage() === 1, 404);

        return view('food-vendors.menu', [
            'vendor' => $user,
            'products' => $products,
        ]);
    }
}
