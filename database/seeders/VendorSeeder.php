<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class VendorSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'masivo@baritienda.online'],
            [
                'name'              => 'Masivo',
                'email'             => 'masivo@baritienda.online',
                'password'          => bcrypt('masivo123'),
                'role'              => 'vendor',
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Usuario vendedor creado:');
        $this->command->info('  Email:    masivo@baritienda.online');
        $this->command->info('  Password: masivo123');
        $this->command->info('  Panel:    /panel');
    }
}
