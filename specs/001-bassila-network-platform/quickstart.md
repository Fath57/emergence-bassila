# Quickstart: Plateforme Réseau Communautaire Bassilaise

**Branch**: `001-bassila-network-platform` | **Date**: 2026-04-07

## Prérequis

- Docker + Docker Compose
- PHP 8.2+ et Composer (ou utiliser Laravel Sail)
- Node.js 20+ et npm

## Setup initial

```bash
# 1. Créer le projet Laravel 11
composer create-project laravel/laravel emergence-bassila
cd emergence-bassila

# 2. Installer les dépendances PHP
composer require \
  livewire/livewire \
  filament/filament \
  spatie/laravel-permission \
  intervention/image \
  spatie/laravel-activitylog

composer require --dev \
  pestphp/pest \
  pestphp/pest-plugin-laravel \
  laravel/dusk

# 3. Installer les dépendances JS
npm install
npm install -D tailwindcss postcss autoprefixer @tailwindcss/forms

# 4. Configurer l'environnement
cp .env.example .env
# Configurer DB_*, MAIL_*, FILESYSTEM_DISK=s3, AWS_* dans .env
php artisan key:generate

# 5. Lancer les migrations et seeders
php artisan migrate --seed

# 6. Setup Filament
php artisan filament:install --panels
php artisan make:filament-user  # Créer le premier admin

# 7. Démarrer le serveur de développement
php artisan serve
npm run dev
php artisan queue:work  # Dans un autre terminal
```

## Structure de développement recommandée

Développer les modules dans cet ordre (aligné sur les priorités de la spec) :

1. **Auth** — Inscription, vérification email, login, reset password
2. **Profiles** — Création, édition, upload avatar, page publique
3. **Directory** — Recherche fulltext, filtres, pagination
4. **Contact** — Formulaire de contact, emails de notification
5. **Blog** — Éditeur WYSIWYG, statuts, commentaires
6. **Admin** — Panel Filament, modération, logs, dashboard

## Commandes clés

```bash
# Tests
./vendor/bin/pest
./vendor/bin/pest --coverage

# Migrations
php artisan migrate:fresh --seed  # Reset complet (dev seulement)
php artisan migrate                # Nouvelles migrations seulement

# Livewire
php artisan make:livewire Profile/CreateProfile
php artisan make:livewire Directory/SearchDirectory

# Filament
php artisan make:filament-resource Profile --generate
php artisan make:filament-resource BlogPost --generate

# Emails
php artisan make:mail ContactMessageReceived

# Queue
php artisan queue:work --tries=3
php artisan queue:failed           # Voir les jobs échoués
```

## Variables d'environnement requises

```env
# Base de données
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5437
DB_DATABASE=emergence_bassila
DB_USERNAME=postgres
DB_PASSWORD=secret

# Email
MAIL_MAILER=smtp
MAIL_HOST=localhost
MAIL_PORT=1025  # Mailpit en dev
MAIL_FROM_ADDRESS=noreply@bassila-network.com
MAIL_FROM_NAME="Bassila Network"

# Stockage fichiers
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=minioadmin      # MinIO en dev
AWS_SECRET_ACCESS_KEY=minioadmin
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=emergence-bassila
AWS_URL=http://localhost:9000
AWS_ENDPOINT=http://localhost:9000

# Queue
QUEUE_CONNECTION=redis  # ou 'database' pour démarrage rapide
```
