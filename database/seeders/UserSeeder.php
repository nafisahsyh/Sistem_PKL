<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Jalankan seeder.
     */
    public function run(): void
    {
        User::create([
            'nama' => 'Nafisaida',
            'username' => 'adminkece',
            'email' => 'aidasekarningrum@gmail.com',
            'password' => 'admin123',
            'role' => 'super_admin',
        ]);
    }
}
