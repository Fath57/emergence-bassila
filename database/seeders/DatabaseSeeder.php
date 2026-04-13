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
            RolePermissionSeeder::class,
            CountrySeeder::class,
            VillageSeeder::class,
            SectorSeeder::class,
            SkillSeeder::class,
            SettingSeeder::class,
            BlogCategorySeeder::class,
            AdminSeeder::class,
            ProfileSeeder::class,
        ]);
    }
}
