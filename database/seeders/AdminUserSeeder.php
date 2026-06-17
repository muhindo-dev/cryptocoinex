<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@devroots.ac.ug'],
            [
                'name' => 'DevRoots Admin',
                'username' => 'devroots_admin',
                'password' => Hash::make('Admin@DevRoots2025!'),
                'is_admin' => true,
                'role' => 'admin',
            ]
        );
    }
}
