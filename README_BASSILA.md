# Bassila Connect - Plateforme Communautaire

Plateforme web dédiée aux ressortissants de Bassila, commune du département du Donga au Bénin.

## 🎯 Objectif

Bassila Connect permet aux ressortissants de la commune de Bassila de :
- **Rester connectés** avec leur communauté d'origine
- **Partager leurs compétences** et expériences professionnelles
- **Trouver des opportunités** professionnelles au sein de la diaspora
- **Recruter des talents** issus de Bassila pour leurs projets
- **Maintenir le lien** avec la commune et ses événements

## ✨ Fonctionnalités Principales

### Pour les Membres
- **Inscription et Profil Détaillé**
  - Informations personnelles et professionnelles
  - Quartier d'origine à Bassila
  - Localisation actuelle (ville, pays)
  - Compétences et domaines d'expertise
  - Formation et expériences
  - Réseaux sociaux

- **Annuaire Communautaire**
  - Recherche de membres par nom, profession, localisation
  - Filtres par pays, domaine d'expertise, compétences
  - Visibilité contrôlée (public/privé)
  - Disponibilité pour opportunités professionnelles

- **Galerie Photos**
  - Partage de photos de Bassila et d'événements
  - Modération par les administrateurs

- **Actualités**
  - Nouvelles de la commune
  - Événements communautaires
  - Publications par les membres

### Pour les Administrateurs
- **Validation des Inscriptions**
  - Approbation des nouveaux membres
  - Rejet des inscriptions suspectes
  - Gestion des statuts admin

- **Modération**
  - Validation des photos de la galerie
  - Gestion du contenu

## 🏗️ Architecture Technique

### Stack Technologique
- **Backend:** Laravel 10 (PHP 8.1+)
- **Frontend:** Blade Templates + Tailwind CSS
- **Authentification:** Laravel Breeze
- **Base de données:** MySQL/SQLite
- **Gestion d'API:** Laravel Sanctum

### Structure de la Base de Données

#### Tables Principales

**users**
- Informations de base (nom, email, mot de passe)
- Flags : `is_admin`, `is_approved`
- Gestion par Laravel Breeze

**user_profiles**
- Profil détaillé de chaque utilisateur
- Informations géographiques (quartier d'origine, localisation actuelle)
- Informations professionnelles (profession, compétences, formation)
- Réseaux sociaux
- Préférences de visibilité

**skills**
- Compétences disponibles dans la plateforme
- Catégorisation des compétences

**skill_user** (table pivot)
- Relation many-to-many entre users et skills
- Niveau de compétence (débutant, intermédiaire, expert)

**news**
- Articles et actualités
- Auteur, titre, contenu, image
- Statut de publication

**gallery**
- Photos de la galerie communautaire
- Métadonnées (titre, description, catégorie)
- Modération (is_approved)

## 📦 Installation

### Prérequis
- PHP 8.1 ou supérieur
- Composer
- MySQL ou SQLite
- Node.js et NPM (pour assets frontend)

### Étapes d'Installation

1. **Cloner le projet**
```bash
git clone <url-du-repo>
cd emergence-bassila
```

2. **Installer les dépendances PHP**
```bash
composer install
```

3. **Configurer l'environnement**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Configurer la base de données**
Éditer le fichier `.env` :
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=emergence_bassila
DB_USERNAME=root
DB_PASSWORD=votre_mot_de_passe
```

5. **Créer la base de données**
```bash
mysql -u root -p
CREATE DATABASE emergence_bassila CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
exit
```

6. **Exécuter les migrations**
```bash
php artisan migrate
```

7. **Installer et compiler les assets frontend**
```bash
npm install
npm run dev
# Ou pour la production:
npm run build
```

8. **Créer un administrateur**
Utiliser Tinker pour créer le premier admin :
```bash
php artisan tinker
```
```php
$user = App\Models\User::create([
    'name' => 'Admin Bassila',
    'email' => 'admin@bassilaconnect.com',
    'password' => bcrypt('MotDePasseSecurise'),
    'is_admin' => true,
    'is_approved' => true,
]);

$user->profile()->create([
    'ville_actuelle' => 'Bassila',
    'pays_actuel' => 'Bénin',
]);
```

9. **Lancer le serveur de développement**
```bash
php artisan serve
```

L'application sera accessible à l'adresse : `http://localhost:8000`

## 🔐 Système de Sécurité

### Middlewares Personnalisés

**EnsureUserIsApproved**
- Vérifie que l'utilisateur est approuvé par un admin
- Redirige vers la page de connexion si non approuvé
- Message d'information sur l'attente de validation

**EnsureUserIsAdmin**
- Restreint l'accès aux routes admin
- Retourne une erreur 403 si non-admin

### Workflow d'Inscription

1. L'utilisateur s'inscrit via le formulaire
2. Le compte est créé avec `is_approved = false`
3. L'utilisateur reçoit un message indiquant l'attente de validation
4. Un administrateur valide ou rejette le compte
5. Une fois approuvé, l'utilisateur accède à toutes les fonctionnalités

## 🗺️ Routes Principales

### Routes Publiques
- `/` - Landing page
- `/annuaire` - Annuaire des membres approuvés
- `/annuaire/{user}` - Profil public d'un membre
- `/login`, `/register` - Authentification (Breeze)

### Routes Utilisateurs Authentifiés
- `/dashboard` - Tableau de bord
- `/profile` - Édition du profil

### Routes Admin
- `/admin/users` - Gestion des utilisateurs
- `/admin/users/{user}/approve` - Approuver un utilisateur
- `/admin/users/{user}/reject` - Rejeter un utilisateur
- `/admin/users/{user}/toggle-admin` - Changer le statut admin
- `/admin/gallery/{gallery}/approve` - Approuver une photo
- `/admin/gallery/{gallery}/reject` - Rejeter une photo

## 📝 Modèles et Relations

### User
- `hasOne(UserProfile)` - Profil de l'utilisateur
- `belongsToMany(Skill)` - Compétences de l'utilisateur
- `hasMany(News)` - Actualités créées
- `hasMany(Gallery)` - Photos uploadées

### UserProfile
- `belongsTo(User)` - Utilisateur associé

### Skill
- `belongsToMany(User)` - Utilisateurs ayant cette compétence

### News
- `belongsTo(User, 'author_id')` - Auteur de l'actualité

### Gallery
- `belongsTo(User)` - Utilisateur ayant uploadé la photo

## 🚀 Prochaines Étapes de Développement

### Phase 1 - MVP (Actuel)
- ✅ Structure de base
- ✅ Authentification
- ✅ Modèles et migrations
- ✅ Système de validation admin
- ✅ Landing page adaptée
- ⏳ Vues pour l'annuaire
- ⏳ Vues pour le tableau de bord
- ⏳ Vues pour l'interface admin

### Phase 2 - Fonctionnalités Avancées
- [ ] Messagerie interne entre membres
- [ ] Système de notifications
- [ ] Offres d'emploi / Services
- [ ] Forum de discussions
- [ ] Événements communautaires
- [ ] Export des données (CV, annuaire)

### Phase 3 - Améliorations
- [ ] Application mobile (Flutter/React Native)
- [ ] Système de paiement pour cotisations
- [ ] Statistiques avancées
- [ ] Carte interactive des membres
- [ ] Multilingue (Français, Anglais, Langues locales)
- [ ] API REST complète

## 📄 Licence

MIT License

## 👥 Contributeurs

Développé pour la communauté de Bassila, Donga, Bénin.

## 📞 Contact

Pour toute question ou suggestion :
- Email: contact@bassilaconnect.com
- Bassila, Donga, Bénin

---

**Fait avec ❤️ pour la communauté de Bassila**
