# RBAC + User Management — Design Spec

**Project:** Bassila Émergence — Admin autonomy roadmap, sub-project ② (RBAC foundation)
**Date:** 2026-04-11
**Status:** Draft — awaiting user review

---

## 1. Context

Bassila Émergence currently has **3 roles** (`admin`, `moderator`, `user`) but
**zero granular permissions**. All authorization is a binary
`$user->hasRole('admin')` scattered across policies, middlewares, and Livewire
components. The `moderator` role is seeded but never checked anywhere in code.

There is **no UI** for managing users, inviting new members, changing roles,
or deactivating accounts. The only way to create a user is through the public
registration form.

This sub-project rebuilds the authorization layer on top of Spatie Permission
v6's permission model, redefines the 3 roles with a cohesive permission set,
migrates every existing `hasRole()` check to `can()`, and delivers the admin UI
for the full user lifecycle: invite → accept → edit → deactivate.

It is the **second** sub-project of the admin autonomy roadmap. Its outputs
(the 17 seeded permissions) are the **contracts** consumed by sub-projects
③ Blog editor and ④ Newsletter — those specs reference permissions like
`posts.publish.own`, `newsletter.campaigns.send`, and `admin.access` as if
they already exist. This spec is what makes them exist.

## 2. Goals

- Replace every `hasRole('admin')` in the codebase with a granular `can()` check.
- Seed **17 permissions** grouped into 8 domains (`posts`, `comments`, `profiles`,
  `users`, `roles`, `settings`, `newsletter`, `admin`).
- Redefine **3 roles** (`admin`, `editor`, `member`) with a curated permission
  map that balances authority and safety.
- Deliver an admin UI with 4 pages: user list (+ invitations tab), invite
  user form, edit user page, read-only role/permission matrix.
- Deliver a public invitation acceptance flow (token-based, 7-day expiry,
  auto-login on accept).
- Guarantee **anti-lockout**: no action can leave zero active admins.
- Audit every role change, deactivation, invitation sent, and invitation
  accepted via `spatie/laravel-activitylog` (same pattern as ① Settings).
- Deactivated users cannot log in, are hidden from the public directory, and
  their public profile page returns 404.

## 3. Non-Goals

Explicitly **out of scope** for this sub-project:

- **Editing roles from the UI** (create / rename / delete roles). Roles are
  managed via the seeder, versioned in git.
- **Editing the permission matrix from the UI**. Same reasoning.
- **Direct user → permission assignments** (bypass of role). Spatie supports it,
  we intentionally don't expose it.
- **Multi-role users**. Technically Spatie allows N roles per user; we enforce
  exactly 1 through the UI.
- **SSO / OAuth / SAML**. Login stays email + password.
- **Impersonation** ("login as user"). Useful for debugging but has RGPD
  implications; deferred to a later iteration if a concrete need arises.
- **Login rate limiting** beyond Laravel's existing `throttle:10,1` on
  `/connexion`. Sufficient for this project's size.
- **Automatic purge of expired invitations**. Rows are kept for audit. A
  scheduled cleanup can be added later if the table grows.
- **Email change workflow with re-verification**. Admins can edit user emails
  directly; the edited email is treated as verified. Proper re-verification is
  deferred.

## 4. Architecture overview

```
┌─────────────────────────────────────────────────────────────────────┐
│                         Admin UI (Livewire)                        │
│                                                                     │
│   /admin/utilisateurs                  → Admin\Users                │
│   /admin/utilisateurs/inviter          → Admin\InviteUser           │
│   /admin/utilisateurs/{user}/editer    → Admin\EditUser             │
│   /admin/roles                         → Admin\RoleMatrix (RO)      │
│                                                                     │
│   /invitation/{token}                  → Auth\AcceptInvitation      │
│                                          (public, no auth required) │
└──────────────────┬──────────────────────────────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────────────────────────────┐
│             RolePermissionSeeder (source of truth)                  │
│                                                                     │
│  3 roles × 17 permissions, seeded via Spatie models.                │
│  Idempotent via firstOrCreate + syncPermissions.                    │
│  Migrates legacy `user`/`moderator` roles to `member`/`editor`.     │
└──────────────────┬──────────────────────────────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────────────────────────────┐
│                  Authorization layer (migrated)                     │
│                                                                     │
│  OLD:  hasRole('admin')                                             │
│  NEW:  can('posts.publish.own'), can('settings.manage'), …          │
│                                                                     │
│  Touched files:                                                     │
│   • app/Http/Middleware/EnsureCanAccessAdmin (renamed)              │
│   • app/Policies/BlogPostPolicy (create/update/delete/publish)      │
│   • app/Policies/ProfilePolicy (delete)                             │
│   • app/Livewire/Admin/* (explicit authorize() at action entry)     │
│   • All ① Settings plan usages (patched)                            │
└─────────────────────────────────────────────────────────────────────┘
```

Single source of truth: one seeder, one permission set, one `can()` call per
authorization check. No scattered role literals.

## 5. Permission inventory (17 permissions, 3 roles)

| Permission | Description | `admin` | `editor` | `member` |
|---|---|---|---|---|
| **Posts** | | | | |
| `posts.create` | Create a post (draft state) | ✅ | ✅ | ✅ \* |
| `posts.publish.own` | Publish your own post (bypass `blog.require_moderation`) | ✅ | ✅ | ❌ |
| `posts.edit.own` | Edit your own post | ✅ | ✅ | ✅ |
| `posts.edit.any` | Edit any post (includes publishing someone else's) | ✅ | ❌ | ❌ |
| `posts.delete.any` | Delete any post | ✅ | ❌ | ❌ |
| **Comments** | | | | |
| `comments.moderate` | Approve / reject / delete comments | ✅ | ❌ | ❌ |
| **Profiles** | | | | |
| `profiles.moderate` | Approve / reject profile verification requests | ✅ | ❌ | ❌ |
| **Users** | | | | |
| `users.view` | See the admin user list | ✅ | ❌ | ❌ |
| `users.invite` | Send invitation emails | ✅ | ❌ | ❌ |
| `users.edit` | Edit user fields (name, email, `is_active`) | ✅ | ❌ | ❌ |
| `users.assign-role` | Change a user's role | ✅ | ❌ | ❌ |
| **Roles** | | | | |
| `roles.view` | See the role/permission matrix page | ✅ | ❌ | ❌ |
| **Settings** (sub-project ①) | | | | |
| `settings.manage` | Edit site settings at `/admin/parametres` | ✅ | ❌ | ❌ |
| **Newsletter** (sub-project ④) | | | | |
| `newsletter.subscribers.view` | View the subscriber list | ✅ | ❌ | ❌ |
| `newsletter.campaigns.compose` | Create and edit newsletter drafts | ✅ | ❌ | ❌ |
| `newsletter.campaigns.send` | Send a finalized campaign | ✅ | ❌ | ❌ |
| **Admin access** | | | | |
| `admin.access` | Enter any `/admin/*` route | ✅ | ✅ | ❌ |

\* The `member` role holds `posts.create` unconditionally, but
`BlogPostPolicy::create` adds an extra check:
`setting('blog.public_creation', true)` must be true for non-admins.
The permission grant is documented in the Role Matrix UI with a footnote
referencing the setting.

### Role summary

| Role | Meaning |
|---|---|
| `admin` | Full access. Manages settings, users, roles, newsletter, moderation, everything. |
| `editor` | Trusted contributor. Can publish own articles without passing through moderation, accesses `/admin/` for a personal author dashboard, but has no authority over other users, moderation queues, or system settings. |
| `member` | Default role for newly registered users. Can create posts (gated by setting) and edit their own posts, nothing else. |

## 6. Data model changes

### 6.1 Migration `add_active_flag_to_users_table`

```php
Schema::table('users', function (Blueprint $table) {
    $table->boolean('is_active')->default(true)->after('password');
    $table->index('is_active', 'idx_users_is_active');
});
```

### 6.2 Migration `create_user_invitations_table`

```php
Schema::create('user_invitations', function (Blueprint $table) {
    $table->id();
    $table->string('email')->unique();
    $table->string('first_name', 100)->nullable();
    $table->string('last_name', 100)->nullable();
    $table->string('role', 50);
    $table->string('token', 64)->unique();
    $table->text('message')->nullable();           // personal message from the inviter
    $table->foreignId('invited_by')
          ->constrained('users')
          ->cascadeOnDelete();
    $table->timestamp('expires_at');
    $table->timestamp('accepted_at')->nullable();
    $table->timestamps();

    $table->index('expires_at', 'idx_invitations_expires');
});
```

Notes:
- **`email` is unique at the table level**: only one pending invitation per
  email. Re-inviting = update of the existing row (new token, refreshed
  `expires_at`).
- **`token` is 64 chars** (`Str::random(64)`): ~380 bits of entropy, impossible
  to brute-force.
- **`cascadeOnDelete` on `invited_by`**: if the inviting admin is hard-deleted
  (not soft-deactivated), pending invitations they created are deleted too.
  This is acceptable — the invitation no longer has an authority to track back
  to.
- **`accepted_at`**: kept after acceptance for audit. A separate column keeps
  the distinction with `expires_at` clean.

### 6.3 Model `App\Models\UserInvitation`

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class UserInvitation extends Model
{
    use LogsActivity;

    protected $fillable = [
        'email', 'first_name', 'last_name', 'role',
        'token', 'message', 'invited_by', 'expires_at', 'accepted_at',
    ];

    protected $casts = [
        'expires_at'  => 'datetime',
        'accepted_at' => 'datetime',
    ];

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isAccepted(): bool
    {
        return $this->accepted_at !== null;
    }

    public function isPending(): bool
    {
        return ! $this->isAccepted() && ! $this->isExpired();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['email', 'role', 'accepted_at'])
            ->logOnlyDirty()
            ->useLogName('invitations');
    }
}
```

### 6.4 Model `App\Models\User` — additions

Two things added to the existing `User` model:

```php
/**
 * Check whether a user is the last remaining active admin.
 * Used by the anti-lockout guard before role changes and deactivations.
 */
public static function isLastActiveAdmin(User $target): bool
{
    if (! $target->hasRole('admin') || ! $target->is_active) {
        return false;
    }

    return User::role('admin')
        ->where('is_active', true)
        ->where('id', '!=', $target->id)
        ->doesntExist();
}
```

And activity logging on role changes / deactivations — done by adding
the `LogsActivity` trait to `User` with:

```php
public function getActivitylogOptions(): LogOptions
{
    return LogOptions::defaults()
        ->logOnly(['email', 'is_active'])
        ->logOnlyDirty()
        ->useLogName('users');
}
```

Role changes are logged separately by the admin Livewire component (after
calling `syncRoles`, it calls `activity('users')->performedOn($user)->withProperties(...)->log('role_changed')`).

## 7. `RolePermissionSeeder` — idempotent source of truth

The existing `RoleSeeder` is **replaced** by a more complete
`RolePermissionSeeder` that:

1. Clears Spatie's permission cache before and after (critical — Spatie caches
   permission lookups in memory, stale cache = silent authorization bugs)
2. Creates or updates the 17 permissions via `firstOrCreate`
3. Creates the 3 roles and `syncPermissions` on each (replaces, never appends)
4. Migrates any legacy `model_has_roles` entries from `user` → `member` and
   `moderator` → `editor`
5. Deletes the now-orphaned legacy roles (`user`, `moderator`)

```php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->seedPermissions();
        $this->seedRolesWithPermissions();
        $this->migrateLegacyRoleNames();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function seedPermissions(): void
    {
        $permissions = [
            'posts.create', 'posts.publish.own',
            'posts.edit.own', 'posts.edit.any', 'posts.delete.any',
            'comments.moderate',
            'profiles.moderate',
            'users.view', 'users.invite', 'users.edit', 'users.assign-role',
            'roles.view',
            'settings.manage',
            'newsletter.subscribers.view',
            'newsletter.campaigns.compose',
            'newsletter.campaigns.send',
            'admin.access',
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }
    }

    private function seedRolesWithPermissions(): void
    {
        $definitions = [
            'admin' => Permission::pluck('name')->all(),
            'editor' => [
                'posts.create',
                'posts.publish.own',
                'posts.edit.own',
                'admin.access',
            ],
            'member' => [
                'posts.create',
                'posts.edit.own',
            ],
        ];

        foreach ($definitions as $roleName => $permissionNames) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($permissionNames);
        }
    }

    private function migrateLegacyRoleNames(): void
    {
        $legacyMap = [
            'user'      => 'member',
            'moderator' => 'editor',
        ];

        foreach ($legacyMap as $old => $new) {
            $oldRole = Role::where('name', $old)->first();
            $newRole = Role::where('name', $new)->first();

            if ($oldRole && $newRole) {
                DB::table('model_has_roles')
                    ->where('role_id', $oldRole->id)
                    ->update(['role_id' => $newRole->id]);

                $oldRole->delete();
            }
        }
    }
}
```

## 8. Code migration — every `hasRole('admin')` in the codebase

Exhaustive map of existing references that must change:

| File | Current | Replacement |
|---|---|---|
| `app/Http/Middleware/EnsureUserIsAdmin.php` | `! $user->hasRole('admin')` | Rename file to `EnsureCanAccessAdmin.php`, check `! $user->can('admin.access')`. Error message: `"Accès réservé au personnel autorisé."` |
| `bootstrap/app.php` | `'admin' => EnsureUserIsAdmin::class` | `'admin' => EnsureCanAccessAdmin::class` — alias key stays the same so `routes/web.php` is untouched |
| `app/Policies/BlogPostPolicy::create` | `return true` | `return $user->can('posts.create')` |
| `app/Policies/BlogPostPolicy::update` | `$user->id === $post->user_id \|\| $user->hasRole('admin')` | `$user->id === $post->user_id ? $user->can('posts.edit.own') : $user->can('posts.edit.any')` |
| `app/Policies/BlogPostPolicy::delete` | `$user->id === $post->user_id \|\| $user->hasRole('admin')` | `$user->id === $post->user_id \|\| $user->can('posts.delete.any')` |
| `app/Policies/BlogPostPolicy::publish` | *does not exist* | **New method**: `return $user->id === $post->user_id ? $user->can('posts.publish.own') : $user->can('posts.edit.any')` |
| `app/Policies/ProfilePolicy::delete` | `$user->id === $profile->user_id \|\| $user->hasRole('admin')` | `$user->id === $profile->user_id \|\| $user->can('profiles.moderate')` |
| `app/Livewire/Admin/Dashboard.php` | implicit admin layout | Add `$this->authorize('admin.access')` at the top of `render()` |
| `app/Livewire/Admin/ModerateProfiles.php::approve` | implicit | `$this->authorize('profiles.moderate')` at entry |
| `app/Livewire/Admin/ModerateProfiles.php::confirmReject` | implicit | `$this->authorize('profiles.moderate')` at entry |
| `app/Livewire/Admin/ManagePosts.php::publish/unpublish/delete` | implicit | `$this->authorize('posts.edit.any')` at entry |
| `app/Livewire/Admin/ModerateComments.php::approve/delete` | implicit | `$this->authorize('comments.moderate')` at entry |
| `app/Livewire/Auth/Register.php::register` | `$user->assignRole('user')` | `$user->assignRole('member')` |
| `database/seeders/ProfileSeeder.php` | `$user->assignRole('user')` | `$user->assignRole('member')` |
| `database/seeders/AdminSeeder.php` | `$admin->assignRole('admin')` | unchanged |
| `database/seeders/DatabaseSeeder.php` | `RoleSeeder::class` | `RolePermissionSeeder::class` |
| `tests/Pest.php` | `$this->seed(RoleSeeder::class)` | `$this->seed(RolePermissionSeeder::class)` |
| All test files using `assignRole('user')` | find+replace to `'member'` | ~15 files — grep before commit |
| `tests/Feature/Admin/ModerationTest.php` `assertForbidden()` tests | use `'user'` / `'member'` interchangeably | Update to `'member'` |

**Settings plan (① — already drafted)** — the plan file
`docs/superpowers/plans/2026-04-11-site-settings.md` will be patched
with `hasRole('admin')` → `can('settings.manage')` in:

- `MaintenanceModeCheck::handle` — the admin bypass check
- `BlogPostPolicy::create` — the settings-plan intended the same check
- `CreatePost::save` and `EditPost::save` — the force-draft bypass

This is a single targeted patch after ② ships; it's flagged in the ① plan
header as a pre-implementation task.

## 9. Deactivation semantics

When `is_active = false`:

| Effect | Where |
|---|---|
| Login refused | `app/Livewire/Auth/Login::login()` — after `Auth::attempt` succeeds, check `Auth::user()->is_active`; if false → `Auth::logout()` + error `"Votre compte a été désactivé. Contactez un administrateur."` |
| Public profile 404 | `app/Http/Controllers/ProfileController::show` — `abort_if(! $profile->user->is_active, 404)` |
| Directory hidden | `app/Livewire/Directory/SearchDirectory` — add `whereHas('user', fn ($q) => $q->where('is_active', true))` to the main query |
| Articles still visible | **Yes** — an article, once published, stays part of the site's corpus. Deactivation is not deletion |
| Comments still visible | **Yes** — same reasoning |
| Sessions forcibly killed | **No** — out of scope. Next time the user refreshes the admin will be logged out by the login check if they sign back in |

## 10. Anti-lockout enforcement

The rule: **at least one user with role `admin` AND `is_active = true` must
exist at all times.**

Every action that could violate this rule checks `User::isLastActiveAdmin` on
the target user and aborts with a French error message if it would leave zero
active admins. Guarded actions:

- `EditUser::changeRole(string $newRole)` — if the current user is the last
  active admin and `$newRole !== 'admin'`, abort.
- `EditUser::deactivate()` — if the current user is the last active admin,
  abort.
- `EditUser::delete()` — not exposed in the UI (no hard delete in scope), but
  if added later, same check.

The UI also **hides** the "Deactivate" button and **disables** the role
dropdown on the row of the last active admin so the user never triggers a
server-side rejection.

## 11. Admin UI — routes & nav

Added to the existing `admin` route group in `routes/web.php`:

```php
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // … existing routes from sub-project ① and the current admin …

    Route::get('/utilisateurs',                  AdminUsers::class)->name('users');
    Route::get('/utilisateurs/inviter',          AdminInviteUser::class)->name('users.invite');
    Route::get('/utilisateurs/{user}/editer',    AdminEditUser::class)->name('users.edit');
    Route::get('/roles',                         AdminRoleMatrix::class)->name('roles');
});
```

Plus the **public** acceptance route outside the admin group:

```php
Route::get('/invitation/{token}', AcceptInvitation::class)->name('invitation.accept');
```

Update `resources/views/layouts/admin.blade.php` `$navItems`:

```php
$navItems = [
    ['route' => 'admin.dashboard', 'label' => 'Tableau de bord', 'icon' => 'home'],
    ['route' => 'admin.profiles',  'label' => 'Profils',         'icon' => 'users'],
    ['route' => 'admin.posts',     'label' => 'Articles',        'icon' => 'document'],
    ['route' => 'admin.comments',  'label' => 'Commentaires',    'icon' => 'chat'],
    ['route' => 'admin.users',     'label' => 'Utilisateurs',    'icon' => 'user-plus'],
    ['route' => 'admin.roles',     'label' => 'Rôles',           'icon' => 'shield'],
    ['route' => 'admin.settings',  'label' => 'Paramètres',      'icon' => 'cog'],
];
```

The `user-plus` and `shield` SVG icons are added to the `@if ($item['icon'] === …)` chain (heroicons-o).

## 12. Page `/admin/utilisateurs` — `App\Livewire\Admin\Users`

Permission: `can('users.view')` at render entry.

Two tabs on top of the page, with active-tab state held in a `#[Url] $tab`
Livewire property: `'active'` (default) shows registered users;
`'pending'` shows pending invitations.

### Active-users tab layout

Filters bar:
- `wire:model.live.debounce.300ms $search` — text input searching
  `LOWER(users.email) LIKE '%…%' OR LOWER(users.name) LIKE '%…%'` (the `name`
  column is the Postgres generated column concatenating `first_name` and
  `last_name`).
- `$roleFilter` dropdown: All / admin / editor / member.
- `$statusFilter` dropdown: All / Active / Inactive / Email not verified.

Columns (see §8 C of Part 1 brainstorming):

| Avatar | Name | Email | Role (badge) | Status | Profile | Created | Actions |

- Role badge colors: `admin` → `bg-red-50 text-red-700 border-red-200`;
  `editor` → `bg-blue-50 text-blue-700 border-blue-200`;
  `member` → `bg-gray-100 text-gray-600 border-gray-200`.
- Status icon: green check (`Actif`), gray circle (`Inactif`), orange exclamation (`Email non vérifié`).
- Profile column: `<a href="/profils/{slug}">Voir</a>` if a public profile exists, `—` otherwise.
- Actions: single "Éditer" link → `/admin/utilisateurs/{id}/editer`.

Pagination via `WithPagination`, 20 per page.

### Pending-invitations tab layout

Same table structure, but columns adapted:

| Email | Full name (from invitation) | Role | Invited by | Expires in | Actions |

Actions on each row:
- `[Relancer]` — calls `resend(int $invitationId)` which regenerates the
  token, resets `expires_at` to `now()+7d`, and re-queues the email
- `[Annuler]` — deletes the invitation row

Expired invitations have an orange `Expirée` badge and only a `[Relancer]`
action (no Cancel — let the admin resend instead of leaving expired rows
cluttering).

## 13. Page `/admin/utilisateurs/inviter` — `App\Livewire\Admin\InviteUser`

Permission: `can('users.invite')`.

Form fields (5):

| Field | Type | Required | Validation |
|---|---|---|---|
| `email` | `email` | ✅ | `required`, `email`, **unique on `users.email`** AND **unique on `user_invitations.email` where `accepted_at IS NULL`** |
| `first_name` | `text` | ❌ | `nullable`, `max:100` |
| `last_name` | `text` | ❌ | `nullable`, `max:100` |
| `role` | `select` | ✅ | `required`, `in:admin,editor,member` |
| `message` | `textarea` | ❌ | `nullable`, `max:500` — personal note included in the invitation email |

Custom validation error messages (French, via `lang/fr/validation.php` custom
section):
- Email already in users: `"Ce membre fait déjà partie de la plateforme."`
- Email has pending invitation: `"Une invitation est déjà en attente pour cet
  email. Vous pouvez la relancer depuis la liste."`

### `send()` method flow

1. `$this->authorize('users.invite')`
2. `$validated = $this->validate()`
3. Create `UserInvitation`:
    - `email`, `first_name`, `last_name`, `role`, `message` from form
    - `token = Str::random(64)`
    - `invited_by = Auth::id()`
    - `expires_at = now()->addDays(7)`
4. `Mail::to($validated['email'])->queue(new InvitationMail($invitation))`
5. Flash success: `"Invitation envoyée à {$email}."`
6. `$this->redirect(route('admin.users', ['tab' => 'pending']), navigate: true)`

## 14. Page `/admin/utilisateurs/{user}/editer` — `App\Livewire\Admin\EditUser`

Permission: `can('users.edit')` at entry; specific actions check their
own permission.

Three editable sections + a read-only history section.

### Section 14.1 — Basic info

Fields: `first_name`, `last_name`, `email`. Bound to public Livewire props.
`save()` method:

```php
public function save(): void
{
    $this->authorize('users.edit');
    $this->validate([
        'first_name' => ['required', 'string', 'max:100'],
        'last_name'  => ['required', 'string', 'max:100'],
        'email'      => ['required', 'email', 'unique:users,email,' . $this->user->id],
    ]);
    $this->user->update($this->only(['first_name', 'last_name', 'email']));
    session()->flash('success', 'Informations mises à jour.');
}
```

### Section 14.2 — Role

Dropdown with `admin / editor / member`. If
`User::isLastActiveAdmin($this->user)` returns `true`, the dropdown is
disabled with a tooltip `"Vous ne pouvez pas rétrograder le dernier
administrateur actif."`

`changeRole(string $newRole)`:

```php
public function changeRole(string $newRole): void
{
    $this->authorize('users.assign-role');

    if ($this->user->hasRole('admin')
        && $newRole !== 'admin'
        && User::isLastActiveAdmin($this->user)) {
        session()->flash('error', 'Impossible : au moins un administrateur actif doit rester.');
        return;
    }

    $oldRole = $this->user->roles->first()?->name;
    $this->user->syncRoles([$newRole]);

    activity('users')
        ->performedOn($this->user)
        ->causedBy(Auth::user())
        ->withProperties(['old' => $oldRole, 'new' => $newRole])
        ->log('role_changed');

    session()->flash('success', 'Rôle mis à jour.');
}
```

### Section 14.3 — Status

A single "Active" toggle (labeled `Actif / Inactif`) with a confirmation
dialog. `toggleActive()`:

```php
public function toggleActive(): void
{
    $this->authorize('users.edit');

    if ($this->user->is_active && User::isLastActiveAdmin($this->user)) {
        session()->flash('error', 'Impossible : au moins un administrateur actif doit rester.');
        return;
    }

    $this->user->update(['is_active' => ! $this->user->is_active]);
    session()->flash('success', $this->user->is_active ? 'Compte réactivé.' : 'Compte désactivé.');
}
```

### Section 14.4 — Activity history (read-only)

Queries the last 20 activity log entries targeting this user:

```php
Activity::query()
    ->where('subject_type', User::class)
    ->where('subject_id', $this->user->id)
    ->with('causer')
    ->latest()
    ->limit(20)
    ->get();
```

Rendered as a list with causer name, event description (`role_changed`,
`updated`, `created`), property diff when available, and relative timestamp.

## 15. Page `/admin/roles` — `App\Livewire\Admin\RoleMatrix` (read-only)

Permission: `can('roles.view')`.

Purpose: documentation. Admin opens this page to **understand** the
permission map. No save button, no edit affordance.

### Render logic

```php
public function render()
{
    $permissions = Permission::query()
        ->orderBy('name')
        ->get()
        ->groupBy(fn ($p) => explode('.', $p->name)[0]); // 'posts', 'users', etc.

    $roles = Role::with('permissions')->orderByRaw("
        CASE name
            WHEN 'admin' THEN 1
            WHEN 'editor' THEN 2
            WHEN 'member' THEN 3
            ELSE 4
        END
    ")->get();

    return view('livewire.admin.role-matrix', compact('permissions', 'roles'));
}
```

### Layout

Grouped table with one section per permission domain (`posts`, `comments`,
`profiles`, `users`, `roles`, `settings`, `newsletter`, `admin`). Each row
is a permission, each column is a role. Cells show `✓` if the role has the
permission, `—` otherwise.

A disclaimer appears at the top:
> "Les rôles et leurs permissions sont définis en code. Pour ajouter un
> nouveau rôle ou modifier les permissions d'un rôle existant, contactez
> un développeur."

The `posts.create` / `member` cell has a footnote marker referencing
`setting('blog.public_creation')`.

## 16. Public page `/invitation/{token}` — `App\Livewire\Auth\AcceptInvitation`

No auth required. Layout: `layouts.guest` (same as login/register).

### `mount(string $token)`

1. `$this->invitation = UserInvitation::where('token', $token)->first()`
2. `abort_if(! $this->invitation, 404)`
3. `if ($this->invitation->isAccepted())` → redirect `/connexion` with flash
   `"Cette invitation a déjà été utilisée."`
4. `if ($this->invitation->isExpired())` → redirect `/connexion` with flash
   `"Cette invitation a expiré. Demandez à l'administrateur de vous en
   envoyer une nouvelle."`
5. Pre-fill `$this->first_name = $this->invitation->first_name ?? ''`,
   same for `last_name`.

### Form fields

- `first_name` (editable, required)
- `last_name` (editable, required)
- `password` (required, `min:8`, `confirmed`)
- `password_confirmation`
- Read-only display: the email and role assigned by the admin (as muted text
  above the form)

### `accept()`

```php
public function accept(): void
{
    $this->validate([
        'first_name' => ['required', 'string', 'max:100'],
        'last_name'  => ['required', 'string', 'max:100'],
        'password'   => ['required', 'min:8', 'confirmed'],
    ]);

    $user = DB::transaction(function () {
        $user = User::create([
            'first_name'        => $this->first_name,
            'last_name'         => $this->last_name,
            'email'             => $this->invitation->email,
            'password'          => bcrypt($this->password),
            'email_verified_at' => now(),
            'is_active'         => true,
        ]);

        $user->assignRole($this->invitation->role);

        $this->invitation->update(['accepted_at' => now()]);

        return $user;
    });

    Auth::login($user);
    session()->regenerate();

    $this->redirect(route('profile.create'), navigate: true);
}
```

Bienvenue flash on `/profil/creer`: `"Bienvenue sur Bassila Émergence !
Complétez votre profil pour rejoindre l'annuaire."` (set via
`session()->flash('success', …)` just before the redirect).

## 17. Invitation email — `App\Mail\InvitationMail`

```php
class InvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public UserInvitation $invitation) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Vous êtes invité à rejoindre Bassila Émergence",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.invitation',
            with: [
                'invitation'    => $this->invitation,
                'invitedByName' => $this->invitation->invitedBy->name,
                'acceptUrl'     => route('invitation.accept', ['token' => $this->invitation->token]),
            ],
        );
    }
}
```

### Template `resources/views/mail/invitation.blade.php`

Reuses the structure of the 4 existing mail templates (white header with logo
+ blue accent bottom border, dark navy footer). Content sketch:

> **Vous êtes invité à rejoindre Bassila Émergence**
>
> Bonjour,
>
> **{{ $invitedByName }}** vous invite à rejoindre Bassila Émergence, la plateforme de networking des Bassilois à travers le monde.
>
> Vous avez été invité avec le rôle de **{{ $invitation->role }}**.
>
> @if ($invitation->message)
> > *« {{ $invitation->message }} »*
> @endif
>
> [Accepter l'invitation] → links to `$acceptUrl`
>
> Cette invitation est valable 7 jours. Si vous ne souhaitez pas rejoindre la plateforme, ignorez simplement cet email.

## 18. Login deactivation check

Targeted modification to `app/Livewire/Auth/Login::login()`:

```php
public function login(): void
{
    $this->validate();

    if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
        $this->addError('email', __('auth.failed'));
        return;
    }

    if (! Auth::user()->is_active) {
        Auth::logout();
        $this->addError('email', 'Votre compte a été désactivé. Contactez un administrateur.');
        return;
    }

    session()->regenerate();
    $this->redirect(route('directory.index'), navigate: true);
}
```

This is a single 5-line addition that runs after successful credential
verification but before session regeneration, ensuring a deactivated user is
never actually logged in.

## 19. Testing strategy

23 tests total, grouped by layer.

### Unit (6)

1. `UserTest::is_last_active_admin_returns_true_for_sole_admin`
2. `UserTest::is_last_active_admin_returns_false_when_two_admins_active`
3. `UserTest::is_last_active_admin_returns_false_for_non_admin`
4. `UserInvitationTest::is_expired_true_when_expires_at_in_past`
5. `RolePermissionSeederTest::creates_3_roles_and_17_permissions`
6. `RolePermissionSeederTest::migrates_legacy_user_role_to_member_on_rerun`

### Feature — invitation flow (6)

7. `InvitationTest::admin_can_invite_a_new_email`
8. `InvitationTest::invite_fails_for_existing_user_email`
9. `InvitationTest::invite_fails_for_pending_invitation_email`
10. `InvitationTest::accept_page_404s_on_unknown_token`
11. `InvitationTest::accept_page_redirects_on_expired_token`
12. `InvitationTest::accept_creates_user_with_correct_role_and_auto_logs_in`

### Feature — user management (5)

13. `UserManagementTest::admin_can_deactivate_a_user_and_user_cannot_login`
14. `UserManagementTest::cannot_deactivate_the_last_active_admin`
15. `UserManagementTest::cannot_demote_the_last_active_admin`
16. `UserManagementTest::admin_can_promote_a_member_to_editor`
17. `UserManagementTest::user_list_filters_by_role_and_status`

### Feature — authorization migration (4)

18. `RbacTest::editor_can_publish_own_article_without_moderation`
19. `RbacTest::member_cannot_publish_when_moderation_required`
20. `RbacTest::editor_cannot_edit_another_users_article`
21. `RbacTest::admin_bypasses_every_permission_via_admin_role`

### Feature — role matrix page (2)

22. `RoleMatrixTest::renders_all_permissions_grouped_by_domain`
23. `RoleMatrixTest::forbids_users_without_roles_view`

## 20. Acceptance criteria

- [ ] `RolePermissionSeeder` creates exactly 17 permissions and 3 roles
      (`admin`, `editor`, `member`). No legacy `user` / `moderator` rows remain.
- [ ] Every `hasRole('admin')` call from the mapped files is removed. `grep -r "hasRole\\('admin'\\)"` returns zero matches in `app/` and `tests/`.
- [ ] An admin at `/admin/utilisateurs/inviter` can send an invitation;
      the invited email receives a French email with a working 7-day link;
      clicking the link and submitting the form creates a `users` row with
      the correct role and auto-logs in.
- [ ] An admin editing the last active admin cannot demote nor deactivate
      them; both UI and server-side checks block the action with a French
      error message.
- [ ] A deactivated user cannot log in even with valid credentials; their
      public profile returns 404; they are hidden from the `/annuaire`.
- [ ] The `/admin/roles` page renders the 3 roles × 17 permissions matrix
      with correct checkmarks and is forbidden to non-admins.
- [ ] All 23 tests pass.
- [ ] The existing test suite still passes after the role rename
      `user` → `member` is propagated.

## 21. Open questions / cross-project impact

- **① Settings plan patch** — after ② ships, the ① Settings plan must be
  patched to replace `hasRole('admin')` with `can('settings.manage')` in the
  maintenance middleware, `BlogPostPolicy::create`, and the force-draft
  bypass in `CreatePost::save` / `EditPost::save`. This is a flagged
  pre-implementation task in the ① plan header.

- **③ Blog editor** — will consume `posts.create`, `posts.publish.own`,
  `posts.edit.own`, `posts.edit.any`, `posts.delete.any`. Note that
  publishing someone else's article goes through `posts.edit.any` — there
  is no separate `posts.publish.any` permission because editing implies
  control over the status transition. The spec will reference these
  permissions as already-seeded contracts.

- **④ Newsletter** — will consume `newsletter.subscribers.view`,
  `newsletter.campaigns.compose`, `newsletter.campaigns.send`. Same contract.

- **Email change re-verification** (noted in Non-Goals) — if deferred work
  is prioritized, it becomes an additional sub-project or a patch to this
  one. Adding `email_verified_at = null` on email edit + a re-verification
  mail is ~50 LoC.
