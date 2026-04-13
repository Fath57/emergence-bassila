<?php

namespace Database\Seeders;

use App\Models\Village;
use Illuminate\Database\Seeder;

class VillageSeeder extends Seeder
{
    public function run(): void
    {
        $villages = [
            // ── Arrondissement de Bassila (Centre & environs) ──
            ['name' => 'Bassila Abiguédou', 'arrondissement' => 'Bassila', 'sort_order' => 1],
            ['name' => 'Bassila Allan',     'arrondissement' => 'Bassila', 'sort_order' => 2],
            ['name' => 'Bassila Bakabaka',  'arrondissement' => 'Bassila', 'sort_order' => 3],
            ['name' => 'Adjimon',           'arrondissement' => 'Bassila', 'sort_order' => 4],
            ['name' => 'Adjiro',            'arrondissement' => 'Bassila', 'sort_order' => 5],
            ['name' => 'Aoro-Lokpa',        'arrondissement' => 'Bassila', 'sort_order' => 6],
            ['name' => 'Aoro-Nago',         'arrondissement' => 'Bassila', 'sort_order' => 7],
            ['name' => 'Appi',              'arrondissement' => 'Bassila', 'sort_order' => 8],
            ['name' => 'Assion',            'arrondissement' => 'Bassila', 'sort_order' => 9],
            ['name' => 'Barei',             'arrondissement' => 'Bassila', 'sort_order' => 10],
            ['name' => 'Béssakourou',       'arrondissement' => 'Bassila', 'sort_order' => 11],
            ['name' => 'Biguina Akpassa',   'arrondissement' => 'Bassila', 'sort_order' => 12],
            ['name' => 'Diapéri',           'arrondissement' => 'Bassila', 'sort_order' => 13],
            ['name' => 'Gbégourou',         'arrondissement' => 'Bassila', 'sort_order' => 14],
            ['name' => 'Kounouhou',         'arrondissement' => 'Bassila', 'sort_order' => 15],
            ['name' => 'Prekété',           'arrondissement' => 'Bassila', 'sort_order' => 16],
            ['name' => 'Tchétou',           'arrondissement' => 'Bassila', 'sort_order' => 17],
            ['name' => 'Worogui',           'arrondissement' => 'Bassila', 'sort_order' => 18],

            // ── Arrondissement d'Alédjo ──
            ['name' => 'Alédjo-Koura',      'arrondissement' => 'Alédjo',  'sort_order' => 1],
            ['name' => 'Akaradé',           'arrondissement' => 'Alédjo',  'sort_order' => 2],
            ['name' => 'Boutou',            'arrondissement' => 'Alédjo',  'sort_order' => 3],
            ['name' => 'Igadougou',         'arrondissement' => 'Alédjo',  'sort_order' => 4],
            ['name' => 'Kadégué',           'arrondissement' => 'Alédjo',  'sort_order' => 5],
            ['name' => 'Kouaté',            'arrondissement' => 'Alédjo',  'sort_order' => 6],
            ['name' => 'Nibadara',          'arrondissement' => 'Alédjo',  'sort_order' => 7],
            ['name' => 'Partago',           'arrondissement' => 'Alédjo',  'sort_order' => 8],
            ['name' => 'Tchimbéri',         'arrondissement' => 'Alédjo',  'sort_order' => 9],

            // ── Arrondissement de Manigri ──
            ['name' => 'Manigri-Centre',    'arrondissement' => 'Manigri', 'sort_order' => 1],
            ['name' => 'Manigri-Igbomakro', 'arrondissement' => 'Manigri', 'sort_order' => 2],
            ['name' => 'Bétékoukou',       'arrondissement' => 'Manigri', 'sort_order' => 3],
            ['name' => 'Gbassi',           'arrondissement' => 'Manigri', 'sort_order' => 4],
            ['name' => 'Kikélé',           'arrondissement' => 'Manigri', 'sort_order' => 5],
            ['name' => 'Kpakpaza',         'arrondissement' => 'Manigri', 'sort_order' => 6],
            ['name' => 'Ode',              'arrondissement' => 'Manigri', 'sort_order' => 7],
            ['name' => 'Sème',             'arrondissement' => 'Manigri', 'sort_order' => 8],

            // ── Arrondissement de Pénéssoulou ──
            ['name' => 'Pénéssoulou',      'arrondissement' => 'Pénéssoulou', 'sort_order' => 1],
            ['name' => 'Bayakou',          'arrondissement' => 'Pénéssoulou', 'sort_order' => 2],
            ['name' => 'Bodi',             'arrondissement' => 'Pénéssoulou', 'sort_order' => 3],
            ['name' => 'Kodowari',         'arrondissement' => 'Pénéssoulou', 'sort_order' => 4],
            ['name' => 'Kolokondé',        'arrondissement' => 'Pénéssoulou', 'sort_order' => 5],
            ['name' => 'Nagayilé',         'arrondissement' => 'Pénéssoulou', 'sort_order' => 6],
            ['name' => 'Nioro',            'arrondissement' => 'Pénéssoulou', 'sort_order' => 7],
            ['name' => 'Penelan',          'arrondissement' => 'Pénéssoulou', 'sort_order' => 8],
            ['name' => 'Salmanga',         'arrondissement' => 'Pénéssoulou', 'sort_order' => 9],
            ['name' => 'Kokobou',          'arrondissement' => 'Pénéssoulou', 'sort_order' => 10],
        ];

        foreach ($villages as $data) {
            Village::firstOrCreate(
                ['name' => $data['name'], 'arrondissement' => $data['arrondissement']],
                ['is_active' => true, 'sort_order' => $data['sort_order']],
            );
        }
    }
}