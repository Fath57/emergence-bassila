<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Administrateur principal
        User::create([
            'first_name' => 'Admin',
            'last_name' => 'Bassila',
            'email' => 'admin@bassila.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => 'active',
            'village_origin' => 'Bassila Centre',
            'quartier' => 'Centre-Ville',
            'current_city' => 'Cotonou',
            'current_country' => 'Bénin',
            'current_profession' => 'Administrateur Système',
            'current_company' => 'Emergence Bassila',
            'professional_status' => 'Employé',
            'phone' => '+229 97 00 00 00',
            'bio' => 'Administrateur de la plateforme Emergence Bassila. Originaire de Bassila, je travaille pour connecter tous les ressortissants de notre belle commune.',
            'profile_visible' => true,
            'approved_at' => now(),
            'approved_by' => 1,
        ]);

        // Modérateur
        User::create([
            'first_name' => 'Modérateur',
            'last_name' => 'Communauté',
            'email' => 'moderator@bassila.com',
            'password' => Hash::make('password'),
            'role' => 'moderator',
            'status' => 'active',
            'village_origin' => 'Pénéssoulou',
            'quartier' => 'Quartier Nord',
            'current_city' => 'Porto-Novo',
            'current_country' => 'Bénin',
            'current_profession' => 'Gestionnaire de Communauté',
            'phone' => '+229 97 11 11 11',
            'bio' => 'Modérateur de la plateforme, je veille au respect des règles et à la qualité des contenus.',
            'profile_visible' => true,
            'approved_at' => now(),
            'approved_by' => 1,
        ]);

        // Utilisateurs actifs
        $activeUsers = [
            [
                'first_name' => 'Jean-Pierre',
                'last_name' => 'KPADE',
                'email' => 'jeanpierre@example.com',
                'village_origin' => 'Bassila Centre',
                'quartier' => 'Akpassa',
                'current_city' => 'Cotonou',
                'current_country' => 'Bénin',
                'current_profession' => 'Ingénieur Informatique',
                'current_company' => 'Tech Solutions Bénin',
                'professional_status' => 'Employé',
                'phone' => '+229 97 22 22 22',
                'bio' => 'Développeur passionné, originaire de Bassila. Je cherche à mettre mes compétences au service de ma communauté.',
                'linkedin_url' => 'https://linkedin.com/in/jeanpierre-kpade',
            ],
            [
                'first_name' => 'Marie',
                'last_name' => 'SIKA',
                'email' => 'marie@example.com',
                'village_origin' => 'Pénéssoulou',
                'current_city' => 'Paris',
                'current_country' => 'France',
                'current_profession' => 'Infirmière',
                'current_company' => 'Hôpital Saint-Louis',
                'professional_status' => 'Employé',
                'phone' => '+33 6 12 34 56 78',
                'bio' => 'Infirmière en France, fière de mes origines de Bassila. Je souhaite contribuer au développement sanitaire de ma région natale.',
            ],
            [
                'first_name' => 'Paul',
                'last_name' => 'TCHAKONDO',
                'email' => 'paul@example.com',
                'village_origin' => 'Manigri',
                'current_city' => 'Parakou',
                'current_country' => 'Bénin',
                'current_profession' => 'Enseignant',
                'current_company' => 'Lycée Technique',
                'professional_status' => 'Employé',
                'phone' => '+229 97 33 33 33',
                'bio' => 'Enseignant de mathématiques, je m\'investis dans l\'éducation de la jeunesse béninoise.',
            ],
            [
                'first_name' => 'Fatima',
                'last_name' => 'BELLO',
                'email' => 'fatima@example.com',
                'village_origin' => 'Bassila Centre',
                'quartier' => 'Akpamè',
                'current_city' => 'Montréal',
                'current_country' => 'Canada',
                'current_profession' => 'Chef de Projet',
                'current_company' => 'Consulting Group',
                'professional_status' => 'Employé',
                'phone' => '+1 514 555 1234',
                'bio' => 'Gestionnaire de projets internationaux. Je cherche à créer des ponts entre le Canada et le Bénin.',
                'linkedin_url' => 'https://linkedin.com/in/fatima-bello',
            ],
            [
                'first_name' => 'Daniel',
                'last_name' => 'ABALO',
                'email' => 'daniel@example.com',
                'village_origin' => 'Pénéssoulou',
                'current_city' => 'Lomé',
                'current_country' => 'Togo',
                'current_profession' => 'Entrepreneur',
                'current_company' => 'Abalo & Co',
                'professional_status' => 'Indépendant',
                'phone' => '+228 90 12 34 56',
                'bio' => 'Entrepreneur basé à Lomé, spécialisé dans l\'import-export. Toujours attaché à mes racines de Bassila.',
            ],
            [
                'first_name' => 'Sandrine',
                'last_name' => 'KOMBATE',
                'email' => 'sandrine@example.com',
                'village_origin' => 'Manigri',
                'current_city' => 'Abidjan',
                'current_country' => 'Côte d\'Ivoire',
                'current_profession' => 'Comptable',
                'current_company' => 'Cabinet AEC',
                'professional_status' => 'Employé',
                'phone' => '+225 07 12 34 56 78',
                'bio' => 'Expert-comptable en Côte d\'Ivoire, je souhaite participer au développement économique de Bassila.',
            ],
            [
                'first_name' => 'Thomas',
                'last_name' => 'AGBODJAN',
                'email' => 'thomas@example.com',
                'village_origin' => 'Bassila Centre',
                'current_city' => 'Bassila',
                'current_country' => 'Bénin',
                'current_profession' => 'Agriculteur',
                'professional_status' => 'Indépendant',
                'phone' => '+229 97 44 44 44',
                'bio' => 'Agriculteur moderne à Bassila. Je développe des techniques d\'agriculture durable dans notre région.',
            ],
            [
                'first_name' => 'Sophie',
                'last_name' => 'DOSSOU',
                'email' => 'sophie@example.com',
                'village_origin' => 'Pénéssoulou',
                'quartier' => 'Quartier Sud',
                'current_city' => 'Bruxelles',
                'current_country' => 'Belgique',
                'current_profession' => 'Juriste',
                'current_company' => 'Cabinet Juridique International',
                'professional_status' => 'Employé',
                'phone' => '+32 470 12 34 56',
                'bio' => 'Avocate spécialisée en droit international. J\'aspire à utiliser mes compétences pour soutenir les initiatives de développement à Bassila.',
                'linkedin_url' => 'https://linkedin.com/in/sophie-dossou',
            ],
        ];

        foreach ($activeUsers as $userData) {
            User::create(array_merge($userData, [
                'password' => Hash::make('password'),
                'role' => 'user',
                'status' => 'active',
                'profile_visible' => true,
                'approved_at' => now(),
                'approved_by' => 1,
            ]));
        }

        // Utilisateurs en attente de validation
        $pendingUsers = [
            [
                'first_name' => 'Ahmed',
                'last_name' => 'SOULE',
                'email' => 'ahmed@example.com',
                'village_origin' => 'Manigri',
                'current_city' => 'Niamey',
                'current_country' => 'Niger',
                'current_profession' => 'Médecin',
                'phone' => '+227 90 12 34 56',
            ],
            [
                'first_name' => 'Rachelle',
                'last_name' => 'KETE',
                'email' => 'rachelle@example.com',
                'village_origin' => 'Bassila Centre',
                'current_city' => 'Londres',
                'current_country' => 'Royaume-Uni',
                'current_profession' => 'Designer Graphique',
                'phone' => '+44 20 1234 5678',
            ],
            [
                'first_name' => 'Marc',
                'last_name' => 'ZINSOU',
                'email' => 'marc@example.com',
                'village_origin' => 'Pénéssoulou',
                'current_city' => 'Dakar',
                'current_country' => 'Sénégal',
                'current_profession' => 'Journaliste',
            ],
        ];

        foreach ($pendingUsers as $userData) {
            User::create(array_merge($userData, [
                'password' => Hash::make('password'),
                'role' => 'user',
                'status' => 'pending',
                'profile_visible' => true,
            ]));
        }
    }
}
