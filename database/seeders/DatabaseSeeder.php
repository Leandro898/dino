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
    // 1. Agregá esta línea arriba de todo
    \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();

    DB::table('users')->truncate();

    User::factory()->create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    // 2. Agregá esta línea al final
    \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();
}
}
