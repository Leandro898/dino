<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // SEEDERS DESACTIVADOS PARA SEGURIDAD EN PRODUCCIÓN.
        // No se ejecutan para evitar sobrescribir o limpiar datos reales en el servidor (usuarios, categorías, etc.).
        // Si necesitas volver a sembrar datos de base en el futuro, puedes descomentar las líneas de abajo.
        
        /*
        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();

        $this->call(CategorySeeder::class);
        $this->call(ShippingZoneSeeder::class);

        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();
        */
    }
}
