<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SkillSeeder extends Seeder
{
    public function run(): void
    {
        $taxonomy = [
            'Technologies & Informatique' => [
                'PHP', 'Python', 'JavaScript', 'TypeScript', 'Java', 'C', 'C++', 'C#',
                'Go', 'Rust', 'Swift', 'Kotlin', 'Ruby', 'Scala', 'R',
                'React', 'Vue.js', 'Angular', 'Node.js', 'Laravel', 'Symfony',
                'Django', 'FastAPI', 'Spring Boot', 'Flutter', 'React Native',
                'HTML/CSS', 'Tailwind CSS', 'Bootstrap',
                'SQL', 'PostgreSQL', 'MySQL', 'MongoDB', 'Redis', 'Elasticsearch',
                'Docker', 'Kubernetes', 'CI/CD', 'Linux', 'Git', 'DevOps',
                'AWS', 'Google Cloud', 'Azure', 'Terraform',
                'Cybersécurité', 'Réseaux & Systèmes', 'Administration système',
                'Intelligence artificielle', 'Machine Learning', 'Data Science',
                'Data Engineering', 'Big Data', 'Blockchain', 'IoT',
                'Gestion de bases de données', 'Architecture logicielle',
            ],
            'Santé & Médecine' => [
                'Médecine générale', 'Chirurgie', 'Pédiatrie', 'Gynécologie-obstétrique',
                'Cardiologie', 'Neurologie', 'Psychiatrie', 'Ophtalmologie',
                'Dermatologie', 'Oncologie', 'Radiologie', 'Anesthésie-réanimation',
                'Médecine interne', 'Médecine d\'urgence', 'Infectiologie',
                'Pharmacie', 'Biologie médicale', 'Infirmerie', 'Sage-femme',
                'Kinésithérapie', 'Odontologie', 'Nutrition & Diététique',
                'Santé publique', 'Épidémiologie', 'Médecine tropicale',
                'Optique & Optométrie',
            ],
            'Éducation & Recherche' => [
                'Enseignement primaire', 'Enseignement secondaire',
                'Enseignement supérieur', 'Formation professionnelle',
                'Orientation scolaire', 'Psychologie éducative',
                'Recherche scientifique', 'Rédaction académique',
                'Pédagogie & Andragogie',
            ],
            'Droit & Juridique' => [
                'Droit civil', 'Droit pénal', 'Droit des affaires',
                'Droit international', 'Droit du travail', 'Droit administratif',
                'Droit constitutionnel', 'Notariat', 'Magistrature', 'Avocat',
                'Compliance & Réglementation',
            ],
            'Gestion & Entrepreneuriat' => [
                'Management', 'Stratégie d\'entreprise', 'Entrepreneuriat',
                'Gestion de projet', 'Gestion de produit (Product management)',
                'Gestion du changement', 'Lean / Agile / Scrum',
                'Ressources humaines', 'Recrutement & Talent',
                'Supply chain & Logistique', 'Achats & Approvisionnement',
                'Qualité & Processus',
            ],
            'Finance & Comptabilité' => [
                'Comptabilité générale', 'Audit & Contrôle interne',
                'Finance d\'entreprise', 'Finance de marché',
                'Fiscalité', 'Banque', 'Microfinance & Fintech',
                'Investissement & Private equity', 'Bourse & Trading',
                'Contrôle de gestion', 'Modélisation financière',
            ],
            'Marketing & Communication' => [
                'Marketing digital', 'SEO / SEA', 'Réseaux sociaux',
                'Content marketing', 'E-commerce', 'CRM',
                'Relations publiques', 'Journalisme', 'Rédaction web',
                'Communication institutionnelle', 'Publicité & Média',
                'Événementiel', 'Branding & Identité visuelle',
            ],
            'Ingénierie & BTP' => [
                'Génie civil', 'Architecture', 'Urbanisme & Aménagement',
                'Génie électrique', 'Génie mécanique', 'Génie industriel',
                'Génie chimique', 'Génie énergétique', 'Topographie & Géomatique',
                'Maintenance industrielle', 'Automatisme & Robotique',
                'Télécommunications', 'Électronique',
            ],
            'Agriculture & Environnement' => [
                'Agronomie', 'Élevage & Zootechnie', 'Pêche & Aquaculture',
                'Agroalimentaire & Transformation', 'Foresterie & Sylviculture',
                'Développement durable', 'Gestion de l\'eau & Hydraulique',
                'Agriculture biologique', 'Géologie & Mines',
                'Gestion des déchets & Recyclage',
            ],
            'Arts & Création' => [
                'Design graphique', 'Design UX/UI', 'Motion design & Animation',
                'Illustration & BD', 'Photographie', 'Vidéo & Montage',
                'Architecture d\'intérieur', 'Mode & Stylisme', 'Artisanat',
                'Musique & Production audio', 'Théâtre & Arts de la scène',
                'Cinéma & Réalisation',
            ],
            'Sciences Sociales & Humanités' => [
                'Sociologie', 'Anthropologie', 'Histoire', 'Géographie',
                'Sciences politiques', 'Relations internationales', 'Diplomatie',
                'Économie du développement', 'Philosophie',
                'Travail social & Humanitaire', 'Genre & Droits humains',
                'Gestion des ONG & Associations',
            ],
            'Langues' => [
                'Traduction & Interprétariat', 'Linguistique',
                'Enseignement du français', 'Enseignement de l\'anglais',
            ],
            'Métiers & Services' => [
                'Électricité bâtiment', 'Plomberie & Sanitaire',
                'Mécanique automobile', 'Transport & Mobilité',
                'Tourisme & Hôtellerie', 'Restauration & Cuisine',
                'Sport & Coaching', 'Sécurité & Protection',
            ],
        ];

        foreach ($taxonomy as $category => $names) {
            foreach ($names as $order => $name) {
                DB::table('skills')->updateOrInsert(
                    ['name' => $name],
                    ['category' => $category, 'sort_order' => $order],
                );
            }
        }
    }
}
