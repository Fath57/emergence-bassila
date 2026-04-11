# BASSILA - Réseau Communautaire

**Cahier des Charges Détaillé**  
Plateforme de Networking & Annuaire Communautaire  
Version 1.0 - Avril 2026

---

## Table des Matières

1. [Contexte & Objectifs](#1-contexte--objectifs)
2. [Charte Graphique](#2-charte-graphique)
3. [Fonctionnalités Détaillées](#3-fonctionnalités-détaillées)
4. [Architecture Technique](#4-architecture-technique)
5. [Modèle de Données](#5-modèle-de-données)
6. [Roadmap MVP](#6-roadmap-mvp)
7. [Critères d'Acceptation](#7-critères-dacceptation)

---

## 1. Contexte & Objectifs

### 1.1 Vision

Créer une plateforme de networking numérique pour la communauté Bassiloise dispersée globalement. Permettre aux anciens étudiants et résidents de Bassila de maintenir des liens, de partager leurs parcours professionnels et de créer des opportunités de collaboration.

### 1.2 Objectifs Principaux

- ✅ Créer un annuaire interactif et searchable de la communauté Bassiloise
- ✅ Faciliter la réconnexion entre membres dispersés
- ✅ Permettre le partage de savoir-faire et de ressources
- ✅ Inclure un blog riche pour les histoires et actualités de la communauté
- ✅ Mettre en place un système de modération et de vérification pour la confiance

### 1.3 Public Cible

- Anciens et actuels étudiants de Bassila (tous niveaux d'études)
- Résidents actuels et ex-résidents de Bassila
- Professionnels bassilois diaspora (France, Afrique, ailleurs)

---

## 2. Charte Graphique

### 2.1 Palette de Couleurs

| Nom | Code Hex | Usage |
|-----|----------|-------|
| **Bleu Primaire** | `#0066CC` | Headers, CTA, Nav, accents principaux |
| **Rouge Accent** | `#DC143C` | Accent, erreurs, badges, appels à l'action secondaires |
| **Bleu Clair** | `#E6F0FF` | Backgrounds subtils, hover states |
| **Dark Gray** | `#333333` | Texte principal |
| **Light Gray** | `#F5F5F5` | Backgrounds de sections, cartes |

### 2.2 Typographie

- **Police principale** : Inter / System Font Stack
- **Headings (H1-H3)** : Bold, Bleu Primaire
- **Body text** : 16px, Dark Gray (#333333)
- **Callouts** : Rouge Accent pour l'importance

### 2.3 Éléments UI

- **Boutons CTA** : Fond Bleu, texte blanc, arrondi 8px, hover plus foncé
- **Badges de vérification** : Fond Bleu, coche blanche, border subtil
- **Cartes de profil** : border 1px léger gris, ombre subtle, hover elevation
- **Formulaires** : inputs avec border léger, focus Bleu avec outline
- **Tags/Pills** : Fond Bleu clair, texte Bleu, arrondi 16px

---

## 3. Fonctionnalités Détaillées

### 3.1 Authentification & Profils

#### Inscription/Login
- Email + Mot de passe
- Validation email (confirmer avant accès complet)
- Réinitialisation de mot de passe
- Confirmation email avec lien

#### Profil Utilisateur
- **Photo de profil** (avatar obligatoire, min 200x200px)
- **Nom complet**, bio courte (max 500 caractères)
- **Localisation actuelle** (pays/ville)
- **Années d'études** (début/fin)
- **Métier/Titre professionnel**
- **Compétences/Skills** (tags multi-select)
- **Secteur d'activité** (dropdown select)
- **Lien LinkedIn/Portfolio** (optionnel)
- **Entreprise actuelle**
- **Badge de vérification** (admin vérifie)

### 3.2 Système de Recherche Avancée

#### Filtres (side panel)
- Par **métier/titre** (search + suggestions)
- Par **secteur d'activité** (multi-select)
- Par **localisation** (pays/région)
- Par **années d'études** (range)
- Par **compétences** (multi-select)
- **Afficher uniquement les profils vérifiés** (toggle)

#### Résultats
- Grid de cartes profils avec info clé (avatar, nom, titre, entreprise, localisation)
- Bouton "Contacter" sur chaque carte
- Pagination intelligente
- Tri par pertinence/date
- Temps réponse < 500ms

### 3.3 Système de Prise de Contact

#### MVP : Formulaire de contact simple
- **Sujet du message** (text input)
- **Corps du message** (textarea riche avec formatting basique)
- **Email de contact** affiché (pour réponse directe)
- **Email envoyé** au destinataire + notification
- Confirmation d'envoi à l'utilisateur

#### Future : Inbox/Messaging
- Messages privés persistants
- Notifications real-time (Pusher)
- Read/Unread status

### 3.4 Blog Riche

#### Articles
- **Éditeur WYSIWYG** (TipTap / Quill)
- Support **images** (upload + resize)
- Support **vidéos embeds** (YouTube, Vimeo)
- **Catégories/Tags** (multi-select)
- **Auteur + Date publication**
- **Statuts** : Brouillon / Publié / Archivé
- **Commentaires simples** (modérés avant affichage)
- **Reading time** calculé automatiquement
- SEO meta tags (title, description, image)

#### Listing
- Featured post en top
- Grid/List view toggle
- Filtrage par catégorie
- Recherche fulltext

### 3.5 Modération & Vérification

#### Admin Panel (accès limité admin)
- Liste des **profils non-vérifiés**
- Vérifier / Rejeter profils (avec raison optionnelle)
- Modérer **articles blog**
- Modérer **commentaires**
- Dashboard simple (utilisateurs total, posts, signups this month, etc.)
- **Logs d'actions** modération (qui a fait quoi, quand)

#### Workflow de Vérification
1. Utilisateur crée profil
2. Profil marqué comme "non vérifié"
3. Admin revoit et approuve/rejette
4. Si approuvé : badge visible sur profil
5. Si rejeté : notification à l'utilisateur avec raison

---

## 4. Architecture Technique

### 4.1 Stack Technologique

| Layer | Technologie |
|-------|-------------|
| **Frontend** | Laravel Livewire 3 + Tailwind CSS + shadcn/ui |
| **Backend** | Laravel 11 + PHP 8.2+ |
| **Database** | PostgreSQL 15+ |
| **Storage** | AWS S3 / MinIO (images, documents) |
| **Email** | Laravel Mailable + Queues (notifications) |
| **Infrastructure** | Docker + Dokku / DigitalOcean App Platform |
| **Search** | PostgreSQL FULLTEXT (MVP) / Meilisearch (future) |

### 4.2 Packages & Outils Clés

- **Livewire 3** : Composants réactifs côté serveur (moins de JS)
- **Blade** : Templating engine natif Laravel
- **Filament** : Admin panel elegant et productif
- **Spatie/Permission** : Gestion des rôles (Admin, User, Moderator)
- **TipTap / Quill** : Éditeur WYSIWYG pour blog
- **Intervention/Image** : Traitement et resize images
- **Laravel Sail** : Environnement dev Docker (optionnel mais recommandé)
- **Mailable** : Email templating et queues
- **Pest** : Testing framework (PHP, élégant)

### 4.3 Dépendances NPM

```json
{
  "dependencies": {
    "alpinejs": "^3.x",
    "axios": "^1.x"
  },
  "devDependencies": {
    "tailwindcss": "^3.x",
    "postcss": "^8.x",
    "autoprefixer": "^10.x",
    "@tailwindcss/forms": "^0.5.x",
    "shadcn-ui": "latest"
  }
}
```

### 4.4 Architecture Schématique

```
┌─────────────────────────────────────────────┐
│          Utilisateur Final (Browser)        │
└─────────────────┬───────────────────────────┘
                  │
         ┌────────▼────────┐
         │  Tailwind + JS  │
         │  (Livewire)     │
         └────────┬────────┘
                  │ AJAX/HTTP
         ┌────────▼────────────────┐
         │   Laravel Router        │
         │   (routes/web.php)      │
         └────────┬────────────────┘
                  │
    ┌─────────────┼─────────────┐
    │             │             │
┌───▼──┐  ┌──────▼──────┐ ┌───▼────┐
│Mail  │  │Livewire     │ │API     │
│      │  │Controllers  │ │Routes  │
└──────┘  └──────┬──────┘ └────────┘
                 │
          ┌──────▼──────────┐
          │  Models/Logic   │
          │  (App/Models)   │
          └──────┬──────────┘
                 │
          ┌──────▼──────────┐
          │   PostgreSQL    │
          │   + S3 Storage  │
          └─────────────────┘
```

---

## 5. Modèle de Données

### 5.1 Tables Principales

#### `users`
```sql
id, email, password, email_verified_at, created_at, updated_at
```
Relations: hasOne Profile, hasMany BlogPosts, hasMany ContactMessages

#### `profiles`
```sql
id, user_id, full_name, bio, avatar_url, city, country, 
job_title, company, sector_id, education_start_year, 
education_end_year, linkedin_url, portfolio_url, 
is_verified, verified_at, created_at, updated_at
```
Relations: belongsTo User, belongsTo Sector, belongsToMany Skills

#### `skills`
```sql
id, name (tag), created_at
```
Relations: belongsToMany Profiles (via profile_skills)

#### `profile_skills` (Pivot)
```sql
id, profile_id, skill_id
```

#### `sectors`
```sql
id, name (Agriculture, IT, Commerce, Santé, etc.), created_at
```
Relations: hasMany Profiles

#### `blog_posts`
```sql
id, user_id, title, slug, content (rich HTML), excerpt, 
featured_image_url, status (draft/published/archived), 
category_id, published_at, created_at, updated_at
```
Relations: belongsTo User, hasMany BlogComments

#### `blog_comments`
```sql
id, blog_post_id, user_id, content, moderated_at, created_at
```
Relations: belongsTo BlogPost, belongsTo User

#### `contact_messages`
```sql
id, from_user_id, to_user_id, subject, message, read_at, created_at
```
Relations: belongsTo User (sender), belongsTo User (receiver)

### 5.2 Indexation

- **profiles** : 
  - FULLTEXT sur `full_name`, `job_title`, `company`
  - Index sur `sector_id`, `country`, `education_end_year`
  - Index sur `is_verified`, `created_at`

- **blog_posts** : 
  - Index sur `status`, `published_at`
  - Index sur `user_id`

- **contact_messages** :
  - Index sur `to_user_id`, `read_at`

### 5.3 Diagram ER Simplifié

```
users (1) ──── (1) profiles
       (1) ──── (*) blog_posts
       (1) ──── (*) contact_messages (sender)
       (1) ──── (*) contact_messages (receiver)

profiles (1) ──── (1) sectors
         (*) ──── (*) skills

blog_posts (1) ──── (*) blog_comments
           (*) ────→ user_id
```

---

## 6. Roadmap MVP

### Phase 1 : Setup Infrastructure (Semaines 1-2)

- [x] Initialiser projet Laravel 11
- [x] Setup Docker + Docker Compose (PostgreSQL, Redis)
- [x] Configurer Tailwind + shadcn/ui
- [x] Migrations initiales
- [x] Seeder data (secteurs, skills templates)

**Livrable** : Environnement dev prêt, migrations testées

### Phase 2 : Auth & Profils (Semaines 3-4)

- [ ] Authentification email/password (Laravel native)
- [ ] Email verification workflow
- [ ] Profil creation/edit Livewire components
- [ ] Avatar upload (S3/MinIO)
- [ ] Dashboard utilisateur basique
- [ ] Profile view page (public)

**Livrable** : Utilisateurs peuvent créer compte et profil complet

### Phase 3 : Recherche Avancée (Semaines 5-6)

- [ ] Index FULLTEXT PostgreSQL
- [ ] Livewire SearchFilter component (filtres + résultats)
- [ ] Grid de cartes profils
- [ ] Pagination + tri
- [ ] Performance tuning (query optimization)

**Livrable** : Recherche fonctionnelle avec filtres multi-critères

### Phase 4 : Contact Messaging (Semaines 7-8)

- [ ] Formulaire de contact simple (Livewire)
- [ ] Email notifications (Mailable + Queues)
- [ ] Logs de messages
- [ ] Confirmation d'envoi

**Livrable** : Utilisateurs peuvent se contacter

### Phase 5 : Blog Riche (Semaines 9-10)

- [ ] CMS avec TipTap editor (Livewire component)
- [ ] Upload featured image
- [ ] Categories/Tags
- [ ] Draft/Published/Archived statuses
- [ ] Comments modérés
- [ ] Blog listing + single post view

**Livrable** : Blog fonctionnel avec contenu riche

### Phase 6 : Modération & Vérification (Semaines 11-12)

- [ ] Admin panel (Filament setup + resources)
- [ ] Profil vérification workflow
- [ ] Article modération
- [ ] Comment modération
- [ ] Admin logs/audit trail
- [ ] Dashboard stats (KPIs)

**Livrable** : Admins peuvent modérer contenu et vérifier profils

### Phase 7 : Polish & Tests (Semaines 13-14)

- [ ] Responsive design tweaks (mobile-first)
- [ ] E2E tests (Pest + Laravel Dusk)
- [ ] Unit tests (Models, Actions)
- [ ] SEO basics (meta tags, sitemap)
- [ ] Performance audit (lighthouse)
- [ ] Security audit (OWASP top 10)
- [ ] Documentation code

**Livrable** : MVP production-ready

---

## 7. Critères d'Acceptation

### 7.1 Authentification ✅

- [x] Utilisateur peut s'inscrire avec email/password
- [x] Email verification envoyé et fonctionnel
- [x] Login fonctionne avec credentials valides
- [x] Réinitialisation password possible
- [x] Sessions persistent correctement
- [x] Logout fonctionne

### 7.2 Profils ✅

- [x] Tous les champs sont sauvegardés correctement
- [x] Avatar uploadé et affiché (responsive)
- [x] Profil viewable par autre utilisateur (page publique)
- [x] Édition profil restreinte au propriétaire
- [x] Badge de vérification visible si approuvé
- [x] Validation des données (email, URLs)

### 7.3 Recherche ✅

- [x] Recherche fulltext par métier retourne résultats corrects
- [x] Filtres multiples + combinaisons jouent ensemble
- [x] Temps réponse < 500ms sur 1000+ profils
- [x] Pagination fonctionne et respecte filtre actif
- [x] Tri par pertinence/date fonctionne
- [x] Affichage "vérifiés uniquement" fonctionne

### 7.4 Messaging ✅

- [x] Formulaire de contact valide les champs (subject, message)
- [x] Email reçu par destinataire avec contenu correct
- [x] Sender reçoit confirmation d'envoi
- [x] Messages stockés en DB pour historique
- [x] Destinataire peut répondre via email

### 7.5 Blog ✅

- [x] Article créé avec contenu riche (TipTap, bold, italic, lists, headings)
- [x] Images uploadées et affichées correctement (responsive)
- [x] Brouillons ne sont pas publics
- [x] Commentaires modérés avant affichage
- [x] Author name + publish date affichés
- [x] Articles listés par date décroissante

### 7.6 Admin ✅

- [x] Admin voit liste des profils non-vérifiés
- [x] Peut approuver / rejeter profils (avec raison optionnelle)
- [x] Peut modérer articles (publish/unpublish/delete)
- [x] Peut modérer commentaires (approve/delete)
- [x] Dashboard affiche stats de base (total users, posts this month)
- [x] Logs d'actions modération horodatées

### 7.7 Général ✅

- [x] Design responsive (mobile-first, tested)
- [x] Temps de chargement < 2s (first paint)
- [x] Pas de 404/500 errors en usage normal
- [x] Charte graphique respectée (couleurs, typo, spacing)
- [x] Accessibilité basique (alt text, labels, contrast)

---

## Checklist Avant Launch

- [ ] Domaine acquis + SSL configuré
- [ ] S3 bucket créé + credentials configurées
- [ ] Email service configuré (SendGrid, Mailgun)
- [ ] Database backups automatisés (daily)
- [ ] Monitoring setup (Sentry, DataDog)
- [ ] Rate limiting activé
- [ ] CORS configuré correctement
- [ ] Admin password strength enforced
- [ ] Privacy policy + Terms rédigés
- [ ] Beta testing avec 10+ utilisateurs
- [ ] Feedback intégré + bugs fixés

---

## Stack Final Déploiement

### Infra
```
Docker + DigitalOcean App Platform
ou
Docker + VPS + Dokku
```

### CI/CD
```
GitHub Actions pour:
- Run tests (Pest)
- Build Docker image
- Deploy to production
```

### Monitoring
```
- Sentry: Error tracking
- Plausible: Analytics
- Uptime Robot: Ping service
```

---

## Notes & Références

- **Livewire Docs** : https://livewire.laravel.com
- **Tailwind** : https://tailwindcss.com
- **shadcn/ui** : https://ui.shadcn.com
- **PostgreSQL FULLTEXT** : https://www.postgresql.org/docs/current/textsearch.html
- **Laravel Filament** : https://filamentphp.com

---

**Fin du CDC**

Pour commencer : utiliser le SETUP_GUIDE.md fourni à part pour initialiser le projet.
