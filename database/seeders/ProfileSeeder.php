<?php

namespace Database\Seeders;

use App\Models\Profile;
use App\Models\Sector;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ProfileSeeder extends Seeder
{
    public function run(): void
    {
        $sectors = Sector::all();
        $skills  = Skill::all();

        $members = [
            ['Amadou Koulibaly', 'Développeur Web', 'Freelance Numérique', 'Bénin', 'Cotonou', 'IT & Technologie'],
            ['Fatoumata Diallo', 'Médecin Généraliste', 'CHU Cotonou', 'Bénin', 'Cotonou', 'Santé'],
            ['Ibrahim Traoré', 'Directeur Commercial', 'Société Africaine de Commerce', 'Côte d\'Ivoire', 'Abidjan', 'Commerce'],
            ['Aissatou Bah', 'Enseignante', 'Lycée Technique de Parakou', 'Bénin', 'Parakou', 'Éducation'],
            ['Moussa Camara', 'Ingénieur Agronome', 'Ministère de l\'Agriculture', 'Guinée', 'Conakry', 'Agriculture'],
            ['Kadiatou Sylla', 'Comptable', 'Cabinet Conseil Finance', 'France', 'Paris', 'Finance & Banque'],
            ['Boubacar Kouyaté', 'Chef de Projet IT', 'Orange Bénin', 'Bénin', 'Cotonou', 'IT & Technologie'],
            ['Mariama Balde', 'Avocate', 'Cabinet Juridique Atlassian', 'Sénégal', 'Dakar', 'Administration publique'],
            ['Souleymane Barry', 'Entrepreneur', 'SB Solutions', 'Bénin', 'Abomey-Calavi', 'Commerce'],
            ['Hawa Diakité', 'Designer UX/UI', 'Agence Créative West Africa', 'Mali', 'Bamako', 'IT & Technologie'],
            ['Oumar Konaté', 'Journaliste', 'Radio Nationale du Bénin', 'Bénin', 'Cotonou', 'Médias & Communication'],
            ['Aminata Touré', 'Pharmacienne', 'Pharmacie Centrale', 'Bénin', 'Porto-Novo', 'Santé'],
            ['Modibo Keïta', 'Architecte', 'Cabinet d\'Architecture Sahel', 'Mali', 'Bamako', 'BTP & Immobilier'],
            ['Safiatou Bamba', 'Responsable RH', 'Groupe Agro-Industrie', 'Côte d\'Ivoire', 'Abidjan', 'Commerce'],
            ['Ibrahima Dieng', 'Développeur Mobile', 'TechHub Dakar', 'Sénégal', 'Dakar', 'IT & Technologie'],
            ['Kadja Kondé', 'Infirmière', 'Hôpital Régional de Natitingou', 'Bénin', 'Natitingou', 'Santé'],
            ['Mamadou Barro', 'Logisticien', 'Transport Express Africa', 'Bénin', 'Cotonou', 'Transports & Logistique'],
            ['Rokhaya Sow', 'Professeure d\'Université', 'Université d\'Abomey-Calavi', 'Bénin', 'Abomey-Calavi', 'Éducation'],
            ['Seydou Diallo', 'Directeur Financier', 'BNB Finance', 'Bénin', 'Cotonou', 'Finance & Banque'],
            ['Ndeye Fatou Mbaye', 'Photographe', 'Studio Lumière Africa', 'Sénégal', 'Saint-Louis', 'Médias & Communication'],
            ['Ousmane Traoré', 'Gérant', 'Boutique Mode Africaine', 'Bénin', 'Cotonou', 'Commerce'],
            ['Binta Camara', 'Data Scientist', 'Banque Africaine de Développement', 'France', 'Lyon', 'Finance & Banque'],
            ['Cheick Oumar Sanogo', 'Technicien Réseau', 'MTN Bénin', 'Bénin', 'Cotonou', 'IT & Technologie'],
            ['Awa Kouyaté', 'Sage-femme', 'Centre de Santé Maternel', 'Guinée', 'Kindia', 'Santé'],
            ['Lamine Diallo', 'Consultant Management', 'McKinsey Afrique de l\'Ouest', 'Sénégal', 'Dakar', 'Commerce'],
        ];

        foreach ($members as $index => [$name, $job, $company, $country, $city, $sectorName]) {
            $email = 'user' . ($index + 1) . '@bassilanetwork.test';
            [$firstName, $lastName] = array_pad(preg_split('/\s+/', trim($name), 2) ?: [''], 2, '');

            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'first_name'        => $firstName,
                    'last_name'         => $lastName,
                    'password'          => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );

            $user->assignRole('member');

            if ($user->profile()->doesntExist()) {
                $sector = $sectors->firstWhere('name', $sectorName);

                $profile = $user->profile()->create([
                    'first_name'  => $firstName,
                    'last_name'   => $lastName,
                    'job_title'   => $job,
                    'company'     => $company,
                    'country'     => $country,
                    'city'        => $city,
                    'sector_id'   => $sector?->id,
                    'avatar_url'  => 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=0066CC&color=fff&size=200',
                    'is_verified' => $index % 3 === 0,
                    'verified_at' => $index % 3 === 0 ? now() : null,
                    'bio'         => "Membre de la communauté Bassilaise. {$job} chez {$company}, basé(e) à {$city}, {$country}.",
                    'education_start_year' => 1995 + ($index % 15),
                    'education_end_year'   => 2000 + ($index % 12),
                ]);

                // Attach random skills
                $randomSkills = $skills->random(min(3, $skills->count()))->pluck('id')->toArray();
                $profile->skills()->sync($randomSkills);
            }
        }
    }
}
