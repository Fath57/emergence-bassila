# Bassila Network

Plateforme de networking communautaire pour la diaspora et les résidents Bassilais. Un annuaire professionnel, un espace de mise en relation et un blog communautaire.

## Stack technique

| Couche | Technologie |
|---|---|
| Backend | PHP 8.2+, Laravel 11 |
| Frontend | Livewire 3, Tailwind CSS 4 |
| Admin | Filament 5 |
| Base de données | PostgreSQL 15 |
| Cache / Queue | Redis 7 |
| Stockage fichiers | MinIO (S3-compatible) |
| Emails (dev) | Mailpit |
| Autorisation | Spatie/Permission |
| Tests | Pest |

## Fonctionnalités (MVP)

- **Inscription & profil** — Création de compte avec vérification email, profil professionnel avec avatar (auto-généré si absent), compétences, secteur, localisation
- **Annuaire** — Recherche fulltext PostgreSQL, filtres combinables (secteur, pays, promotion, compétences, profils vérifiés), pagination
- **Contact** — Formulaire de contact entre membres, notifications email aux deux parties
- **Blog** — Publication d'articles avec statuts draft/publié, commentaires modérés
- **Admin** — Panel Filament pour vérifier les profils, modérer les commentaires, publier/dépublier des articles, tableau de bord statistiques

## Prérequis

- Docker + Docker Compose
- PHP 8.2+ et Composer
- Node.js 20+ et npm

## Installation

### 1. Cloner et configurer l'environnement

```bash
git clone <repo-url> emergence-bassila
cd emergence-bassila

cp .env.example .env
```

Éditer `.env` avec les valeurs de développement :

```env
APP_NAME="Bassila Network"
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5437
DB_DATABASE=emergence_bassila
DB_USERNAME=postgres
DB_PASSWORD=secret

MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=1025
MAIL_FROM_ADDRESS=noreply@bassila-network.com
MAIL_FROM_NAME="Bassila Network"

FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=minioadmin
AWS_SECRET_ACCESS_KEY=minioadmin
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=emergence-bassila
AWS_URL=http://localhost:9000
AWS_ENDPOINT=http://localhost:9000
AWS_USE_PATH_STYLE_ENDPOINT=true

QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
```

### 2. Démarrer les services Docker

```bash
docker compose up -d
```

Cela démarre PostgreSQL (`:5437`), Redis (`:6380`), MinIO (`:9000`, console `:9001`) et Mailpit (SMTP `:1025`, UI `:8025`).

### 3. Installer les dépendances

```bash
composer install
npm install
```

### 4. Initialiser la base de données

```bash
php artisan key:generate
php artisan migrate --seed
```

Le seeder crée :
- Les rôles `admin`, `moderator`, `user`
- 12 secteurs d'activité
- Des compétences prédéfinies
- Un compte admin : `admin@bassilanetwork.test` / `admin2024!`
- 25 profils de membres fictifs

### 5. Créer le bucket MinIO

```bash
# Via la console MinIO : http://localhost:9001
# Identifiants : minioadmin / minioadmin
# Créer un bucket nommé "emergence-bassila" avec accès public
```

Ou via CLI :

```bash
docker compose exec minio mc alias set local http://localhost:9000 minioadmin minioadmin
docker compose exec minio mc mb local/emergence-bassila
docker compose exec minio mc anonymous set public local/emergence-bassila
```

### 6. Lancer le serveur de développement

```bash
# Terminal 1 : Serveur Laravel
php artisan serve

# Terminal 2 : Assets Vite
npm run dev

# Terminal 3 : Worker de queue (emails)
php artisan queue:work
```

L'application est accessible sur `http://localhost:8000`.

## Panel administrateur

Accessible sur `/admin` avec le compte `admin@bassilanetwork.test` / `admin2024!`.

Le panel permet de :
- Approuver ou rejeter des profils (avec email automatique au membre)
- Publier/dépublier/supprimer des articles de blog
- Modérer les commentaires (approuver ou supprimer)
- Visualiser les statistiques : total membres, nouvelles inscriptions, articles publiés ce mois

## Tests

```bash
# Lancer tous les tests
./vendor/bin/pest

# Avec couverture de code
./vendor/bin/pest --coverage

# Un fichier spécifique
./vendor/bin/pest tests/Feature/Auth/RegisterTest.php
```

Les tests utilisent `RefreshDatabase` — une base de données de test (SQLite en mémoire ou PostgreSQL dédié) est recommandée. Configurer `phpunit.xml` pour pointer vers une base de test :

```xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

## Qualité du code

```bash
# Vérifier le style de code (Laravel Pint)
./vendor/bin/pint --test

# Formater automatiquement
./vendor/bin/pint

# Analyse statique (Larastan niveau 5)
./vendor/bin/phpstan analyse
```

Ces deux vérifications bloquent le merge via GitHub Actions (`.github/workflows/quality.yml`).

## Build de production

```bash
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

## Architecture

```
app/
├── Filament/
│   ├── Resources/          # Ressources admin (Profile, BlogPost, BlogComment)
│   └── Widgets/            # StatsOverview
├── Http/Controllers/       # BlogController, ProfileController (pages statiques)
├── Livewire/
│   ├── Auth/               # Register, Login, ForgotPassword, ResetPassword
│   ├── Blog/               # CreatePost, EditPost, CommentForm
│   ├── Contact/            # ContactForm
│   ├── Directory/          # SearchDirectory
│   └── Profile/            # CreateProfile, EditProfile, ProfileCard
├── Mail/                   # 4 Mailables (contact + vérification profil)
├── Models/                 # 9 modèles Eloquent
├── Policies/               # ProfilePolicy, BlogPostPolicy
├── Providers/Filament/     # AdminPanelProvider
└── Services/               # AvatarGenerator

resources/views/
├── blog/                   # index.blade.php, show.blade.php
├── layouts/                # app.blade.php, guest.blade.php
├── livewire/               # Vues des composants Livewire
├── mail/                   # Templates email (contact/, profile/)
└── profile/                # show.blade.php (page publique)

database/
├── factories/              # UserFactory, ProfileFactory, BlogPostFactory, ...
├── migrations/             # 14 migrations
└── seeders/                # RoleSeeder, SectorSeeder, SkillSeeder, ProfileSeeder, AdminSeeder

tests/Feature/
├── Admin/                  # ModerationTest
├── Auth/                   # RegisterTest, LoginTest, EmailVerificationTest
├── Blog/                   # BlogPostTest, CommentTest
├── Contact/                # ContactFormTest
├── Directory/              # SearchTest
└── Profile/                # CreateProfileTest, EditProfileTest
```

## Variables d'environnement clés

| Variable | Description | Valeur dev |
|---|---|---|
| `DB_CONNECTION` | Driver base de données | `pgsql` |
| `MAIL_HOST` / `MAIL_PORT` | Serveur SMTP | Mailpit `127.0.0.1:1025` |
| `FILESYSTEM_DISK` | Stockage fichiers | `s3` |
| `AWS_ENDPOINT` | URL MinIO | `http://localhost:9000` |
| `QUEUE_CONNECTION` | Driver de queue | `redis` |
| `SENTRY_LARAVEL_DSN` | Monitoring erreurs | (optionnel en dev) |

## Contribution

1. Créer une branche depuis `main` : `git checkout -b NNN-description`
2. Implémenter les changements en suivant le style PSR-12 (vérifié par Pint)
3. Ajouter des tests Pest pour les nouveaux comportements
4. Vérifier que `pint --test` et `phpstan analyse` passent
5. Ouvrir une Pull Request — les checks GitHub Actions valident automatiquement
