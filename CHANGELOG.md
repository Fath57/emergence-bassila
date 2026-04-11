# Changelog

All notable changes to Bassila Network are documented here.

Format based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).
Versions follow [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [Unreleased]

### Added — Admin autonomy sub-project ③ Rich Blog Editor (2026-04-11)

- **TipTap 3 editor** wired via a dedicated `resources/js/editor.js` Vite entry
  (~180 KB gzipped, loaded only on `/blog/rediger` and `/blog/{slug}/modifier`
  through `@push('head') @vite(...)`). StarterKit (headings H2-H4), Link,
  Image, Table (+Row/Header/Cell from `@tiptap/extension-table`),
  CodeBlockLowlight with github.css theme, Youtube embeds. Toolbar with
  `data-cmd` buttons: bold, italic, strike, H2-H4, bullet/ordered lists,
  blockquote, code block, hr, link (prompt), image (file picker + forced
  alt-text modal + `/blog/upload-image` fetch), youtube (prompt), undo/redo
- **`<x-tiptap-editor>`** anonymous Blade component: toolbar + mount point +
  hidden `<textarea data-editor-content wire:model.live.debounce.3000ms>`
  that TipTap writes HTML into via `dispatchEvent('input')` so Livewire's
  autosave hook fires on the PHP side. `wire:ignore` on the root preserves
  editor state across re-renders
- **`BlogContentSanitizer` service** wrapping HTMLPurifier (ezyang/htmlpurifier
  direct — `mews/purifier` caps at Laravel 12) with a curated whitelist:
  `p, br, hr, h2-h4, strong, em, s, u, code, ul, ol, li, blockquote, pre,
  a[href|rel|target], img[src|alt|title|width|height], iframe[src|...],
  table, thead, tbody, tr, th, td`. `URI.SafeIframeRegexp` pins iframes to
  `https://www.youtube.com/embed/` and `https://player.vimeo.com/video/`.
  `CSS.AllowedProperties = []` disallows all inline styles.
  `URI.AllowedSchemes` rejects `javascript:`, `data:`, `file:`, `vbscript:`.
  `Core.EscapeNonASCIICharacters = false` preserves French accents. Warns
  in the log if >30% of bytes were stripped
- **`BlogImageUploader` service** using `intervention/image` v4 encoder API
  (`JpegEncoder(quality:85)`, `PngEncoder`, `WebpEncoder`, `GifEncoder`):
  `uploadCover` crops to 1600×900 JPEG, `uploadInline` scales down wide
  images to max 1400px and preserves native format. Writes to
  `Storage::disk('public')` under `blog/covers/{Y/m}/` and `blog/inline/{Y/m}/`
- **`BlogImageUploadController`** at `POST /blog/upload-image` with
  `can:create,App\Models\BlogPost` middleware; accepts jpg/jpeg/png/webp/gif
  ≤ 10 MB, returns `{"url": "..."}`
- **`add_rich_editor_fields_to_blog_posts_table`** migration: nullable
  `meta_title` (70 chars — Google SERP limit) and `meta_description`
  (160 chars)
- **`BlogPost` model**: `meta_title`/`meta_description` added to fillable,
  two new accessors `resolved_meta_title` and `resolved_meta_description`
  with fallback to `title` and `Str::limit(strip_tags($content), 155)`
- **CreatePost refactor**: `autoSave()` creates the row on first call
  (title non-empty gate), updates it on subsequent calls, persists meta
  fields on explicit save, redirects to `/mes-articles` after save,
  uses `BlogImageUploader::uploadCover` against the local `public` disk
  (migrated away from s3), runs every persist through the sanitizer
- **EditPost refactor**: same autosave semantics on existing posts,
  meta fields loaded on mount, `published_at` set only on first transition
  to published
- **`/mes-articles`** (`App\Livewire\Blog\MyPosts`, auth-only): 4 tabs
  (all / draft / published / archived) with counts, ordering CASE
  (draft→published→archived) then `latest('updated_at')`, paginated 15/page,
  delete action with `wire:confirm`, `#[Url]` binding on the filter tab
- **`/blog/preview/{post}`** (`App\Livewire\Blog\PreviewPost`): mount() 403s
  unless `post->user_id === Auth::id()` or `Auth::user()->can('posts.edit.any')`.
  Renders the draft like the public template with an amber
  non-dismissible "Aperçu — Cet article est {status} et n'est pas visible
  publiquement" banner and a "Retour à l'édition" link
- **Nav "Mes articles"** link added to `partials/nav.blade.php` between
  "Mon profil" and logout, with active highlight
- **SEO meta tags** on `blog/show.blade.php`: `@section('title'|'description')`
  now use the resolved accessors, `@push('head')` stack injects OG +
  Twitter Card tags (`og:type=article`, `og:image`/`twitter:image` fallback
  to `featured_image_url`). `@stack('head')` added to `layouts/app.blade.php`
  so page-level meta lands in the document head
- **Public blog show**: content now rendered via `{!! $post->content !!}`
  wrapped in `prose prose-lg` from `@tailwindcss/typography` (not
  `nl2br(e(...))`) since content is sanitized HTML after ③
- **`migrate_plain_text_posts_to_html`** one-shot migration: backs up every
  `blog_posts` row to `storage/app/backups/blog_posts_YYYY-MM-DD_HHMMSS.json`
  then wraps plain text on blank lines into `<p>` tags (preserving single
  line breaks via `nl2br`), runs each through the sanitizer. Idempotent:
  skips any post already containing block-level HTML tags
  (`p|h1-6|ul|ol|blockquote|pre|figure`). `down()` restores from the latest
  backup. Rollback-safe
- `@tailwindcss/typography` plugin added to `resources/css/app.css` for
  `.prose` styling inside both the editor and the public blog render
- PHPUnit `ini memory_limit=512M` added to `phpunit.xml` — the image-upload
  fixtures (`UploadedFile::fake()->image(2400, 1000)`) pushed past the
  default 128M during the full test run

### Test coverage

- 25 new tests (5 sanitizer + 3 uploader + 4 upload endpoint + 6 CreatePost
  + 4 EditPost + 3 MyPosts + 3 PreviewPost + 3 migration)
- 35/35 blog tests passing end-to-end. The 5 suite-wide failures in
  `ContactFormTest` and `SearchTest` pre-date this sub-project and are
  unrelated to it

### Added — Admin autonomy sub-project ② RBAC + User Management (2026-04-11)

- **17 permissions** seeded across 8 domains via the new `RolePermissionSeeder` (replaces the minimal `RoleSeeder`): `posts.*`, `comments.moderate`, `profiles.moderate`, `users.*`, `roles.view`, `settings.manage`, `newsletter.*`, `admin.access`
- **3 roles redefined**: `admin` (full), `editor` (trusted contributor — `posts.create/publish.own/edit.own + admin.access`), `member` (default — `posts.create + posts.edit.own`)
- Legacy role migration: existing `user` role assignments become `member`, `moderator` become `editor`, orphaned rows deleted
- **All `hasRole('admin')` authorization gates migrated to `can(...)` checks** across BlogPostPolicy, ProfilePolicy, all 4 admin Livewire components, Register, seeders, and 11 test files. Only 2 legitimate role-membership checks remain (in `User::isLastActiveAdmin` and `EditUser::changeRole`) where the intent is "is this user in the admin role" rather than an authorization gate
- New `publish()` method on `BlogPostPolicy` — owner gets `posts.publish.own`, others need `posts.edit.any`
- **`is_active` flag on users** — deactivated users cannot log in, their public profile returns 404, and they are hidden from the directory
- **`user_invitations` table** + `UserInvitation` model with token, expiry, `accepted_at`, and LogsActivity audit
- **Public invitation acceptance flow** at `/invitation/{token}` — token validation (404 / expired-redirect / already-used-redirect), pre-filled first/last name, password form, transactional account creation + role assignment + auto-login + redirect to profile creation
- **`InvitationMail`** template matching the existing transactional mail aesthetic (white header with logo, blue accent border, navy footer, optional personal message block)
- **Admin `/admin/utilisateurs`** list page with 2 tabs (active users, pending invitations), search by name/email, role filter, status filter (active/inactive/unverified), resend and cancel invitation actions
- **Admin `/admin/utilisateurs/inviter`** invite form — email (required + unique against users AND pending invitations), optional first/last name, role dropdown with French labels, optional personal message (500 char max), creates `UserInvitation` with 64-char token and 7-day expiry, queues `InvitationMail`, redirects to pending tab
- **Admin `/admin/utilisateurs/{user}/editer`** edit page with 4 sections (basic info, role dropdown, active/inactive toggle, activity history from activity_log) — **anti-lockout guards** block demoting or deactivating the last active admin both in UI (disabled controls) and at action entry (explicit `User::isLastActiveAdmin` check)
- **Admin `/admin/roles`** read-only permission matrix page — 3 roles × 17 permissions grid grouped by French domain labels, checkmark/em-dash cells, disclaimer banner explaining roles are code-managed, footnote clarifying the `posts.create` × `member` setting-gated case
- **User model additions**: `LogsActivity` trait with `logOnly(['email', 'is_active'])`, static `isLastActiveAdmin(User)` method for anti-lockout guards, `is_active` added to fillable and cast as boolean
- **Login deactivation check** — after a successful `Auth::attempt`, verifies `is_active` and logs out + displays French error if false
- **23 new tests** covering unit (isLastActiveAdmin × 4), seeder (role/permission creation × 6), deactivation semantics (5), invitation acceptance (5), invitation send (4), anti-lockout + edit user (6), role matrix (2), users list (3)
- Admin sidebar nav now includes **Utilisateurs** (user-plus icon) and **Rôles** (shield icon) entries
- **Baseline permissions bridge**: `admin.access` and `settings.manage` were seeded early during sub-project ① preparation so the admin middleware and settings page could use `can(...)` from the start

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
