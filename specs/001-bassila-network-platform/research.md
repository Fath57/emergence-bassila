# Research: Plateforme Réseau Communautaire Bassilaise

**Branch**: `001-bassila-network-platform` | **Date**: 2026-04-07

## Stack Technologique

### Decision: Laravel 11 + Livewire 3 (monolithe server-rendered)

- **Decision**: Laravel 11 avec Livewire 3 pour les composants interactifs
- **Rationale**: La CDC spécifie explicitement cette stack. Livewire 3 réduit la quantité de JavaScript nécessaire tout en offrant une réactivité suffisante pour les formulaires et la recherche en temps réel. Un monolithe est adapté à l'échelle communautaire cible (< 5000 membres).
- **Alternatives considered**: SPA React/Vue avec API Laravel séparée (plus complexe, overkill pour le volume), Inertia.js (option valide mais non retenue dans le CDC)

### Decision: PostgreSQL 15+ avec FULLTEXT natif (MVP)

- **Decision**: PostgreSQL FULLTEXT search pour la recherche dans les profils
- **Rationale**: Déjà inclus dans PostgreSQL, zéro dépendance externe, suffisant pour 1000–5000 profils. Laravel supporte les indexes fulltext nativement depuis Laravel 8+.
- **Alternatives considered**: Meilisearch (recommandé pour version future quand > 10k profils), Elasticsearch (trop lourd pour MVP), Algolia (coût)
- **Implementation notes**: Index FULLTEXT sur `profiles.full_name`, `profiles.job_title`, `profiles.company`. Utiliser `whereFullText()` de Laravel ou `to_tsvector` / `to_tsquery` en SQL brut pour les requêtes complexes.

### Decision: Filament v3 pour le panel admin

- **Decision**: Filament v3 pour l'administration et la modération
- **Rationale**: Spécifié dans le CDC. Filament génère des panels CRUD complets avec très peu de code, intègre nativement Spatie/Permission pour les rôles, et s'intègre bien avec Laravel + Livewire.
- **Alternatives considered**: Admin panel custom Livewire (plus de travail), Nova (payant), Backpack (plus complexe)

### Decision: Spatie/Permission pour les rôles

- **Decision**: `spatie/laravel-permission` pour la gestion des rôles (admin, user, moderator)
- **Rationale**: Standard Laravel pour la gestion des permissions, intégration native avec Filament, documentation abondante.
- **Alternatives considered**: Gates/Policies Laravel natifs (insuffisants pour la granularité nécessaire), Bouncer (alternatif valide mais moins adopté)

## Architecture Livewire : Patterns Recommandés

### Composants Livewire 3 pour la recherche

```
SearchDirectory Livewire Component:
- Propriétés : $query, $filters (sector, country, yearFrom, yearTo, skills[], verifiedOnly)
- Méthode : updatedQuery() → déclenche la recherche avec debounce 300ms
- Pagination via WithPagination trait
- Pattern : computed property pour les résultats (lazy loading)
```

### Upload de fichiers avec Livewire 3

```
- Utiliser le trait WithFileUploads
- Validation côté Livewire avant envoi à S3/MinIO
- Intervention/Image pour redimensionner l'avatar avant upload
- Stocker l'URL publique S3 dans profiles.avatar_url
```

### Emails avec Laravel Mailable + Queues

```
- Créer des Mailable classes dédiées pour chaque type de notification
- Dispatcher via Bus::dispatch() ou Mail::queue() pour ne pas bloquer les requêtes
- Utiliser ShouldQueue interface sur les Mailables
- Driver: Redis ou database queue (MVP peut utiliser database driver)
```

## Sécurité

### OWASP Top 10 — Points d'attention

| Risque | Mitigation Laravel |
|--------|-------------------|
| Injection SQL | Utiliser Eloquent et Query Builder — jamais de SQL brut avec interpolation |
| XSS | Blade échappe automatiquement `{{ }}`. Utiliser `{!! !!}` uniquement pour HTML sanitisé (TipTap output) |
| CSRF | Middleware VerifyCsrfToken activé par défaut sur toutes les routes web |
| Auth issues | Laravel Sanctum ou sessions natives. Passwords via `bcrypt` ou `argon2` |
| Upload files | Valider MIME type et extension. Limiter taille (max 2MB pour avatars). Stocker hors webroot (S3) |
| Rate limiting | Utiliser `throttle` middleware sur les routes de contact et auth |

### Validation des uploads

```
- Avatar : max:2048 (2MB), mimes:jpg,jpeg,png, dimensions:min_width=200,min_height=200
- Images blog : max:5120 (5MB), mimes:jpg,jpeg,png,webp,gif
- Sanitiser le HTML WYSIWYG (TipTap output) avec HTMLPurifier ou DOMPurify
```

## Performance

### Indexation PostgreSQL

```sql
-- Profils
CREATE INDEX idx_profiles_sector ON profiles(sector_id);
CREATE INDEX idx_profiles_country ON profiles(country);
CREATE INDEX idx_profiles_education_end ON profiles(education_end_year);
CREATE INDEX idx_profiles_verified ON profiles(is_verified, created_at);
CREATE INDEX idx_profiles_fulltext ON profiles USING GIN(
  to_tsvector('french', coalesce(full_name,'') || ' ' || coalesce(job_title,'') || ' ' || coalesce(company,''))
);

-- Blog
CREATE INDEX idx_blog_posts_status_published ON blog_posts(status, published_at);
CREATE INDEX idx_blog_posts_user ON blog_posts(user_id);

-- Messages
CREATE INDEX idx_contact_messages_recipient ON contact_messages(to_user_id, read_at);
```

### Caching

- Utiliser cache Laravel (Redis recommandé, database pour MVP) pour les résultats de recherche fréquents
- Cache les listes de secteurs et compétences (données quasi-statiques) : `Cache::remember('sectors', 3600, ...)`
- N+1 queries : utiliser `with(['profile', 'skills', 'sector'])` dans les requêtes de listing

## Testing Strategy

### Pest — Structure recommandée

```
tests/Feature/
├── Auth/RegisterTest.php        # Email verification flow
├── Profile/CreateProfileTest.php # Profile creation + avatar upload
├── Directory/SearchTest.php     # Fulltext + filters + pagination
├── Contact/ContactFormTest.php  # Message sending + email dispatch
├── Blog/BlogPostTest.php        # CRUD + statuts + commentaires
└── Admin/ModerationTest.php     # Verification workflow + logs

tests/Unit/
└── Models/ProfileTest.php       # Scopes, relationships, accessors
```

### Approche TDD recommandée

1. Écrire le test Pest (Feature) qui décrit le comportement attendu
2. Faire tourner → RED
3. Implémenter le minimum pour faire passer le test → GREEN
4. Refactorer

Pour les emails : utiliser `Mail::fake()` + `Mail::assertSent()`
Pour les uploads : utiliser `Storage::fake('s3')`

## Environnement de développement

### Docker Compose recommandé

```yaml
services:
  app:     # PHP 8.2 + Laravel
  postgres: # PostgreSQL 15
  redis:   # Queue driver + cache
  minio:   # S3-compatible local storage
  mailpit: # Email testing UI (remplace Mailhog)
```

### Commandes clés

```bash
php artisan serve              # Dev server (ou via Sail)
php artisan migrate --seed     # Migrations + seeders
php artisan queue:work         # Worker pour emails asynchrones
./vendor/bin/pest              # Tests
npm run dev                    # Vite watch
```
