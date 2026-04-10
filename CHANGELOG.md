# Changelog

All notable changes to Bassila Network are documented here.

Format based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).
Versions follow [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [Unreleased]

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
