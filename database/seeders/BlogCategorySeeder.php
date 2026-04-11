<?php

namespace Database\Seeders;

use App\Models\BlogCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BlogCategorySeeder extends Seeder
{
    /**
     * Default French blog categories for Bassila Émergence.
     *
     * Idempotent: firstOrCreate keyed on slug, so re-running the seeder
     * preserves admin-edited names while guaranteeing the baseline set
     * exists. Matches the pattern used by SettingSeeder and
     * RolePermissionSeeder.
     */
    public function run(): void
    {
        $defaults = [
            'Actualités',
            'Éducation',
            'Culture',
            'Économie',
            'Communauté',
            'Projets',
        ];

        foreach ($defaults as $name) {
            BlogCategory::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name],
            );
        }
    }
}
