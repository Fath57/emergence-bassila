<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Catégories pour les actualités
        $newsCategories = [
            ['name' => 'Politique', 'slug' => 'politique', 'type' => 'news', 'order' => 1],
            ['name' => 'Économie', 'slug' => 'economie', 'type' => 'news', 'order' => 2],
            ['name' => 'Sport', 'slug' => 'sport', 'type' => 'news', 'order' => 3],
            ['name' => 'Culture', 'slug' => 'culture', 'type' => 'news', 'order' => 4],
            ['name' => 'Éducation', 'slug' => 'education', 'type' => 'news', 'order' => 5],
            ['name' => 'Santé', 'slug' => 'sante', 'type' => 'news', 'order' => 6],
            ['name' => 'Développement', 'slug' => 'developpement', 'type' => 'news', 'order' => 7],
            ['name' => 'Diaspora', 'slug' => 'diaspora', 'type' => 'news', 'order' => 8],
            ['name' => 'Tradition', 'slug' => 'tradition', 'type' => 'news', 'order' => 9],
        ];

        foreach ($newsCategories as $category) {
            Category::create(array_merge($category, [
                'description' => 'Catégorie ' . $category['name'],
                'is_active' => true,
            ]));
        }

        // Catégories pour les opportunités
        $opportunityCategories = [
            ['name' => 'Informatique & Tech', 'slug' => 'informatique-tech', 'type' => 'opportunity', 'order' => 1],
            ['name' => 'Santé & Médical', 'slug' => 'sante-medical', 'type' => 'opportunity', 'order' => 2],
            ['name' => 'Éducation & Formation', 'slug' => 'education-formation', 'type' => 'opportunity', 'order' => 3],
            ['name' => 'Commerce & Vente', 'slug' => 'commerce-vente', 'type' => 'opportunity', 'order' => 4],
            ['name' => 'Agriculture', 'slug' => 'agriculture', 'type' => 'opportunity', 'order' => 5],
            ['name' => 'BTP & Construction', 'slug' => 'btp-construction', 'type' => 'opportunity', 'order' => 6],
            ['name' => 'Finance & Comptabilité', 'slug' => 'finance-comptabilite', 'type' => 'opportunity', 'order' => 7],
            ['name' => 'Marketing & Communication', 'slug' => 'marketing-communication', 'type' => 'opportunity', 'order' => 8],
            ['name' => 'Droit & Justice', 'slug' => 'droit-justice', 'type' => 'opportunity', 'order' => 9],
            ['name' => 'Artisanat', 'slug' => 'artisanat', 'type' => 'opportunity', 'order' => 10],
            ['name' => 'Tourisme & Hôtellerie', 'slug' => 'tourisme-hotellerie', 'type' => 'opportunity', 'order' => 11],
            ['name' => 'Autres', 'slug' => 'autres', 'type' => 'opportunity', 'order' => 12],
        ];

        foreach ($opportunityCategories as $category) {
            Category::create(array_merge($category, [
                'description' => 'Catégorie ' . $category['name'],
                'is_active' => true,
            ]));
        }
    }
}
