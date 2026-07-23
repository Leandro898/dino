<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Category::truncate();

        $categories = [
            [
                'slug' => 'comida',
                'name' => 'Comida',
                'keywords' => ['comida', 'pan', 'pizza', 'empanada', 'hamburguesa', 'pollo', 'pastel', 'snack', 'pizz', 'empan', 'hamburg'],
            ],
            [
                'slug' => 'almacen',
                'name' => 'Super y hogar',
                'keywords' => ['super', 'hogar', 'limpieza', 'almacen', 'leche', 'cafe', 'azucar', 'aceite'],
            ],
            [
                'slug' => 'farmacia',
                'name' => 'Farmacia',
                'keywords' => ['farmacia', 'medicina', 'vitamina', 'analgésico', 'ibuprofeno', 'paracetamol', 'medic', 'analg', 'ibuprof', 'paracetam'],
            ],
            [
                'slug' => 'bebidas',
                'name' => 'Bebidas',
                'keywords' => ['coca', 'fanta', 'sprite', 'agua', 'cerveza', 'vino', 'fernet', 'bebida', 'gaseosa'],
            ],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}
