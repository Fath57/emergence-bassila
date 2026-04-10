<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SectorSeeder extends Seeder
{
    public function run(): void
    {
        $sectors = [
            'Agriculture',
            'Commerce',
            'Santé',
            'Éducation',
            'IT & Technologie',
            'Industrie',
            'Finance & Banque',
            'BTP & Immobilier',
            'Transports & Logistique',
            'Médias & Communication',
            'Administration publique',
            'Autre',
        ];

        foreach ($sectors as $name) {
            DB::table('sectors')->insertOrIgnore(['name' => $name]);
        }
    }
}
