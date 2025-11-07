<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // L'ordre est important : d'abord les utilisateurs et catégories,
        // puis les compétences, et enfin les actualités et opportunités

        $this->command->info('🌱 Démarrage du seeding de la base de données...');

        $this->command->info('👥 Seeding des utilisateurs...');
        $this->call(UserSeeder::class);

        $this->command->info('📁 Seeding des catégories...');
        $this->call(CategorySeeder::class);

        $this->command->info('💼 Seeding des compétences...');
        $this->call(SkillSeeder::class);

        $this->command->info('📰 Seeding des actualités et événements...');
        $this->call(NewsSeeder::class);

        $this->command->info('🎯 Seeding des opportunités...');
        $this->call(OpportunitySeeder::class);

        $this->command->info('');
        $this->command->info('✅ Seeding terminé avec succès !');
        $this->command->info('');
        $this->command->info('📊 Résumé :');
        $this->command->info('   - Utilisateurs créés (admin, modérateur, utilisateurs actifs et en attente)');
        $this->command->info('   - Catégories pour actualités et opportunités');
        $this->command->info('   - Compétences variées attachées aux utilisateurs');
        $this->command->info('   - Actualités et événements (publiés et brouillons)');
        $this->command->info('   - Opportunités d\'emploi (actives et en attente)');
        $this->command->info('');
        $this->command->info('🔑 Comptes de test :');
        $this->command->info('   Admin: admin@bassila.com / password');
        $this->command->info('   Modérateur: moderator@bassila.com / password');
        $this->command->info('   Utilisateur: jeanpierre@example.com / password');
        $this->command->info('');
    }
}
