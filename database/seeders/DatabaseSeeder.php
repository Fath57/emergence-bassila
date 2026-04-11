<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            CountrySeeder::class,
            SectorSeeder::class,
            SkillSeeder::class,
            SettingSeeder::class,
            AdminSeeder::class,
            ProfileSeeder::class,
        ]);
    }
}
