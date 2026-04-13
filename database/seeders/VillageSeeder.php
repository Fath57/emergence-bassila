<?php

namespace Database\Seeders;

use App\Models\Village;
use Illuminate\Database\Seeder;

class VillageSeeder extends Seeder
{
    public function run(): void
    {
        $villages = [
            // ── Arrondissement de Bassila ──
            ['name' => 'Bassila',          'arrondissement' => 'Bassila',      'sort_order' => 1],
            ['name' => 'Alédjo-Attakora',  'arrondissement' => 'Bassila',      'sort_order' => 2],
            ['name' => 'Barei',            'arrondissement' => 'Bassila',      'sort_order' => 3],
            ['name' => 'Béssakourou',      'arrondissement' => 'Bassila',      'sort_order' => 4],
            ['name' => 'Gbégourou',        'arrondissement' => 'Bassila',      'sort_order' => 5],
            ['name' => 'Kounouhou',        'arrondissement' => 'Bassila',      'sort_order' => 6],
            ['name' => 'Tchétou',          'arrondissement' => 'Bassila',      'sort_order' => 7],
            ['name' => 'Worogui',          'arrondissement' => 'Bassila',      'sort_order' => 8],

            // ── Arrondissement de Manigri ──
            ['name' => 'Manigri',          'arrondissement' => 'Manigri',      'sort_order' => 1],
            ['name' => 'Bétékoukou',       'arrondissement' => 'Manigri',      'sort_order' => 2],
            ['name' => 'Gbassi',           'arrondissement' => 'Manigri',      'sort_order' => 3],
            ['name' => 'Kikélé',           'arrondissement' => 'Manigri',      'sort_order' => 4],
            ['name' => 'Kpakpaza',         'arrondissement' => 'Manigri',      'sort_order' => 5],
            ['name' => 'Ode',              'arrondissement' => 'Manigri',      'sort_order' => 6],
            ['name' => 'Sème',             'arrondissement' => 'Manigri',      'sort_order' => 7],

            // ── Arrondissement de Pénéssoulou ──
            ['name' => 'Pénéssoulou',      'arrondissement' => 'Pénéssoulou', 'sort_order' => 1],
            ['name' => 'Kolokondé',        'arrondissement' => 'Pénéssoulou', 'sort_order' => 2],
            ['name' => 'Kokobou',          'arrondissement' => 'Pénéssoulou', 'sort_order' => 3],

            // ── Arrondissement de Wawa ──
            ['name' => 'Wawa',             'arrondissement' => 'Wawa',         'sort_order' => 1],
            ['name' => 'Manta',            'arrondissement' => 'Wawa',         'sort_order' => 2],
        ];

        foreach ($villages as $data) {
            Village::firstOrCreate(
                ['name' => $data['name'], 'arrondissement' => $data['arrondissement']],
                ['is_active' => true, 'sort_order' => $data['sort_order']],
            );
        }
    }
}
