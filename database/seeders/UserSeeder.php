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
            'nama' => 'Nafisah Aida',
            'username' => 'superadminksm',
            'email' => 'aidasekarningrum@gmail.com',
            'password' => 'pklksm2025',
            'role' => 'super_admin',
        ]);
    }
}
