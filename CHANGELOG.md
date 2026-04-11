# Changelog

All notable changes to Bassila Network are documented here.

Format based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).
Versions follow [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [Unreleased]

### Added — Admin autonomy sub-project ① Site Settings (2026-04-11)

- **Typed key-value settings table** with 7 initial feature flags:
  - `site.registration_open`, `site.maintenance_mode`, `site.maintenance_message`
  - `blog.public_creation`, `blog.require_moderation`
  - `comments.enabled`, `comments.require_moderation`
- `Setting` Eloquent model with `casted_value` accessor (bool/string), `LogsActivity` trait for audit trail
- Global `setting()` helper with 2-layer caching: persistent `Cache::rememberForever` + per-request `app()->instance()` memo → zero DB queries after priming
- Idempotent `SettingSeeder` using `firstOrNew` 2-branch pattern: re-running the seeder preserves admin-edited values while re-syncing metadata (label/description/group/sort_order)
- `/admin/parametres` Livewire page rendering 3 grouped cards from DB, with nested `values[group][field]` binding matching Livewire v3 dot-notation semantics, save action persisting changes and logging activity, and a history section showing the last 20 changes ordered by id
- `MaintenanceModeCheck` middleware — 503 + custom French message for all non-admin public routes when maintenance mode is on; `/connexion`, `/admin/*`, `/livewire/*`, `/build/*`, `/up` are always allowlisted
- `CheckRegistrationOpen` middleware — `/inscription` redirects to `/connexion` with flash message when registration is closed; "Créer un profil" CTA hidden from nav in the same case
- `BlogPostPolicy::create` now reads `setting('blog.public_creation')`; `/blog/rediger` route is gated by `can:create,App\Models\BlogPost` middleware
- `CreatePost::save` / `EditPost::save` force `status = draft` for non-admin publications when `blog.require_moderation` is enabled, with a French info flash
- `CommentForm::submit` refuses to save when `comments.enabled = false` and branches `moderated_at` on `comments.require_moderation`
- `blog/show` template hides the comment form entirely when comments are disabled, showing a neutral placeholder

### Added — Baseline RBAC (2026-04-11)

- 2 baseline permissions seeded: `admin.access`, `settings.manage`, both attached to the `admin` role
- `EnsureUserIsAdmin` middleware migrated from `hasRole('admin')` to `can('admin.access')`
- Full 17-permission set and role rename will ship with sub-project ② RBAC

### Changed

- `spatie/laravel-activitylog` migration now published and run on both dev and test databases
- `app/helpers.php` registered via `composer.json` autoload `files`

### Fixed

- Setting cache: plain-array payload (not Eloquent Collection) to survive cross-request deserialization on the database cache driver
- Setting cache: per-request memo via container binding to avoid repeated `cache` table lookups (one effective DB/cache hit per HTTP request)
- Activity log history ordered by `id` (not `created_at`) to stay deterministic across events sharing a timestamp

### Test coverage

- 32 new tests (unit + feature) covering Setting model, helper, seeder, maintenance middleware, registration gate, blog flags, comment flags, and the admin page

## [0.1.0] — 2026-04-08

Initial MVP release on branch `001-bassila-network-platform`.

### Added

#### Infrastructure
- Laravel 11 project with PHP 8.2+, Livewire 4, Filament 5, Tailwind CSS 4
- Docker Compose stack: PostgreSQL 15, Redis 7, MinIO, Mailpit
- Spatie/Permission for role-based access control (`admin`, `moderator`, `user`)
- GitHub Actions CI pipeline (`quality.yml`) running Laravel Pint and Larastan (level 5)
- Sentry integration for error monitoring (`config/sentry.php`)
- Vite build pipeline with Tailwind CSS and production asset hashing

#### Authentication (US1)
- `Register` Livewire component — email/password signup with `MustVerifyEmail`
- `Login` Livewire component — credentials validation with session regeneration
- `ForgotPassword` and `ResetPassword` Livewire components
- Guest layout (`layouts/guest.blade.php`) for auth pages
- Email verification flow with redirect to profile creation on success
- Rate limiting on auth routes (`throttle:10,1`)
- Logout route with session invalidation

#### Member Profiles (US1)
- `profiles` table migration with full schema: name, bio, avatar, location, sector, education years, LinkedIn, portfolio, verification fields
- `profile_skills` pivot table for many-to-many skill associations
- `Profile` Eloquent model with scopes: `verified()`, `inSector()`, `inCountry()`, `withEducationYears()`, `withSkills()`, `search()`
- `Sector` and `Skill` models with seeders (12 sectors, pre-defined skills)
- `CreateProfile` Livewire component with avatar upload to S3/MinIO, auto-generated initials avatar via `AvatarGenerator` service when no photo provided
- `EditProfile` Livewire component loading the authenticated user's own profile
- Public profile page (`profile/show.blade.php`) with skills, education, links and verification badge
- `ProfileCard` Livewire component for directory grid display
- `ProfilePolicy` — public read, owner-only write
- `ProfileSeeder` with 25 realistic member profiles

#### Directory Search (US2)
- `SearchDirectory` Livewire component with URL-bound state (`#[Url]`)
- Fulltext search using PostgreSQL `to_tsvector` / `plainto_tsquery` with GIN index
- Combinable filters: sector, country, education year range, skills (multi-select), verified-only toggle
- Debounced search input (300ms), live filter updates
- Pagination with `WithPagination` trait, page reset on filter change
- `resetFilters()` action clears all active filters

#### Member Contact (US3)
- `contact_messages` table migration
- `ContactMessage` model with `sender` and `receiver` relations
- `ContactForm` Livewire component embedded on the public profile page
- Per-user rate limiting: 5 messages per minute via `RateLimiter`
- `ContactMessageReceived` Mailable — sent to recipient with sender info and message body
- `ContactMessageSent` Mailable — confirmation sent to the sender
- Both mailables implement `ShouldQueue` for async dispatch
- HTML email templates for both notification types

#### Blog (US4)
- `blog_categories`, `blog_posts`, `blog_comments` table migrations
- `BlogPost` model with status enum (`draft`, `published`, `archived`), computed `reading_time` accessor, scopes `published()`, `draft()`, `byCategory()`
- `BlogComment` model with `approved()` scope (`moderated_at IS NOT NULL`)
- `BlogCategory` model with `posts()` relation
- `CreatePost` and `EditPost` Livewire components with image upload, auto-slug from title, status selection
- `CommentForm` Livewire component — comments submitted pending moderation (`moderated_at = null`)
- Public blog listing (`blog/index.blade.php`) with featured article, category sidebar, pagination
- Public article view (`blog/show.blade.php`) with author card, reading time, approved comments section
- `BlogPostPolicy` — any authenticated user can create, author or admin can edit/delete
- Route ordering fix: `/blog/rediger` defined before `/blog/{slug}` to avoid slug capture

#### Admin Panel (US5)
- Filament 5 panel at `/admin`, restricted to users with the `admin` role
- `ProfileResource` — list all profiles, approve (sets `is_verified = true`, dispatches `ProfileVerificationApproved` mail, logs action) and reject (with optional reason, dispatches `ProfileVerificationRejected` mail)
- `BlogPostResource` — list by status, publish/unpublish/delete actions
- `BlogCommentResource` — list pending comments, approve (`moderated_at = now()`) and delete
- `StatsOverview` widget — total members, new registrations this month, posts published this month
- `ModerationLog` model tracking all admin actions
- `ProfileVerificationApproved` and `ProfileVerificationRejected` Mailables with HTML templates
- `AdminSeeder` creating the initial admin account

#### Developer Experience
- `ProfileFactory`, `SectorFactory`, `BlogPostFactory`, `BlogCommentFactory` for test data
- `HasFactory` trait added to `Profile`, `Sector`, `BlogPost`, `BlogComment` models
- `tests/Pest.php` configuring `RefreshDatabase` for all Feature tests
- 11 Pest feature tests across all modules:
  - `Auth/RegisterTest`, `Auth/LoginTest`, `Auth/EmailVerificationTest`
  - `Profile/CreateProfileTest`, `Profile/EditProfileTest`
  - `Directory/SearchTest`
  - `Contact/ContactFormTest`
  - `Blog/BlogPostTest`, `Blog/CommentTest`
  - `Admin/ModerationTest`
- Main application layout (`layouts/app.blade.php`) with responsive navigation, flash messages, SEO meta tags (`og:title`, `og:description`, `og:image`)

### Technical notes

- The blog WYSIWYG editor (TipTap) is not yet integrated — the current implementation uses a `<textarea>`. HTML sanitization should be added before enabling rich content input.
- T090 (performance audit with 1000+ seeded profiles, search < 500ms) is pending and requires a running environment.

---

[Unreleased]: https://github.com/your-org/emergence-bassila/compare/v0.1.0...HEAD
[0.1.0]: https://github.com/your-org/emergence-bassila/releases/tag/v0.1.0
