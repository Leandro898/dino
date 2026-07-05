<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUsersSeeder extends Seeder
{
    public function run()
    {
        User::updateOrCreate(
            ['email' => 'test_admin@baritienda.online'],
            [
                'name' => 'Admin Test',
                'password' => Hash::make('test_admin123'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'test_vendor@baritienda.online'],
            [
                'name' => 'Vendor Test',
                'password' => Hash::make('test_vendor123'),
                'role' => 'vendor',
                'email_verified_at' => now(),
            ]
        );
    }
}
