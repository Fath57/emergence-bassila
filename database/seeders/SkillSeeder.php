<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Skill;
use App\User;

class SkillSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Création des compétences
        $skills = [
            // Informatique
            'PHP', 'Laravel', 'JavaScript', 'React', 'Vue.js', 'Node.js', 'Python',
            'Java', 'C++', 'SQL', 'MySQL', 'PostgreSQL', 'MongoDB',
            'HTML/CSS', 'Git', 'Docker', 'AWS', 'Azure',

            // Design
            'Photoshop', 'Illustrator', 'Figma', 'UI/UX Design', 'Graphic Design',

            // Marketing & Communication
            'Marketing Digital', 'SEO', 'Social Media', 'Content Writing',
            'Communication', 'Relations Publiques',

            // Gestion & Administration
            'Gestion de Projet', 'Management', 'Leadership', 'Planification Stratégique',
            'Ressources Humaines', 'Administration',

            // Finance & Comptabilité
            'Comptabilité', 'Audit', 'Finance', 'Fiscalité', 'Budget',

            // Santé
            'Soins Infirmiers', 'Médecine Générale', 'Pharmacie', 'Santé Publique',

            // Éducation
            'Enseignement', 'Formation', 'Pédagogie', 'Encadrement',

            // Agriculture
            'Agriculture', 'Élevage', 'Agroforesterie', 'Irrigation',

            // Autres
            'Droit', 'Juridique', 'Traduction', 'Rédaction', 'Analyse de Données',
            'Mécanique', 'Électricité', 'Plomberie', 'Menuiserie',
        ];

        foreach ($skills as $skillName) {
            Skill::create([
                'name' => $skillName,
                'category' => $this->getSkillCategory($skillName),
            ]);
        }

        // Attacher des compétences aux utilisateurs
        $this->attachSkillsToUsers();
    }

    /**
     * Déterminer la catégorie d'une compétence
     */
    private function getSkillCategory(string $skillName): string
    {
        $categories = [
            'Informatique & Tech' => ['PHP', 'Laravel', 'JavaScript', 'React', 'Vue.js', 'Node.js', 'Python', 'Java', 'C++', 'SQL', 'MySQL', 'PostgreSQL', 'MongoDB', 'HTML/CSS', 'Git', 'Docker', 'AWS', 'Azure'],
            'Design' => ['Photoshop', 'Illustrator', 'Figma', 'UI/UX Design', 'Graphic Design'],
            'Marketing & Communication' => ['Marketing Digital', 'SEO', 'Social Media', 'Content Writing', 'Communication', 'Relations Publiques'],
            'Gestion' => ['Gestion de Projet', 'Management', 'Leadership', 'Planification Stratégique', 'Ressources Humaines', 'Administration'],
            'Finance' => ['Comptabilité', 'Audit', 'Finance', 'Fiscalité', 'Budget'],
            'Santé' => ['Soins Infirmiers', 'Médecine Générale', 'Pharmacie', 'Santé Publique'],
            'Éducation' => ['Enseignement', 'Formation', 'Pédagogie', 'Encadrement'],
            'Agriculture' => ['Agriculture', 'Élevage', 'Agroforesterie', 'Irrigation'],
            'Artisanat' => ['Mécanique', 'Électricité', 'Plomberie', 'Menuiserie'],
        ];

        foreach ($categories as $category => $skills) {
            if (in_array($skillName, $skills)) {
                return $category;
            }
        }

        return 'Autre';
    }

    /**
     * Attacher des compétences aux utilisateurs
     */
    private function attachSkillsToUsers(): void
    {
        // Jean-Pierre KPADE - Ingénieur Informatique
        $user = User::where('email', 'jeanpierre@example.com')->first();
        if ($user) {
            $skills = Skill::whereIn('name', ['PHP', 'Laravel', 'JavaScript', 'Vue.js', 'MySQL', 'Git'])->get();
            foreach ($skills as $skill) {
                $user->skills()->attach($skill->id, [
                    'level' => rand(3, 5),
                    'years_experience' => rand(2, 8),
                ]);
            }
        }

        // Marie SIKA - Infirmière
        $user = User::where('email', 'marie@example.com')->first();
        if ($user) {
            $skills = Skill::whereIn('name', ['Soins Infirmiers', 'Santé Publique'])->get();
            foreach ($skills as $skill) {
                $user->skills()->attach($skill->id, [
                    'level' => rand(4, 5),
                    'years_experience' => rand(5, 12),
                ]);
            }
        }

        // Paul TCHAKONDO - Enseignant
        $user = User::where('email', 'paul@example.com')->first();
        if ($user) {
            $skills = Skill::whereIn('name', ['Enseignement', 'Pédagogie', 'Encadrement'])->get();
            foreach ($skills as $skill) {
                $user->skills()->attach($skill->id, [
                    'level' => rand(4, 5),
                    'years_experience' => rand(6, 15),
                ]);
            }
        }

        // Fatima BELLO - Chef de Projet
        $user = User::where('email', 'fatima@example.com')->first();
        if ($user) {
            $skills = Skill::whereIn('name', ['Gestion de Projet', 'Management', 'Leadership', 'Planification Stratégique'])->get();
            foreach ($skills as $skill) {
                $user->skills()->attach($skill->id, [
                    'level' => rand(4, 5),
                    'years_experience' => rand(5, 10),
                ]);
            }
        }

        // Daniel ABALO - Entrepreneur
        $user = User::where('email', 'daniel@example.com')->first();
        if ($user) {
            $skills = Skill::whereIn('name', ['Management', 'Finance', 'Commerce', 'Leadership'])->get();
            foreach ($skills as $skill) {
                $user->skills()->attach($skill->id, [
                    'level' => rand(3, 5),
                    'years_experience' => rand(4, 12),
                ]);
            }
        }

        // Sandrine KOMBATE - Comptable
        $user = User::where('email', 'sandrine@example.com')->first();
        if ($user) {
            $skills = Skill::whereIn('name', ['Comptabilité', 'Audit', 'Finance', 'Fiscalité'])->get();
            foreach ($skills as $skill) {
                $user->skills()->attach($skill->id, [
                    'level' => rand(4, 5),
                    'years_experience' => rand(6, 12),
                ]);
            }
        }

        // Thomas AGBODJAN - Agriculteur
        $user = User::where('email', 'thomas@example.com')->first();
        if ($user) {
            $skills = Skill::whereIn('name', ['Agriculture', 'Élevage', 'Agroforesterie'])->get();
            foreach ($skills as $skill) {
                $user->skills()->attach($skill->id, [
                    'level' => rand(4, 5),
                    'years_experience' => rand(10, 20),
                ]);
            }
        }

        // Sophie DOSSOU - Juriste
        $user = User::where('email', 'sophie@example.com')->first();
        if ($user) {
            $skills = Skill::whereIn('name', ['Droit', 'Juridique'])->get();
            foreach ($skills as $skill) {
                $user->skills()->attach($skill->id, [
                    'level' => 5,
                    'years_experience' => rand(6, 12),
                ]);
            }
        }
    }
}
