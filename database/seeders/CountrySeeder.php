<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        // sort_order: 1 = Bénin + voisins, 2 = Afrique de l'Ouest, 3 = reste Afrique, 4 = Europe/Amériques/Asie
        $countries = [
            // ─── Bénin & voisins immédiats (sort 1) ───
            ['name' => 'Bénin',               'code' => 'BJ', 'flag' => '🇧🇯', 'sort_order' => 1],
            ['name' => 'Togo',                'code' => 'TG', 'flag' => '🇹🇬', 'sort_order' => 1],
            ['name' => 'Nigeria',             'code' => 'NG', 'flag' => '🇳🇬', 'sort_order' => 1],
            ['name' => 'Niger',               'code' => 'NE', 'flag' => '🇳🇪', 'sort_order' => 1],
            ['name' => 'Burkina Faso',        'code' => 'BF', 'flag' => '🇧🇫', 'sort_order' => 1],
            ['name' => 'Ghana',               'code' => 'GH', 'flag' => '🇬🇭', 'sort_order' => 1],

            // ─── Afrique de l'Ouest (sort 2) ───
            ['name' => 'Côte d\'Ivoire',      'code' => 'CI', 'flag' => '🇨🇮', 'sort_order' => 2],
            ['name' => 'Sénégal',             'code' => 'SN', 'flag' => '🇸🇳', 'sort_order' => 2],
            ['name' => 'Mali',                'code' => 'ML', 'flag' => '🇲🇱', 'sort_order' => 2],
            ['name' => 'Guinée',              'code' => 'GN', 'flag' => '🇬🇳', 'sort_order' => 2],
            ['name' => 'Guinée-Bissau',       'code' => 'GW', 'flag' => '🇬🇼', 'sort_order' => 2],
            ['name' => 'Sierra Leone',        'code' => 'SL', 'flag' => '🇸🇱', 'sort_order' => 2],
            ['name' => 'Liberia',             'code' => 'LR', 'flag' => '🇱🇷', 'sort_order' => 2],
            ['name' => 'Gambie',              'code' => 'GM', 'flag' => '🇬🇲', 'sort_order' => 2],
            ['name' => 'Cap-Vert',            'code' => 'CV', 'flag' => '🇨🇻', 'sort_order' => 2],
            ['name' => 'Mauritanie',          'code' => 'MR', 'flag' => '🇲🇷', 'sort_order' => 2],

            // ─── Afrique Centrale (sort 3) ───
            ['name' => 'Cameroun',            'code' => 'CM', 'flag' => '🇨🇲', 'sort_order' => 3],
            ['name' => 'Tchad',               'code' => 'TD', 'flag' => '🇹🇩', 'sort_order' => 3],
            ['name' => 'République du Congo', 'code' => 'CG', 'flag' => '🇨🇬', 'sort_order' => 3],
            ['name' => 'RD Congo',            'code' => 'CD', 'flag' => '🇨🇩', 'sort_order' => 3],
            ['name' => 'Gabon',               'code' => 'GA', 'flag' => '🇬🇦', 'sort_order' => 3],
            ['name' => 'Centrafrique',        'code' => 'CF', 'flag' => '🇨🇫', 'sort_order' => 3],
            ['name' => 'Guinée équatoriale',  'code' => 'GQ', 'flag' => '🇬🇶', 'sort_order' => 3],
            ['name' => 'São Tomé-et-Príncipe','code' => 'ST', 'flag' => '🇸🇹', 'sort_order' => 3],
            ['name' => 'Burundi',             'code' => 'BI', 'flag' => '🇧🇮', 'sort_order' => 3],
            ['name' => 'Rwanda',              'code' => 'RW', 'flag' => '🇷🇼', 'sort_order' => 3],

            // ─── Afrique de l'Est (sort 3) ───
            ['name' => 'Éthiopie',            'code' => 'ET', 'flag' => '🇪🇹', 'sort_order' => 3],
            ['name' => 'Kenya',               'code' => 'KE', 'flag' => '🇰🇪', 'sort_order' => 3],
            ['name' => 'Tanzanie',            'code' => 'TZ', 'flag' => '🇹🇿', 'sort_order' => 3],
            ['name' => 'Ouganda',             'code' => 'UG', 'flag' => '🇺🇬', 'sort_order' => 3],
            ['name' => 'Mozambique',          'code' => 'MZ', 'flag' => '🇲🇿', 'sort_order' => 3],
            ['name' => 'Madagascar',          'code' => 'MG', 'flag' => '🇲🇬', 'sort_order' => 3],
            ['name' => 'Djibouti',            'code' => 'DJ', 'flag' => '🇩🇯', 'sort_order' => 3],
            ['name' => 'Érythrée',            'code' => 'ER', 'flag' => '🇪🇷', 'sort_order' => 3],
            ['name' => 'Somalie',             'code' => 'SO', 'flag' => '🇸🇴', 'sort_order' => 3],
            ['name' => 'Malawi',              'code' => 'MW', 'flag' => '🇲🇼', 'sort_order' => 3],
            ['name' => 'Zambie',              'code' => 'ZM', 'flag' => '🇿🇲', 'sort_order' => 3],
            ['name' => 'Zimbabwe',            'code' => 'ZW', 'flag' => '🇿🇼', 'sort_order' => 3],
            ['name' => 'Comores',             'code' => 'KM', 'flag' => '🇰🇲', 'sort_order' => 3],
            ['name' => 'Seychelles',          'code' => 'SC', 'flag' => '🇸🇨', 'sort_order' => 3],
            ['name' => 'Maurice',             'code' => 'MU', 'flag' => '🇲🇺', 'sort_order' => 3],

            // ─── Afrique du Nord (sort 3) ───
            ['name' => 'Maroc',               'code' => 'MA', 'flag' => '🇲🇦', 'sort_order' => 3],
            ['name' => 'Algérie',             'code' => 'DZ', 'flag' => '🇩🇿', 'sort_order' => 3],
            ['name' => 'Tunisie',             'code' => 'TN', 'flag' => '🇹🇳', 'sort_order' => 3],
            ['name' => 'Égypte',              'code' => 'EG', 'flag' => '🇪🇬', 'sort_order' => 3],
            ['name' => 'Libye',               'code' => 'LY', 'flag' => '🇱🇾', 'sort_order' => 3],
            ['name' => 'Soudan',              'code' => 'SD', 'flag' => '🇸🇩', 'sort_order' => 3],
            ['name' => 'Soudan du Sud',       'code' => 'SS', 'flag' => '🇸🇸', 'sort_order' => 3],

            // ─── Afrique Australe (sort 3) ───
            ['name' => 'Afrique du Sud',      'code' => 'ZA', 'flag' => '🇿🇦', 'sort_order' => 3],
            ['name' => 'Angola',              'code' => 'AO', 'flag' => '🇦🇴', 'sort_order' => 3],
            ['name' => 'Namibie',             'code' => 'NA', 'flag' => '🇳🇦', 'sort_order' => 3],
            ['name' => 'Botswana',            'code' => 'BW', 'flag' => '🇧🇼', 'sort_order' => 3],
            ['name' => 'Eswatini',            'code' => 'SZ', 'flag' => '🇸🇿', 'sort_order' => 3],
            ['name' => 'Lesotho',             'code' => 'LS', 'flag' => '🇱🇸', 'sort_order' => 3],

            // ─── Europe (sort 4) ───
            ['name' => 'France',              'code' => 'FR', 'flag' => '🇫🇷', 'sort_order' => 4],
            ['name' => 'Belgique',            'code' => 'BE', 'flag' => '🇧🇪', 'sort_order' => 4],
            ['name' => 'Suisse',              'code' => 'CH', 'flag' => '🇨🇭', 'sort_order' => 4],
            ['name' => 'Allemagne',           'code' => 'DE', 'flag' => '🇩🇪', 'sort_order' => 4],
            ['name' => 'Espagne',             'code' => 'ES', 'flag' => '🇪🇸', 'sort_order' => 4],
            ['name' => 'Italie',              'code' => 'IT', 'flag' => '🇮🇹', 'sort_order' => 4],
            ['name' => 'Portugal',            'code' => 'PT', 'flag' => '🇵🇹', 'sort_order' => 4],
            ['name' => 'Pays-Bas',            'code' => 'NL', 'flag' => '🇳🇱', 'sort_order' => 4],
            ['name' => 'Royaume-Uni',         'code' => 'GB', 'flag' => '🇬🇧', 'sort_order' => 4],
            ['name' => 'Suède',               'code' => 'SE', 'flag' => '🇸🇪', 'sort_order' => 4],
            ['name' => 'Norvège',             'code' => 'NO', 'flag' => '🇳🇴', 'sort_order' => 4],
            ['name' => 'Danemark',            'code' => 'DK', 'flag' => '🇩🇰', 'sort_order' => 4],
            ['name' => 'Finlande',            'code' => 'FI', 'flag' => '🇫🇮', 'sort_order' => 4],
            ['name' => 'Autriche',            'code' => 'AT', 'flag' => '🇦🇹', 'sort_order' => 4],
            ['name' => 'Luxembourg',          'code' => 'LU', 'flag' => '🇱🇺', 'sort_order' => 4],
            ['name' => 'Pologne',             'code' => 'PL', 'flag' => '🇵🇱', 'sort_order' => 4],
            ['name' => 'Russie',              'code' => 'RU', 'flag' => '🇷🇺', 'sort_order' => 4],
            ['name' => 'Ukraine',             'code' => 'UA', 'flag' => '🇺🇦', 'sort_order' => 4],
            ['name' => 'Turquie',             'code' => 'TR', 'flag' => '🇹🇷', 'sort_order' => 4],

            // ─── Amériques (sort 4) ───
            ['name' => 'États-Unis',          'code' => 'US', 'flag' => '🇺🇸', 'sort_order' => 4],
            ['name' => 'Canada',              'code' => 'CA', 'flag' => '🇨🇦', 'sort_order' => 4],
            ['name' => 'Brésil',              'code' => 'BR', 'flag' => '🇧🇷', 'sort_order' => 4],
            ['name' => 'Mexique',             'code' => 'MX', 'flag' => '🇲🇽', 'sort_order' => 4],
            ['name' => 'Argentine',           'code' => 'AR', 'flag' => '🇦🇷', 'sort_order' => 4],
            ['name' => 'Haïti',               'code' => 'HT', 'flag' => '🇭🇹', 'sort_order' => 4],
            ['name' => 'Colombie',            'code' => 'CO', 'flag' => '🇨🇴', 'sort_order' => 4],

            // ─── Asie & Océanie (sort 4) ───
            ['name' => 'Chine',               'code' => 'CN', 'flag' => '🇨🇳', 'sort_order' => 4],
            ['name' => 'Inde',                'code' => 'IN', 'flag' => '🇮🇳', 'sort_order' => 4],
            ['name' => 'Japon',               'code' => 'JP', 'flag' => '🇯🇵', 'sort_order' => 4],
            ['name' => 'Corée du Sud',        'code' => 'KR', 'flag' => '🇰🇷', 'sort_order' => 4],
            ['name' => 'Arabie Saoudite',     'code' => 'SA', 'flag' => '🇸🇦', 'sort_order' => 4],
            ['name' => 'Émirats arabes unis', 'code' => 'AE', 'flag' => '🇦🇪', 'sort_order' => 4],
            ['name' => 'Qatar',               'code' => 'QA', 'flag' => '🇶🇦', 'sort_order' => 4],
            ['name' => 'Australie',           'code' => 'AU', 'flag' => '🇦🇺', 'sort_order' => 4],
        ];

        DB::table('countries')->insertOrIgnore($countries);
    }
}
