<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@bassilanetwork.test'],
            [
                'name'              => 'Admin Bassila',
                'password'          => Hash::make('admin2024!'),
                'email_verified_at' => now(),
            ]
        );

        $admin->assignRole('admin');
    }
}
