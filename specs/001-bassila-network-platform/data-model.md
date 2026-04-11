# Data Model: Plateforme Réseau Communautaire Bassiloise

**Branch**: `001-bassila-network-platform` | **Date**: 2026-04-07

## Entities

### User

Compte d'accès à la plateforme. Un utilisateur peut avoir un rôle admin, moderator ou user.

**Champs**:
- `id` — identifiant unique
- `email` — adresse email unique, validée
- `password` — mot de passe hashé
- `email_verified_at` — timestamp de vérification email (null si non vérifié)
- `created_at`, `updated_at`

**Rôles** (via Spatie/Permission): `admin`, `moderator`, `user`

**Relations**:
- `hasOne` Profile
- `hasMany` BlogPost
- `hasMany` ContactMessage (expéditeur)
- `hasMany` ContactMessage (destinataire)

**Validations**:
- email : unique, format email valide
- password : minimum 8 caractères

---

### Profile

Fiche professionnelle publique d'un utilisateur. Affichée dans l'annuaire.

**Champs**:
- `id`
- `user_id` — FK vers User
- `full_name` — nom complet (obligatoire)
- `bio` — biographie courte (max 500 caractères, optionnel)
- `avatar_url` — URL vers image de profil stockée (obligatoire, min 200x200px)
- `city` — ville (optionnel)
- `country` — pays (obligatoire)
- `job_title` — titre professionnel (obligatoire)
- `company` — entreprise actuelle (optionnel)
- `sector_id` — FK vers Sector (obligatoire)
- `education_start_year` — année de début d'études (optionnel)
- `education_end_year` — année de fin d'études (optionnel)
- `linkedin_url` — lien LinkedIn (optionnel, format URL valide)
- `portfolio_url` — lien portfolio (optionnel, format URL valide)
- `is_verified` — booléen, vrai si approuvé par un admin (défaut : false)
- `verified_at` — timestamp de vérification (null si non vérifié)
- `created_at`, `updated_at`

**Relations**:
- `belongsTo` User
- `belongsTo` Sector
- `belongsToMany` Skill (via table pivot `profile_skills`)

**Validations**:
- full_name : max 255 caractères
- bio : max 500 caractères
- avatar_url : image valide (jpg/png), min 200x200px, max 2MB
- education_end_year : >= education_start_year si les deux sont renseignés
- linkedin_url, portfolio_url : format URL valide si renseignés

**Scopes**:
- `verified()` — profils avec is_verified = true
- `inSector($sectorId)` — filtrage par secteur
- `inCountry($country)` — filtrage par pays
- `withEducationYears($from, $to)` — filtrage par plage d'années d'études
- `withSkills($skillIds)` — filtrage par compétences (pivot)
- `search($query)` — fulltext sur full_name, job_title, company

---

### Skill

Tag de compétence professionnelle réutilisable.

**Champs**:
- `id`
- `name` — libellé unique de la compétence (ex: "Laravel", "Marketing")
- `created_at`

**Relations**:
- `belongsToMany` Profile (via pivot `profile_skills`)

**Validations**:
- name : unique, max 100 caractères

---

### Sector

Catégorie d'activité professionnelle. Données gérées par les admins.

**Champs**:
- `id`
- `name` — libellé du secteur (ex: "IT", "Agriculture", "Santé")
- `created_at`

**Relations**:
- `hasMany` Profile

**Données initiales** (seeders): Agriculture, Commerce, Santé, Éducation, IT & Technologie, Industrie, Finance & Banque, BTP & Immobilier, Transports & Logistique, Médias & Communication, Administration publique, Autre

---

### profile_skills (Pivot)

Table de liaison entre Profile et Skill.

**Champs**:
- `id`
- `profile_id` — FK vers Profile
- `skill_id` — FK vers Skill

---

### BlogPost

Article de contenu publié sur la plateforme.

**Champs**:
- `id`
- `user_id` — FK vers User (auteur)
- `title` — titre de l'article (obligatoire)
- `slug` — URL-friendly, unique, généré depuis le titre
- `content` — contenu riche HTML (obligatoire, généré par WYSIWYG)
- `excerpt` — extrait court pour les listings (optionnel, max 500 caractères)
- `featured_image_url` — URL image de couverture (optionnel)
- `status` — enum : `draft`, `published`, `archived` (défaut : `draft`)
- `category_id` — FK vers BlogCategory (optionnel)
- `published_at` — timestamp de publication (null si brouillon)
- `created_at`, `updated_at`

**Relations**:
- `belongsTo` User
- `hasMany` BlogComment

**Validations**:
- title : max 255 caractères
- content : non vide si statut = published
- slug : unique
- status : dans ['draft', 'published', 'archived']

**Scopes**:
- `published()` — articles avec status = 'published' et published_at <= now()
- `draft()` — articles non publiés
- `byCategory($categoryId)` — filtrage par catégorie

**Computed**:
- `reading_time` — calculé depuis le contenu (nb mots / 200 mots/min)

---

### BlogComment

Commentaire soumis sur un article. Soumis à modération.

**Champs**:
- `id`
- `blog_post_id` — FK vers BlogPost
- `user_id` — FK vers User (auteur du commentaire)
- `content` — texte du commentaire (max 1000 caractères)
- `moderated_at` — timestamp d'approbation par un modérateur (null = en attente)
- `created_at`

**Relations**:
- `belongsTo` BlogPost
- `belongsTo` User

**Validations**:
- content : max 1000 caractères, non vide

**Scopes**:
- `approved()` — commentaires avec moderated_at non null

---

### ContactMessage

Message envoyé entre membres via le formulaire de contact.

**Champs**:
- `id`
- `from_user_id` — FK vers User (expéditeur)
- `to_user_id` — FK vers User (destinataire)
- `subject` — sujet du message (obligatoire, max 255 caractères)
- `message` — corps du message (obligatoire)
- `read_at` — timestamp de lecture (null = non lu)
- `created_at`

**Relations**:
- `belongsTo` User (sender)
- `belongsTo` User (receiver)

**Validations**:
- subject : non vide, max 255 caractères
- message : non vide
- from_user_id ≠ to_user_id (impossible de se contacter soi-même)

---

### ModerationLog (implicite via Filament Activity Log)

Trace horodatée des actions de modération. Peut être géré via le package `spatie/laravel-activitylog` ou un modèle custom.

**Champs**:
- `id`
- `admin_user_id` — FK vers User (qui a effectué l'action)
- `action` — type d'action (ex: `verify_profile`, `reject_profile`, `approve_comment`, `delete_comment`)
- `subject_type` — type de l'entité modérée (Profile, BlogPost, BlogComment)
- `subject_id` — ID de l'entité modérée
- `notes` — raison optionnelle (ex: raison de rejet)
- `created_at`

---

## State Transitions

### Profile.is_verified

```
[Créé] → is_verified: false
    ↓ Admin approve
[Vérifié] → is_verified: true, verified_at: now()
    ↓ Admin reject
[Non vérifié + notification email] → is_verified: false
```

### BlogPost.status

```
[draft] → (auteur publie) → [published] → published_at: now()
[published] → (auteur ou admin archive) → [archived]
[archived] → (admin restaure) → [published]
[draft] → (admin supprime) → supprimé
```

### BlogComment.moderated_at

```
[null = en attente] → (admin approuve) → moderated_at: now()
[null = en attente] → (admin rejette) → supprimé
```

## Indexes

```sql
-- profiles
CREATE INDEX idx_profiles_user_id ON profiles(user_id);
CREATE INDEX idx_profiles_sector_id ON profiles(sector_id);
CREATE INDEX idx_profiles_country ON profiles(country);
CREATE INDEX idx_profiles_is_verified ON profiles(is_verified);
CREATE INDEX idx_profiles_education_end_year ON profiles(education_end_year);
CREATE INDEX idx_profiles_fulltext ON profiles USING GIN(
  to_tsvector('french',
    coalesce(full_name, '') || ' ' ||
    coalesce(job_title, '') || ' ' ||
    coalesce(company, '')
  )
);

-- blog_posts
CREATE INDEX idx_blog_posts_status_published_at ON blog_posts(status, published_at);
CREATE INDEX idx_blog_posts_user_id ON blog_posts(user_id);

-- contact_messages
CREATE INDEX idx_contact_messages_to_user_id ON contact_messages(to_user_id);
CREATE INDEX idx_contact_messages_read_at ON contact_messages(read_at);

-- blog_comments
CREATE INDEX idx_blog_comments_blog_post_id ON blog_comments(blog_post_id);
CREATE INDEX idx_blog_comments_moderated_at ON blog_comments(moderated_at);
```

## ER Diagram (simplifié)

```
users (1) ────────── (1) profiles
      (1) ────────── (*) blog_posts
      (1) ────────── (*) contact_messages [from_user_id]
      (1) ────────── (*) contact_messages [to_user_id]
      (1) ────────── (*) blog_comments

profiles (*) ──── (1) sectors
         (*) ──── (*) skills  [via profile_skills]

blog_posts (1) ──── (*) blog_comments
```
