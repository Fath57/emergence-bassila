---
description: "Task list for Plateforme Réseau Communautaire Bassiloise - MVP"
---

# Tasks: Plateforme Réseau Communautaire Bassiloise - MVP

**Input**: Design documents from `/specs/001-bassila-network-platform/`
**Prerequisites**: plan.md ✅, spec.md ✅, research.md ✅, data-model.md ✅, contracts/ ✅

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (US1–US5)

---

## Phase 1: Setup (Infrastructure partagée)

**Purpose**: Créer le projet Laravel 11 et configurer l'environnement de développement complet

- [X] T001 Initialiser le projet Laravel 11 à la racine du repo (`composer create-project laravel/laravel .`)
- [X] T002 Créer `docker-compose.yml` avec services : PostgreSQL 15, Redis, MinIO, Mailpit
- [X] T003 [P] Installer les dépendances PHP dans `composer.json` : `livewire/livewire`, `filament/filament`, `spatie/laravel-permission`, `intervention/image`, `spatie/laravel-activitylog`
- [X] T004 [P] Installer les dépendances NPM dans `package.json` : `tailwindcss`, `postcss`, `autoprefixer`, `@tailwindcss/forms`, configurer `tailwind.config.js` et `vite.config.js`
- [X] T005 Configurer `.env` avec les variables PostgreSQL, Redis, MinIO, Mailpit, queue driver (voir `quickstart.md`)
- [X] T006 Configurer le layout principal dans `resources/views/layouts/app.blade.php` avec navigation, slots et intégration Livewire + Tailwind
- [X] T091 [P] Configurer Laravel Pint dans `pint.json` à la racine avec le preset `laravel` (standard PSR-12) — ajouter `./vendor/bin/pint --test` dans le script CI
- [X] T092 [P] Installer `nunomaduro/larastan` et configurer `phpstan.neon` à la racine : niveau 5, paths `app/`, inclure le stubs Laravel
- [X] T093 Créer `.github/workflows/quality.yml` avec deux jobs : `pint` (`./vendor/bin/pint --test`) et `larastan` (`./vendor/bin/phpstan analyse`) — les deux bloquent le merge si en échec
- [X] T094 [P] Installer `sentry/sentry-laravel` et configurer `config/sentry.php` + variable `SENTRY_LARAVEL_DSN` dans `.env.example` — activer la capture des exceptions de queue et les failed jobs

**Checkpoint**: Projet initialisé, Docker fonctionnel, layout de base en place, code quality + observabilité configurés

---

## Phase 2: Fondations (Prérequis bloquants)

**Purpose**: Données de base et infrastructure partagée requises par TOUTES les user stories

**⚠️ CRITIQUE**: Aucune user story ne peut démarrer avant la completion de cette phase

- [X] T007 Exécuter les migrations Laravel natives (users, password_resets, failed_jobs, etc.) via `php artisan migrate`
- [X] T008 [P] Créer la migration `create_sectors_table.php` dans `database/migrations/` avec colonnes `id, name, created_at`
- [X] T009 [P] Créer la migration `create_skills_table.php` dans `database/migrations/` avec colonnes `id, name, created_at`
- [X] T010 Créer le seeder `database/seeders/SectorSeeder.php` avec les 12 secteurs définis dans `data-model.md` (Agriculture, Commerce, Santé, etc.)
- [X] T011 [P] Créer le seeder `database/seeders/SkillSeeder.php` avec des compétences templates (Laravel, Python, Marketing, etc.)
- [X] T012 Configurer Spatie/Permission dans `database/seeders/RoleSeeder.php` avec les rôles : `admin`, `moderator`, `user`. Publier les migrations Spatie.
- [X] T013 Mettre à jour `database/seeders/DatabaseSeeder.php` pour appeler SectorSeeder, SkillSeeder, RoleSeeder
- [X] T014 Configurer les routes de base dans `routes/web.php` (accueil, auth, profil, annuaire, blog, contact)
- [X] T015 Configurer le driver de queue `database` dans `config/queue.php` et créer la table `jobs` via `php artisan queue:table && php artisan migrate`

**Checkpoint**: Base de données prête, rôles configurés, routes déclarées — les user stories peuvent démarrer

---

## Phase 3: User Story 1 — Inscription et création de profil (Priority: P1) 🎯 MVP

**Goal**: Un membre s'inscrit, vérifie son email, complète son profil professionnel et sa page publique est visible

**Independent Test**: Créer un compte → vérifier l'email → remplir le profil avec avatar → consulter la page publique depuis un autre navigateur. Valider que l'édition est refusée pour un autre utilisateur.

### Modèles et migrations

- [X] T016 [P] [US1] Créer la migration `create_profiles_table.php` dans `database/migrations/` avec tous les champs définis dans `data-model.md` (full_name, bio, avatar_url, city, country, job_title, company, sector_id, education_start_year, education_end_year, linkedin_url, portfolio_url, is_verified, verified_at)
- [X] T017 [P] [US1] Créer la migration `create_profile_skills_table.php` (table pivot) dans `database/migrations/` avec colonnes `id, profile_id, skill_id`
- [X] T018 [P] [US1] Créer le modèle `app/Models/User.php` avec relation `hasOne Profile`, `hasMany BlogPost`, `hasMany ContactMessage`, trait `HasRoles` de Spatie
- [X] T019 [P] [US1] Créer le modèle `app/Models/Profile.php` avec relations (`belongsTo User`, `belongsTo Sector`, `belongsToMany Skill`) et scopes : `verified()`, `inSector()`, `inCountry()`, `withEducationYears()`, `withSkills()`, `search()`
- [X] T020 [P] [US1] Créer le modèle `app/Models/Sector.php` avec relation `hasMany Profile`
- [X] T021 [P] [US1] Créer le modèle `app/Models/Skill.php` avec relation `belongsToMany Profile`

### Authentification

- [X] T022 [US1] Créer le composant Livewire `app/Livewire/Auth/Register.php` avec validation email/password et envoi de l'email de vérification (Laravel `MustVerifyEmail`)
- [X] T023 [US1] Créer la vue `resources/views/livewire/auth/register.blade.php` pour le formulaire d'inscription
- [X] T024 [US1] Créer le composant Livewire `app/Livewire/Auth/Login.php` avec validation des credentials
- [X] T025 [US1] Créer la vue `resources/views/livewire/auth/login.blade.php` pour le formulaire de connexion
- [X] T026 [US1] Configurer les composants Livewire de reset password dans `app/Livewire/Auth/` (ForgotPassword.php, ResetPassword.php) avec vues correspondantes
- [X] T027 [US1] Ajouter le middleware `verified` sur les routes protégées dans `routes/web.php`

### Profil

- [X] T028 [US1] Créer le composant Livewire `app/Livewire/Profile/CreateProfile.php` avec : upload avatar optionnel (trait `WithFileUploads`), génération automatique d'un avatar par initiales si aucune photo fournie (service `app/Services/AvatarGenerator.php`), validation des champs obligatoires (full_name, job_title, country, sector_id), erreur inline sous le champ avatar si upload invalide sans effacer le reste du formulaire, stockage avatar réel sur S3/MinIO via `Storage::disk('s3')`
- [X] T029 [US1] Créer la vue `resources/views/livewire/profile/create-profile.blade.php` avec le formulaire multi-champs et sélecteur de compétences
- [X] T030 [US1] Créer le composant Livewire `app/Livewire/Profile/EditProfile.php` (même logique que CreateProfile, pré-rempli)
- [X] T031 [US1] Créer la vue `resources/views/livewire/profile/edit-profile.blade.php`
- [X] T032 [P] [US1] Créer la policy `app/Policies/ProfilePolicy.php` avec règles `view` (public), `update/delete` (propriétaire uniquement)
- [X] T033 [US1] Créer `app/Http/Controllers/ProfileController.php` avec méthode `show()` pour la page publique du profil
- [X] T034 [US1] Créer la vue publique `resources/views/profile/show.blade.php` affichant avatar, nom, titre, entreprise, localisation, compétences et badge de vérification si applicable
- [X] T035 [US1] Créer le composant Livewire `app/Livewire/Profile/ProfileCard.php` (carte compacte pour l'annuaire)
- [X] T036 [US1] Créer la vue `resources/views/livewire/profile/profile-card.blade.php` (avatar, nom, titre, entreprise, localisation, bouton Contacter)
- [X] T037 [US1] Mettre à jour `routes/web.php` avec les routes profil : `/profile/create`, `/profile/edit`, `/profiles/{profile}`
- [X] T095 [US1] Ajouter la route `POST /logout` dans `routes/web.php` (middleware `auth`) et le bouton de déconnexion dans `resources/views/layouts/app.blade.php` visible uniquement quand l'utilisateur est connecté

**Checkpoint**: Inscription → vérification email → profil complet → page publique fonctionnelle → déconnexion possible

---

## Phase 4: User Story 2 — Recherche et découverte de membres (Priority: P2)

**Goal**: Recherche fulltext dans l'annuaire avec filtres combinables, pagination et tri

**Independent Test**: Avec des profils seedés (20+ profils variés), effectuer une recherche par métier, appliquer 3 filtres simultanément, paginer les résultats et vérifier que les filtres sont conservés en changeant de page.

### Index et modèles

- [X] T038 [US2] Créer la migration `add_fulltext_index_to_profiles.php` dans `database/migrations/` avec l'index GIN PostgreSQL sur `(full_name, job_title, company)` comme défini dans `data-model.md`
- [X] T039 [US2] Enrichir les scopes fulltext dans `app/Models/Profile.php` : implémenter `search($query)` avec `whereRaw("to_tsvector('french', coalesce(full_name,'') || ' ' || coalesce(job_title,'') || ' ' || coalesce(company,'')) @@ plainto_tsquery('french', ?)", [$query])` (l'index GIN créé en T038 accélère cette expression)
- [X] T040 [US2] Créer le seeder de profils de test `database/seeders/ProfileSeeder.php` avec 50+ profils réalistes couvrant différents secteurs, pays et compétences

### Composant de recherche

- [X] T041 [US2] Créer le composant Livewire `app/Livewire/Directory/SearchDirectory.php` avec : propriétés `$query, $sector, $country, $yearFrom, $yearTo, $skills[], $verifiedOnly`, trait `WithPagination`, debounce 300ms sur `$query`, méthode `results()` retournant les profils filtrés
- [X] T042 [US2] Créer la vue `resources/views/livewire/directory/search-directory.blade.php` avec : panneau de filtres (side panel), grille de ProfileCards, pagination, tri par pertinence/date, toggle "vérifiés uniquement"
- [X] T043 [US2] Ajouter la route `/annuaire` dans `routes/web.php` pointant vers le composant SearchDirectory

**Checkpoint**: Recherche fulltext + filtres combinés + pagination fonctionnels, < 500ms sur profils seedés

---

## Phase 5: User Story 3 — Prise de contact entre membres (Priority: P3)

**Goal**: Formulaire de contact email entre membres avec notification au destinataire et confirmation à l'expéditeur

**Independent Test**: Connecté en tant que userA, envoyer un message au profil de userB. Vérifier dans Mailpit : (1) email reçu par userB avec sujet + corps + email de userA, (2) email de confirmation reçu par userA. Vérifier le message stocké en base.

### Modèle

- [X] T044 [US3] Créer la migration `create_contact_messages_table.php` dans `database/migrations/` avec colonnes : `id, from_user_id, to_user_id, subject, message, read_at, created_at`
- [X] T045 [US3] Créer le modèle `app/Models/ContactMessage.php` avec relations (`belongsTo User sender`, `belongsTo User receiver`), validation `from_user_id != to_user_id`

### Emails (contrats ENV-002 et ENV-003)

- [X] T046 [P] [US3] Créer la Mailable `app/Mail/ContactMessageReceived.php` (destinataire) avec : sujet du message, corps complet, nom et email de l'expéditeur, lien vers son profil — implémenter `ShouldQueue`
- [X] T047 [P] [US3] Créer la Mailable `app/Mail/ContactMessageSent.php` (confirmation expéditeur) avec : confirmation d'envoi, sujet et destinataire rappelés — implémenter `ShouldQueue`
- [X] T048 [P] [US3] Créer les templates email dans `resources/views/mail/contact/` : `received.blade.php` et `sent.blade.php`

### Composant de contact

- [X] T049 [US3] Créer le composant Livewire `app/Livewire/Contact/ContactForm.php` avec : propriétés `$subject, $message`, validation, création du ContactMessage, dispatch des deux Mailables en queue, confirmation d'envoi dans l'UI
- [X] T050 [US3] Créer la vue `resources/views/livewire/contact/contact-form.blade.php` avec champs sujet + message + bouton envoi
- [X] T051 [US3] Ajouter le bouton "Contacter" dans `resources/views/profile/show.blade.php` (afficher uniquement si connecté)
- [X] T052 [US3] Ajouter les routes contact dans `routes/web.php` avec middleware `auth` et rate limiting (`throttle:5,1`)

**Checkpoint**: Envoi de message → email reçu par destinataire avec email expéditeur → confirmation expéditeur → message en base

---

## Phase 6: User Story 4 — Publication et blog riche (Priority: P4)

**Goal**: Éditeur WYSIWYG pour créer des articles riches, statuts draft/published, commentaires modérés

**Independent Test**: Créer un article en brouillon → vérifier non accessible publiquement → publier → commenter → vérifier que le commentaire n'apparaît pas avant modération.

### Modèles et migrations

- [X] T096 [P] [US4] Créer la migration `create_blog_categories_table.php` dans `database/migrations/` avec colonnes : `id, name, slug, created_at`
- [X] T097 [P] [US4] Créer le modèle `app/Models/BlogCategory.php` avec relation `hasMany BlogPost` et scope `bySlug()`
- [X] T053 [P] [US4] Créer la migration `create_blog_posts_table.php` dans `database/migrations/` avec colonnes : `id, user_id, title, slug, content, excerpt, featured_image_url, status (enum: draft/published/archived), published_at, created_at, updated_at`
- [X] T054 [P] [US4] Créer la migration `create_blog_comments_table.php` dans `database/migrations/` avec colonnes : `id, blog_post_id, user_id, content, moderated_at, created_at`
- [X] T055 [P] [US4] Créer le modèle `app/Models/BlogPost.php` avec : relations (`belongsTo User`, `hasMany BlogComment`), scopes `published()`, `draft()`, `byCategory()`, computed `reading_time` (nb mots / 200)
- [X] T056 [P] [US4] Créer le modèle `app/Models/BlogComment.php` avec : relations (`belongsTo BlogPost`, `belongsTo User`), scope `approved()` (moderated_at non null)
- [X] T057 [US4] Créer la policy `app/Policies/BlogPostPolicy.php` : `create` (tout user connecté), `update/delete` (auteur ou admin)

### Éditeur et vues

- [X] T058 [US4] Configurer TipTap (ou Quill) dans `resources/js/app.js` et publier les assets nécessaires
- [X] T059 [US4] Créer le composant Livewire `app/Livewire/Blog/CreatePost.php` avec : upload image de couverture (trait `WithFileUploads`), intégration éditeur WYSIWYG, sélection statut, slug auto-généré depuis le titre
- [X] T060 [US4] Créer la vue `resources/views/livewire/blog/create-post.blade.php` avec éditeur WYSIWYG, champs titre, catégorie, image
- [X] T061 [P] [US4] Créer le composant Livewire `app/Livewire/Blog/EditPost.php` (pré-rempli depuis article existant)
- [X] T062 [US4] Créer la vue listing `resources/views/blog/index.blade.php` avec : article en vedette en haut, grille d'articles, filtrage par catégorie, tri par date
- [X] T063 [US4] Créer la vue article `resources/views/blog/show.blade.php` avec : contenu riche, auteur, date, temps de lecture, section commentaires approuvés
- [X] T064 [US4] Créer le composant Livewire `app/Livewire/Blog/CommentForm.php` : validation, création commentaire en statut "en attente" (moderated_at = null)
- [X] T065 [US4] Créer la vue `resources/views/livewire/blog/comment-form.blade.php`
- [X] T066 [US4] Ajouter les routes blog dans `routes/web.php` : `/blog`, `/blog/create`, `/blog/{slug}`, `/blog/{slug}/edit`

**Checkpoint**: Article créé → brouillon non accessible → publié visible → commentaire soumis en attente de modération

---

## Phase 7: User Story 5 — Modération et vérification des profils (Priority: P5)

**Goal**: Panel admin Filament pour vérifier les profils, modérer le contenu, visualiser les stats

**Independent Test**: Depuis le panel admin, approuver un profil de test → vérifier le badge sur le profil public + email ENV-004 reçu. Rejeter un autre profil → vérifier email ENV-005 reçu avec raison. Modérer un commentaire → vérifier qu'il devient visible.

### Infrastructure admin

- [X] T067 [US5] Installer et configurer Filament v3 panel dans `app/Providers/Filament/AdminPanelProvider.php` avec accès restreint au rôle `admin`
- [X] T068 [US5] Créer le seeder admin `database/seeders/AdminSeeder.php` pour créer le premier utilisateur admin avec `php artisan make:filament-user`
- [X] T069 [P] [US5] Créer la migration `create_moderation_logs_table.php` dans `database/migrations/` avec colonnes : `id, admin_user_id, action, subject_type, subject_id, notes, created_at`
- [X] T070 [P] [US5] Créer le modèle `app/Models/ModerationLog.php` avec relations (`belongsTo User admin`, morphTo `subject`)

### Emails de vérification (contrats ENV-004 et ENV-005)

- [X] T071 [P] [US5] Créer la Mailable `app/Mail/ProfileVerificationApproved.php` (profil approuvé) avec : lien vers le profil public, explication du badge — implémenter `ShouldQueue`
- [X] T072 [P] [US5] Créer la Mailable `app/Mail/ProfileVerificationRejected.php` (profil rejeté) avec : raison du rejet si fournie, invitation à corriger — implémenter `ShouldQueue`
- [X] T073 [P] [US5] Créer les templates email dans `resources/views/mail/profile/` : `approved.blade.php` et `rejected.blade.php`

### Resources Filament

- [X] T074 [US5] Créer `app/Filament/Resources/ProfileResource.php` avec : liste des profils non-vérifiés, actions "Approuver" (is_verified = true, dispatch ENV-004, log moderation) et "Rejeter" (with optional reason, dispatch ENV-005, log moderation)
- [X] T075 [US5] Créer `app/Filament/Resources/BlogPostResource.php` avec : liste des articles par statut, actions publish/unpublish/delete
- [X] T076 [US5] Créer `app/Filament/Resources/BlogCommentResource.php` avec : liste des commentaires en attente, actions "Approuver" (moderated_at = now()) et "Supprimer"
- [X] T077 [US5] Créer le widget stats `app/Filament/Widgets/StatsOverview.php` avec : total utilisateurs, posts publiés ce mois, nouvelles inscriptions ce mois
- [X] T078 [US5] Enregistrer le widget dans `AdminPanelProvider.php` et configurer le dashboard Filament

**Checkpoint**: Admin peut vérifier profils + modérer contenu + voir stats. Emails de vérification envoyés.

---

## Phase 8: Polish & Préoccupations transversales

**Purpose**: Qualité, sécurité, performance et préparation production

- [X] T079 [P] Vérifier et corriger le responsive design mobile-first sur toutes les vues principales (annuaire, profil, blog, contact) — utiliser les breakpoints Tailwind `sm`, `md`, `lg`
- [X] T080 [P] Ajouter les meta tags SEO dans `resources/views/layouts/app.blade.php` et les vues profil/blog (`title`, `description`, `og:image`)
- [X] T081 Configurer le rate limiting dans `routes/web.php` : `throttle:5,1` sur contact, `throttle:10,1` sur auth
- [X] T082 [P] Audit sécurité OWASP : vérifier la sanitisation HTML WYSIWYG (DOMPurify ou HTMLPurifier), la validation des uploads (MIME types), les policies sur toutes les actions sensibles
- [X] T083 Configurer Vite pour build de production dans `vite.config.js` : minification, hashing assets
- [X] T084 [P] Écrire les tests Pest Feature pour l'Auth dans `tests/Feature/Auth/RegisterTest.php`, `LoginTest.php`, `EmailVerificationTest.php`
- [X] T085 [P] Écrire les tests Pest Feature pour les profils dans `tests/Feature/Profile/CreateProfileTest.php`, `EditProfileTest.php`
- [X] T086 [P] Écrire les tests Pest Feature pour la recherche dans `tests/Feature/Directory/SearchTest.php` (fulltext, filtres, pagination)
- [X] T087 [P] Écrire les tests Pest Feature pour le contact dans `tests/Feature/Contact/ContactFormTest.php` avec `Mail::fake()`
- [X] T088 [P] Écrire les tests Pest Feature pour le blog dans `tests/Feature/Blog/BlogPostTest.php`, `CommentTest.php`
- [X] T089 [P] Écrire les tests Pest Feature pour l'admin dans `tests/Feature/Admin/ModerationTest.php`
- [ ] T090 Audit performance : seeder 1000+ profils et vérifier la recherche < 500ms avec `php artisan tinker` ou tests Pest

---

## Dépendances & Ordre d'exécution

### Dépendances entre phases

- **Setup (Phase 1)**: Aucune dépendance — peut démarrer immédiatement
- **Fondations (Phase 2)**: Dépend de Phase 1 — **BLOQUE toutes les user stories**
- **US1 (Phase 3)**: Dépend de Phase 2 — MVP minimal
- **US2 (Phase 4)**: Dépend de Phase 2 — nécessite les profils seedés (T040)
- **US3 (Phase 5)**: Dépend de Phase 2 — nécessite les profils (US1) pour avoir des destinataires
- **US4 (Phase 6)**: Dépend de Phase 2 — peut démarrer en parallèle avec US2/US3
- **US5 (Phase 7)**: Dépend de Phase 2 — nécessite les profils (US1) et le blog (US4) pour modérer
- **Polish (Phase 8)**: Dépend de la completion des user stories souhaitées

### Dépendances entre user stories

- **US1 (P1)**: Peut démarrer dès Phase 2 terminée — socle du MVP
- **US2 (P2)**: Peut démarrer dès Phase 2 terminée — bénéficie des profils US1 mais indépendant
- **US3 (P3)**: Peut démarrer dès Phase 2 terminée — bénéficie des profils US1 (destinataires) mais techniquement indépendant
- **US4 (P4)**: Peut démarrer dès Phase 2 terminée — totalement indépendant de US1/2/3
- **US5 (P5)**: Dépend logiquement de US1 (profils à modérer) et US4 (articles à modérer)

### Dépendances inter-phases notables

- T039 (scopes fulltext Profile) dépend de T019 (modèle Profile créé en Phase 3)
- T096/T097 (BlogCategory) doivent précéder T053 (migration blog_posts qui référence category_id)
- T094 (Sentry) doit être configuré avant le premier déploiement en production

### Au sein de chaque user story

- Migrations → Modèles → Composants Livewire → Vues → Routes
- Les tâches marquées [P] dans la même phase peuvent être exécutées en parallèle
- Chaque phase se termine par un Checkpoint de validation indépendante

---

## Exemples de parallélisation

### Phase 2 (Fondations) — Exemples parallèles

```
En parallèle :
  T008 : Migration sectors
  T009 : Migration skills
```

### Phase 3 (US1) — Exemples parallèles

```
En parallèle (modèles) :
  T018 : Modèle User
  T019 : Modèle Profile
  T020 : Modèle Sector
  T021 : Modèle Skill

En parallèle (auth vs profil) :
  T022→T027 : Composants Auth (Register, Login, Reset)
  T032 : Policy Profile
```

### Phase 5 (US3) — Exemples parallèles

```
En parallèle (mailables) :
  T046 : Mailable ContactMessageReceived
  T047 : Mailable ContactMessageSent
  T048 : Templates email
```

### Phase 7 (US5) — Exemples parallèles

```
En parallèle (resources Filament) :
  T075 : BlogPostResource
  T076 : BlogCommentResource

En parallèle (mailables vérification) :
  T071 : Mailable ProfileVerificationApproved
  T072 : Mailable ProfileVerificationRejected
  T073 : Templates email
```

---

## Stratégie d'implémentation

### MVP First (User Story 1 uniquement)

1. Compléter Phase 1 : Setup
2. Compléter Phase 2 : Fondations (CRITIQUE — bloque tout)
3. Compléter Phase 3 : US1 — Inscription & Profil
4. **STOP & VALIDER** : Inscription → email → profil → page publique fonctionnels
5. Déployer/démontrer le MVP de base

### Livraison incrémentale

1. Setup + Fondations → base prête
2. US1 → Annuaire de membres visible (MVP !)
3. US2 → Recherche fulltext + filtres → valeur communautaire immédiate
4. US3 → Contact entre membres → networking opérationnel
5. US4 → Blog → engagement communautaire
6. US5 → Modération → confiance et qualité du contenu

### Stratégie équipe (si plusieurs développeurs)

1. Équipe complète : Phase 1 + Phase 2
2. Une fois les Fondations terminées :
   - Dev A : US1 (Auth + Profils)
   - Dev B : US2 (Recherche)
   - Dev C : US4 (Blog)
3. US3 et US5 après que US1 est stable

---

## Notes

- [P] = fichiers différents, aucune dépendance — peut s'exécuter en parallèle
- [USn] = traçabilité vers la user story de `spec.md`
- Chaque user story est implémentable et testable indépendamment
- Utiliser `Mail::fake()` + `Storage::fake('s3')` dans les tests
- Committer après chaque phase ou groupe logique
- Consulter `contracts/email-notifications.md` pour le contenu exact des emails
- Consulter `data-model.md` pour les validations et relations exactes
- Consulter `quickstart.md` pour les commandes et variables d'environnement
