# Implementation Plan: Plateforme Réseau Communautaire Bassiloise - MVP

**Branch**: `001-bassila-network-platform` | **Date**: 2026-04-07 | **Spec**: [spec.md](./spec.md)
**Input**: Feature specification from `/specs/001-bassila-network-platform/spec.md`

## Summary

Construire un annuaire communautaire de networking pour la diaspora bassiloise. La plateforme permet l'inscription, la création de profils professionnels vérifiés, la recherche fulltext avec filtres, un formulaire de contact email entre membres, un blog riche avec modération, et un panel d'administration. Stack : Laravel 11 monolithique avec Livewire 3 pour l'interactivité, PostgreSQL pour la persistance et la recherche, Filament pour l'administration.

## Technical Context

**Language/Version**: PHP 8.2+
**Primary Dependencies**: Laravel 11, Livewire 3, Tailwind CSS, Filament v3, Spatie/Permission, Pest
**Storage**: PostgreSQL 15+, AWS S3 / MinIO (fichiers)
**Testing**: Pest (unit + feature), Laravel Dusk (E2E)
**Target Platform**: Web (navigateurs modernes), Docker + DigitalOcean / Dokku
**Project Type**: web-service (monolithe Laravel server-rendered)
**Performance Goals**: Recherche < 500ms sur 1000+ profils, chargement initial < 2s
**Constraints**: Mobile-first responsive, OWASP Top 10, accessibilité basique (WCAG AA)
**Scale/Scope**: Communauté cible ~500–5000 membres à terme

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

La constitution du projet est un template vide (non encore personnalisé pour ce projet). Aucune contrainte spécifique à vérifier.

**Post-design check** : L'architecture choisie (monolithe Livewire) est conforme à la stack définie dans le CDC. Aucune violation de principe identifiée.

## Project Structure

### Documentation (this feature)

```text
specs/001-bassila-network-platform/
├── plan.md              # This file (/speckit.plan command output)
├── research.md          # Phase 0 output
├── data-model.md        # Phase 1 output
├── quickstart.md        # Phase 1 output
├── contracts/           # Phase 1 output (email notification contracts)
│   └── email-notifications.md
└── tasks.md             # Phase 2 output (/speckit.tasks command)
```

### Source Code (repository root)

```text
app/
├── Http/
│   ├── Controllers/
│   │   └── ProfileController.php
│   └── Middleware/
├── Livewire/
│   ├── Auth/
│   │   ├── Login.php
│   │   └── Register.php
│   ├── Profile/
│   │   ├── CreateProfile.php
│   │   ├── EditProfile.php
│   │   └── ProfileCard.php
│   ├── Directory/
│   │   └── SearchDirectory.php
│   ├── Contact/
│   │   └── ContactForm.php
│   └── Blog/
│       ├── CreatePost.php
│       ├── EditPost.php
│       └── CommentForm.php
├── Models/
│   ├── User.php
│   ├── Profile.php
│   ├── Skill.php
│   ├── Sector.php
│   ├── BlogPost.php
│   ├── BlogCategory.php
│   ├── BlogComment.php
│   ├── ContactMessage.php
│   └── ModerationLog.php
├── Mail/
│   ├── ContactMessageReceived.php
│   ├── ContactMessageSent.php
│   ├── ProfileVerificationApproved.php
│   └── ProfileVerificationRejected.php
├── Filament/
│   └── Resources/
│       ├── ProfileResource.php
│       ├── BlogPostResource.php
│       └── BlogCommentResource.php
└── Policies/
    ├── ProfilePolicy.php
    └── BlogPostPolicy.php

database/
├── migrations/
│   ├── create_profiles_table.php
│   ├── create_skills_table.php
│   ├── create_sectors_table.php
│   ├── create_profile_skills_table.php
│   ├── create_blog_posts_table.php
│   ├── create_blog_comments_table.php
│   └── create_contact_messages_table.php
└── seeders/
    ├── SectorSeeder.php
    └── SkillSeeder.php

resources/
├── views/
│   ├── livewire/
│   │   ├── profile/
│   │   ├── directory/
│   │   ├── contact/
│   │   └── blog/
│   └── layouts/
│       └── app.blade.php
└── css/ js/

tests/
├── Feature/
│   ├── Auth/
│   ├── Profile/
│   ├── Directory/
│   ├── Contact/
│   ├── Blog/
│   └── Admin/
└── Unit/
    └── Models/
```

**Structure Decision**: Monolithe Laravel standard avec modules organisés par domaine dans `app/Livewire/`. Filament gère l'admin panel séparément. Pas de séparation frontend/backend (Livewire est server-rendered).

## Complexity Tracking

Aucune violation à justifier. Architecture standard Laravel monolithe adaptée à l'échelle cible.
