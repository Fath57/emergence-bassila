<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Opportunity;
use App\Category;
use App\User;

class OpportunitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::where('status', 'active')->where('role', '!=', 'admin')->get();

        // Opportunités actives
        $activeOpportunities = [
            [
                'title' => 'Développeur Web Laravel - CDI',
                'type' => 'Emploi',
                'description' => "Tech Solutions Bénin recherche un développeur web expérimenté en Laravel pour rejoindre son équipe dynamique.\n\nMissions principales :\n- Développement d'applications web avec Laravel\n- Maintenance et amélioration des applications existantes\n- Participation aux réunions de planification\n- Rédaction de documentation technique\n- Travail en équipe avec les designers et chefs de projet",
                'requirements' => "- Diplôme BAC+3/5 en informatique ou équivalent\n- Minimum 2 ans d'expérience avec Laravel\n- Maîtrise de PHP, MySQL, JavaScript\n- Connaissance de Git\n- Bonne capacité de communication\n- Esprit d'équipe",
                'company_name' => 'Tech Solutions Bénin',
                'company_website' => 'https://techsolutions.bj',
                'location' => 'Cotonou, Bénin',
                'remote_possible' => false,
                'contract_type' => 'CDI',
                'salary_range' => '400 000 - 600 000 FCFA/mois',
                'experience_required' => '2-5 ans',
                'contact_email' => 'recrutement@techsolutions.bj',
                'contact_phone' => '+229 97 00 11 22',
                'deadline' => now()->addDays(30)->format('Y-m-d'),
                'category' => 'Informatique & Tech',
                'is_featured' => true,
            ],
            [
                'title' => 'Infirmier(ère) - Centre de Santé de Bassila',
                'type' => 'Emploi',
                'description' => "Le nouveau Centre de Santé Communautaire de Bassila recrute des infirmiers qualifiés.\n\nMissions :\n- Soins aux patients\n- Administration des traitements\n- Suivi des dossiers médicaux\n- Sensibilisation communautaire\n- Travail en équipe pluridisciplinaire",
                'requirements' => "- Diplôme d'État d'infirmier\n- Expérience en soins de santé primaire souhaitée\n- Connaissance du milieu rural\n- Empathie et sens du service\n- Maîtrise du français et des langues locales appréciée",
                'company_name' => 'Centre de Santé Communautaire de Bassila',
                'location' => 'Bassila, Bénin',
                'remote_possible' => false,
                'contract_type' => 'CDI',
                'salary_range' => '200 000 - 300 000 FCFA/mois',
                'experience_required' => '1-3 ans',
                'contact_email' => 'rh@csb-bassila.org',
                'contact_phone' => '+229 96 55 44 33',
                'deadline' => now()->addDays(25)->format('Y-m-d'),
                'category' => 'Santé & Médical',
                'is_featured' => true,
            ],
            [
                'title' => 'Professeur de Mathématiques - Lycée de Bassila',
                'type' => 'Emploi',
                'description' => "Le Lycée Technique de Bassila recherche un professeur de mathématiques pour les classes de seconde, première et terminale.\n\nResponsabilités :\n- Enseignement des mathématiques\n- Préparation des cours et évaluations\n- Suivi pédagogique des élèves\n- Participation aux conseils de classe\n- Encadrement d'activités parascolaires",
                'requirements' => "- Licence minimum en Mathématiques\n- CAPES ou formation pédagogique appréciée\n- Expérience en enseignement souhaitée\n- Pédagogie et patience\n- Engagement et ponctualité",
                'company_name' => 'Lycée Technique de Bassila',
                'location' => 'Bassila, Bénin',
                'remote_possible' => false,
                'contract_type' => 'CDI',
                'salary_range' => 'Selon grille salariale fonction publique',
                'experience_required' => '0-2 ans',
                'contact_email' => 'direction@lycee-bassila.bj',
                'deadline' => now()->addDays(20)->format('Y-m-d'),
                'category' => 'Éducation & Formation',
            ],
            [
                'title' => 'Stage en Comptabilité - Cabinet AEC Abidjan',
                'type' => 'Stage',
                'description' => "Cabinet d'expertise comptable à Abidjan recherche stagiaire motivé pour une période de 6 mois.\n\nMissions du stage :\n- Assistance comptable et fiscale\n- Saisie des pièces comptables\n- Préparation des déclarations fiscales\n- Classement et archivage\n- Participation aux audits",
                'requirements' => "- Étudiant en Licence 3 ou Master en Comptabilité\n- Maîtrise des logiciels comptables\n- Connaissance d'Excel\n- Rigueur et organisation\n- Disponibilité immédiate",
                'company_name' => 'Cabinet AEC',
                'location' => 'Abidjan, Côte d\'Ivoire',
                'remote_possible' => false,
                'contract_type' => 'Stage',
                'salary_range' => '100 000 FCFA/mois + frais de transport',
                'experience_required' => 'Débutant accepté',
                'contact_email' => 'stages@cabinet-aec.ci',
                'contact_phone' => '+225 07 12 34 56 78',
                'start_date' => now()->addDays(45)->format('Y-m-d'),
                'deadline' => now()->addDays(15)->format('Y-m-d'),
                'category' => 'Finance & Comptabilité',
            ],
            [
                'title' => 'Collaboration Projet Agro-Écologique',
                'type' => 'Collaboration',
                'description' => "Agriculteur innovant recherche partenaires pour développer un projet d'agriculture durable à Bassila.\n\nObjectif du projet :\n- Créer une ferme agro-écologique modèle\n- Former les jeunes aux techniques durables\n- Commercialiser des produits bio\n- Contribuer à la sécurité alimentaire locale\n\nNous recherchons :\n- Agronomes et techniciens agricoles\n- Personnes avec expérience en gestion de projet\n- Investisseurs intéressés par l'agriculture durable\n- Partenaires pour la commercialisation",
                'requirements' => "- Passion pour l'agriculture durable\n- Esprit d'entrepreneuriat\n- Disponibilité pour des déplacements à Bassila\n- Capacité d'investissement ou apport en compétences\n- Engagement à long terme",
                'company_name' => 'Agro-Bassila Initiative',
                'location' => 'Bassila, Bénin',
                'remote_possible' => true,
                'contract_type' => 'Autre',
                'experience_required' => 'Variable selon profil',
                'contact_email' => 'thomas@example.com',
                'contact_phone' => '+229 97 44 44 44',
                'category' => 'Agriculture',
            ],
            [
                'title' => 'Graphiste Freelance pour Projets Ponctuels',
                'type' => 'Collaboration',
                'description' => "Agence de communication recherche graphistes freelance pour des missions ponctuelles.\n\nTypes de projets :\n- Création de logos et identités visuelles\n- Design de supports print et digital\n- Infographies et présentations\n- Montage vidéo simple\n\nCollaboration flexible selon disponibilité.",
                'requirements' => "- Portfolio démontrant vos compétences\n- Maîtrise de la Suite Adobe (Photoshop, Illustrator, InDesign)\n- Créativité et respect des délais\n- Autonomie et professionnalisme\n- Expérience minimum 1 an",
                'company_name' => 'ComDesign Agency',
                'location' => 'À distance (Télétravail)',
                'remote_possible' => true,
                'contract_type' => 'Freelance',
                'salary_range' => 'Selon projet (50 000 - 200 000 FCFA)',
                'experience_required' => '1-3 ans',
                'contact_email' => 'contact@comdesign.com',
                'application_url' => 'https://comdesign.com/apply',
                'category' => 'Marketing & Communication',
            ],
        ];

        foreach ($activeOpportunities as $oppData) {
            $category = Category::where('type', 'opportunity')->where('name', $oppData['category'])->first();
            $user = $users->random();

            Opportunity::create([
                'user_id' => $user->id,
                'category_id' => $category ? $category->id : null,
                'title' => $oppData['title'],
                'type' => $oppData['type'],
                'description' => $oppData['description'],
                'requirements' => $oppData['requirements'],
                'company_name' => $oppData['company_name'],
                'company_website' => $oppData['company_website'] ?? null,
                'location' => $oppData['location'],
                'remote_possible' => $oppData['remote_possible'],
                'contract_type' => $oppData['contract_type'],
                'salary_range' => $oppData['salary_range'] ?? null,
                'experience_required' => $oppData['experience_required'],
                'contact_email' => $oppData['contact_email'],
                'contact_phone' => $oppData['contact_phone'] ?? null,
                'application_url' => $oppData['application_url'] ?? null,
                'start_date' => $oppData['start_date'] ?? null,
                'deadline' => $oppData['deadline'] ?? null,
                'status' => 'active',
                'is_featured' => $oppData['is_featured'] ?? false,
                'views_count' => rand(30, 300),
            ]);
        }

        // Opportunités en attente de validation
        $pendingOpportunities = [
            [
                'title' => 'Mécanicien Automobile - Garage Moderne',
                'type' => 'Emploi',
                'description' => 'Garage automobile recherche mécanicien qualifié pour entretien et réparation de véhicules.',
                'requirements' => 'CAP Mécanique, expérience 2 ans minimum',
                'company_name' => 'Garage Moderne Bassila',
                'location' => 'Bassila, Bénin',
                'contact_email' => 'garage@example.com',
            ],
            [
                'title' => 'Bénévolat - Association d\'Aide aux Enfants',
                'type' => 'Bénévolat',
                'description' => 'Association recherche bénévoles pour soutien scolaire et animation auprès des enfants.',
                'requirements' => 'Motivation, disponibilité week-ends',
                'company_name' => 'Association Espoir Enfance',
                'location' => 'Bassila, Bénin',
                'contact_email' => 'espoir@example.org',
            ],
        ];

        foreach ($pendingOpportunities as $oppData) {
            $user = $users->random();

            Opportunity::create([
                'user_id' => $user->id,
                'category_id' => null,
                'title' => $oppData['title'],
                'type' => $oppData['type'],
                'description' => $oppData['description'],
                'requirements' => $oppData['requirements'],
                'company_name' => $oppData['company_name'],
                'location' => $oppData['location'],
                'remote_possible' => false,
                'experience_required' => 'Variable',
                'contact_email' => $oppData['contact_email'],
                'status' => 'pending',
                'is_featured' => false,
                'views_count' => 0,
            ]);
        }
    }
}
