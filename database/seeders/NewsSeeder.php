<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\News;
use App\Category;
use App\User;
use Illuminate\Support\Str;

class NewsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::where('status', 'active')->where('role', '!=', 'admin')->get();
        $admin = User::where('role', 'admin')->first();

        // Actualités publiées
        $publishedNews = [
            [
                'title' => 'Inauguration du nouveau centre de santé de Bassila',
                'type' => 'Actualité',
                'excerpt' => 'Le nouveau centre de santé communautaire a été officiellement inauguré en présence des autorités locales et de la diaspora.',
                'content' => "Ce vendredi 3 novembre 2024, la commune de Bassila a célébré l'inauguration de son nouveau centre de santé communautaire. Ce projet, financé en partie par la diaspora de Bassila, représente une avancée majeure pour l'accès aux soins de santé dans la région.\n\nLe centre dispose d'équipements modernes incluant une salle d'accouchement, un laboratoire d'analyses, une pharmacie et plusieurs salles de consultation. Il pourra accueillir jusqu'à 100 patients par jour.\n\nLe maire de Bassila a remercié tous les contributeurs, notamment les membres de la diaspora qui ont participé financièrement à ce projet. Il a souligné l'importance de la solidarité communautaire pour le développement local.\n\nLe centre sera géré par une équipe de 15 professionnels de santé dont 3 médecins, 6 infirmières et du personnel administratif.",
                'category' => 'Santé',
                'tags' => 'santé,infrastructure,développement,inauguration',
                'is_featured' => true,
            ],
            [
                'title' => 'Festival culturel de Bassila : Grande réussite de la 5ème édition',
                'type' => 'Culture',
                'excerpt' => 'Le festival culturel annuel de Bassila a attiré plus de 5000 visiteurs venus célébrer les traditions et la culture de la région.',
                'content' => "La 5ème édition du Festival Culturel de Bassila s'est tenue du 15 au 17 octobre 2024 et a connu un franc succès avec la participation de plus de 5000 visiteurs.\n\nAu programme : danses traditionnelles, défilés de mode africaine, expositions d'artisanat local, concours culinaire et concerts de musique traditionnelle et moderne.\n\nLe festival a également été l'occasion de promouvoir les produits agricoles locaux avec un marché spécial où les producteurs ont pu vendre directement leurs produits.\n\nLe comité d'organisation a annoncé que la prochaine édition sera encore plus grande avec l'invitation d'artistes internationaux de la diaspora.",
                'category' => 'Culture',
                'tags' => 'culture,festival,tradition,événement',
                'is_featured' => true,
            ],
            [
                'title' => 'Lancement du programme de formation en agriculture moderne',
                'type' => 'Actualité',
                'excerpt' => 'Un nouveau programme vise à former 200 jeunes agriculteurs aux techniques modernes d\'agriculture durable.',
                'content' => "Le Conseil Communal de Bassila, en partenariat avec des ONG internationales, a lancé un ambitieux programme de formation en agriculture moderne.\n\nCe programme d'une durée de 6 mois formera 200 jeunes aux techniques d'agriculture durable, à l'utilisation d'équipements modernes et aux méthodes de commercialisation.\n\nLes participants bénéficieront également d'un accompagnement pour accéder au crédit agricole et pourront rejoindre des coopératives existantes.\n\nL'objectif est d'augmenter la productivité agricole de 30% dans les 3 prochaines années tout en préservant l'environnement.",
                'category' => 'Développement',
                'tags' => 'agriculture,formation,jeunesse,développement',
            ],
            [
                'title' => 'Construction de 3 nouvelles écoles primaires annoncée',
                'type' => 'Actualité',
                'excerpt' => 'Le gouvernement annonce la construction de 3 écoles primaires dans les villages de Pénéssoulou, Manigri et Akpassa.',
                'content' => "Dans le cadre du plan de développement éducatif, le Ministère des Enseignements Maternel et Primaire a annoncé la construction de 3 nouvelles écoles primaires dans la commune de Bassila.\n\nCes établissements seront construits à Pénéssoulou, Manigri et Akpassa et pourront accueillir respectivement 300, 250 et 200 élèves.\n\nChaque école disposera de 6 salles de classe, d'une bibliothèque, d'une cantine scolaire et de blocs sanitaires.\n\nLes travaux devraient commencer en janvier 2025 et se terminer avant la rentrée scolaire 2025-2026.\n\nLe budget total est estimé à 450 millions de FCFA, financé conjointement par l'État et des partenaires au développement.",
                'category' => 'Éducation',
                'tags' => 'éducation,infrastructure,école,développement',
            ],
            [
                'title' => 'Match de football : Bassila remporte le tournoi régional',
                'type' => 'Actualité',
                'excerpt' => 'L\'équipe de football de Bassila a remporté le tournoi régional en battant l\'équipe de Djougou 2-1 en finale.',
                'content' => "Grande fierté pour Bassila ! L'équipe locale de football a remporté le tournoi régional de la Donga en battant l'équipe favorite de Djougou 2-1 en finale dimanche dernier.\n\nDevant un stade comble de plus de 3000 supporters, les joueurs de Bassila ont fait preuve d'une détermination exceptionnelle. Les buts ont été marqués par Kofi en première mi-temps et Amidou en seconde période.\n\nC'est la première fois en 10 ans que Bassila remporte ce tournoi prestigieux. L'équipe participera maintenant au tournoi national qui se tiendra à Cotonou en décembre.\n\nLe maire a félicité l'équipe et promis un soutien financier pour la préparation du tournoi national.",
                'category' => 'Sport',
                'tags' => 'football,sport,victoire,tournoi',
            ],
        ];

        foreach ($publishedNews as $newsData) {
            $category = Category::where('type', 'news')->where('name', $newsData['category'])->first();
            $user = $users->random();

            News::create([
                'user_id' => $user->id,
                'category_id' => $category ? $category->id : null,
                'title' => $newsData['title'],
                'slug' => Str::slug($newsData['title']),
                'type' => $newsData['type'],
                'excerpt' => $newsData['excerpt'],
                'content' => $newsData['content'],
                'tags' => explode(',', $newsData['tags']),
                'status' => 'published',
                'is_featured' => $newsData['is_featured'] ?? false,
                'published_at' => now()->subDays(rand(1, 30)),
                'views_count' => rand(50, 500),
            ]);
        }

        // Événements à venir
        $events = [
            [
                'title' => 'Assemblée Générale de la Diaspora de Bassila 2024',
                'type' => 'Événement',
                'excerpt' => 'Rencontre annuelle de tous les ressortissants de Bassila vivant à l\'étranger pour discuter des projets de développement.',
                'content' => "L'Association de la Diaspora de Bassila organise son Assemblée Générale annuelle qui réunira tous les membres pour faire le bilan de l'année écoulée et planifier les actions futures.\n\nAu programme :\n- Présentation du rapport d'activités 2024\n- Bilan financier\n- Discussion sur les nouveaux projets de développement\n- Élection du nouveau bureau\n- Networking et échanges\n\nUn cocktail sera offert à tous les participants. Inscription gratuite mais obligatoire avant le 25 novembre.",
                'event_date' => now()->addDays(20)->format('Y-m-d'),
                'event_location' => 'Paris, France - Maison des Associations',
                'is_featured' => true,
            ],
            [
                'title' => 'Journée Portes Ouvertes - École Technique de Bassila',
                'type' => 'Événement',
                'excerpt' => 'Découvrez les formations techniques proposées par l\'école et rencontrez les formateurs.',
                'content' => "L'École Technique de Bassila organise une journée portes ouvertes pour présenter ses différentes filières de formation professionnelle.\n\nFormations disponibles :\n- Électricité et électronique\n- Mécanique automobile\n- Menuiserie et ébénisterie\n- Plomberie et sanitaire\n- Coiffure et esthétique\n- Couture et stylisme\n\nLes visiteurs pourront découvrir les ateliers, rencontrer les formateurs, discuter avec les élèves et obtenir des informations sur les conditions d'admission.\n\nEntrée libre pour tous.",
                'event_date' => now()->addDays(15)->format('Y-m-d'),
                'event_location' => 'Bassila, Bénin - École Technique',
            ],
            [
                'title' => 'Marché des Produits du Terroir',
                'type' => 'Événement',
                'excerpt' => 'Grande foire pour promouvoir les produits agricoles et artisanaux de Bassila.',
                'content' => "Les producteurs et artisans de Bassila se donnent rendez-vous pour la grande foire annuelle des produits du terroir.\n\nAu menu :\n- Dégustation de spécialités culinaires\n- Vente de produits agricoles frais\n- Exposition d'artisanat local\n- Démonstrations de transformation de produits\n- Animations culturelles\n\nC'est l'occasion idéale pour découvrir les richesses de notre terroir et soutenir les producteurs locaux.\n\nEntrée : 500 FCFA (gratuit pour les enfants)",
                'event_date' => now()->addDays(10)->format('Y-m-d'),
                'event_location' => 'Bassila, Bénin - Place du Marché Central',
            ],
        ];

        foreach ($events as $eventData) {
            $user = $users->random();

            News::create([
                'user_id' => $user->id,
                'category_id' => null,
                'title' => $eventData['title'],
                'slug' => Str::slug($eventData['title']),
                'type' => $eventData['type'],
                'excerpt' => $eventData['excerpt'],
                'content' => $eventData['content'],
                'tags' => ['événement', 'bassila'],
                'event_date' => $eventData['event_date'],
                'event_location' => $eventData['event_location'],
                'status' => 'published',
                'is_featured' => $eventData['is_featured'] ?? false,
                'published_at' => now(),
                'views_count' => rand(20, 200),
            ]);
        }

        // Brouillons en attente de validation
        $drafts = [
            [
                'title' => 'Initiative de reboisement dans les villages',
                'type' => 'Actualité',
                'content' => 'Une nouvelle initiative de reboisement vise à planter 10000 arbres dans les villages de la commune pour lutter contre la déforestation et le changement climatique.',
            ],
            [
                'title' => 'Concours de talents pour les jeunes de Bassila',
                'type' => 'Annonce',
                'content' => 'Un grand concours de talents (chant, danse, comédie) sera organisé le mois prochain. Les inscriptions sont ouvertes pour tous les jeunes de 15 à 25 ans.',
            ],
        ];

        foreach ($drafts as $draftData) {
            $user = $users->random();

            News::create([
                'user_id' => $user->id,
                'category_id' => null,
                'title' => $draftData['title'],
                'slug' => Str::slug($draftData['title']),
                'type' => $draftData['type'],
                'excerpt' => null,
                'content' => $draftData['content'],
                'tags' => ['bassila'],
                'status' => 'draft',
                'is_featured' => false,
                'views_count' => 0,
            ]);
        }
    }
}
