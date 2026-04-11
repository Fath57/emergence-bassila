# Site Settings + Feature Flags — Design Spec

**Project:** Bassila Émergence — Admin autonomy roadmap, sub-project ① (foundation)
**Date:** 2026-04-11
**Status:** Draft — awaiting user review

---

## 1. Context

The admin panel currently has no way to change site-wide behavior from the UI.
Every behavioral flag is either hard-coded in a Policy (`BlogPostPolicy::create`
returns `true` unconditionally), hard-coded in a Livewire component (comments
always require moderation), or non-existent (no maintenance mode, no way to
pause new registrations).

This project introduces a **key-value settings system** backed by a database
table, rendered as an admin page that auto-generates its form from the settings
schema. All settings are audited via `spatie/laravel-activitylog` (already
installed, just needs its migration).

This is the **first** of four sub-projects in the admin autonomy roadmap. The
remaining three (② RBAC + user management, ③ Rich blog editor, ④ Newsletter
management) depend on this foundation: they all need feature flags to be
toggleable from the admin without a code deploy.

## 2. Goals

- Allow an admin to toggle 7 site-wide behaviors from `/admin/parametres` with
  zero code changes per toggle.
- Support two value types (`bool`, `string`) at MVP; extensible to `int`, `text`,
  `json` with only an additional branch in the render loop (no schema change).
- Full audit trail: who changed what, when, from what value to what value.
- No performance regression: one query per HTTP request to load all settings,
  cached indefinitely and invalidated automatically on update.
- Idempotent seeding: `php artisan db:seed --class=SettingSeeder` re-creates
  missing settings without overwriting admin-edited values.

## 3. Non-Goals

Explicitly **out of scope** for this sub-project:

- Granular permission gating (e.g. `settings.manage` permission). Access control
  is limited to `hasRole('admin')` via the existing `admin` middleware.
  Fine-grained permissions are sub-project ② RBAC.
- In-UI creation/edition of new settings (label, description, group). New
  settings are added via seeder + code. The admin UI only edits *values*.
- Import/export of settings as JSON.
- Multi-site / multi-tenant settings partitioning.
- Settings preview / dry-run before save.

## 4. Settings inventory (MVP)

Seven settings are seeded at MVP, grouped into three categories.

| Key | Type | Default | Group | Label | Description |
|---|---|---|---|---|---|
| `site.registration_open` | bool | `true` | `site` | Inscriptions ouvertes | Si désactivé, /inscription redirige vers /connexion avec un message. |
| `site.maintenance_mode` | bool | `false` | `site` | Mode maintenance | Bloque tout le site public. Les admins gardent l'accès. |
| `site.maintenance_message` | string | *"Le site est temporairement indisponible pour maintenance. Nous revenons très vite."* | `site` | Message de maintenance | Affiché sur la page de blocage. |
| `blog.public_creation` | bool | `true` | `blog` | Création d'articles par les membres | Si désactivé, seuls les admins peuvent publier. |
| `blog.require_moderation` | bool | `false` | `blog` | Modération avant publication | Les articles créés par des membres restent en brouillon tant qu'un admin ne les a pas publiés. |
| `comments.enabled` | bool | `true` | `comments` | Commentaires activés | Accepter de nouveaux commentaires sur les articles. |
| `comments.require_moderation` | bool | `true` | `comments` | Modération préalable des commentaires | Les commentaires restent invisibles tant qu'un admin ne les a pas approuvés. |

## 5. Architecture overview

```
┌────────────────────────────────────────────────────────────────────┐
│                       Admin UI (Livewire)                         │
│        /admin/parametres  →  App\Livewire\Admin\Settings          │
│  • Form generated from DB, grouped by `group`                      │
│  • History section at bottom reads from activity_log               │
└─────────────────────┬──────────────────────────────────────────────┘
                      │ read/write via Setting model
                      ▼
┌────────────────────────────────────────────────────────────────────┐
│                   App\Models\Setting (Eloquent)                    │
│  • Trait LogsActivity (Spatie) → activity_log                      │
│  • saved/deleted event → cache invalidation                        │
└─────────────────────┬──────────────────────────────────────────────┘
                      │
                      ▼
┌────────────────────────────────────────────────────────────────────┐
│  Global helper: setting('blog.public_creation', true)             │
│  • cache()->rememberForever('settings.all', …)                     │
│  • Cast applied via `type` column                                  │
│  • Fallback to the second argument if key missing                  │
└─────────────────────┬──────────────────────────────────────────────┘
                      │
       ┌──────────────┼──────────────┬───────────────┐
       ▼              ▼              ▼               ▼
  BlogPostPolicy  RegisterRoute  MaintenanceMW   CommentForm
    (create)       (guard mw)     (web group)    (submit gate)
```

Single source of truth: one table, one helper, one cache key. Every consumer
reads through `setting()`; no direct DB access from policies/views/components.

## 6. Data model

### 6.1 Table `settings`

```php
Schema::create('settings', function (Blueprint $table) {
    $table->id();
    $table->string('key', 100)->unique();      // 'blog.public_creation'
    $table->text('value')->nullable();          // stored as string, cast by `type`
    $table->string('type', 20)->default('bool'); // 'bool' | 'string' | future: 'int'|'text'|'json'
    $table->string('group', 50)->index();       // 'blog' | 'site' | 'comments'
    $table->string('label', 255);
    $table->text('description')->nullable();
    $table->unsignedInteger('sort_order')->default(0);
    $table->foreignId('updated_by')
          ->nullable()
          ->constrained('users')
          ->nullOnDelete();
    $table->timestamps();
});
```

Rationale for `varchar(20)` on `type` rather than an enum: adding a future type
becomes a single branch in the render loop rather than a DB migration.

### 6.2 Activitylog migration

`spatie/laravel-activitylog` v4.12 is already installed via composer but its
migration has never been run.

```bash
php artisan vendor:publish --provider="Spatie\\Activitylog\\ActivitylogServiceProvider" --tag="activitylog-migrations"
php artisan migrate
```

Creates the `activity_log` table with `log_name`, `description`, `subject_type`,
`subject_id`, `causer_type`, `causer_id`, `properties` (JSON), `created_at`.

### 6.3 Model `App\Models\Setting`

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Setting extends Model
{
    use LogsActivity;

    protected $fillable = [
        'key', 'value', 'type', 'group', 'label', 'description', 'sort_order', 'updated_by',
    ];

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getCastedValueAttribute(): mixed
    {
        return match ($this->type) {
            'bool'   => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'string' => (string) $this->value,
            default  => $this->value,
        };
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['value'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn (string $event) => "setting.{$event}")
            ->useLogName('settings');
    }

    protected static function booted(): void
    {
        static::saved(fn () => cache()->forget('settings.all'));
        static::deleted(fn () => cache()->forget('settings.all'));
    }
}
```

### 6.4 Seeder `SettingSeeder`

Idempotent with value preservation: on re-run, **existing rows keep their
`value`** (so admin-edited settings are never reverted by reseeding) while
**metadata** (`label`, `description`, `group`, `sort_order`, `type`) is always
brought back into sync with the seeder definition.

```php
namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $seed = [
            [
                'key'         => 'site.registration_open',
                'type'        => 'bool',
                'value'       => '1',
                'group'       => 'site',
                'sort_order'  => 1,
                'label'       => 'Inscriptions ouvertes',
                'description' => "Si désactivé, /inscription redirige vers /connexion avec un message.",
            ],
            [
                'key'         => 'site.maintenance_mode',
                'type'        => 'bool',
                'value'       => '0',
                'group'       => 'site',
                'sort_order'  => 2,
                'label'       => 'Mode maintenance',
                'description' => "Bloque tout le site public. Les admins gardent l'accès.",
            ],
            [
                'key'         => 'site.maintenance_message',
                'type'        => 'string',
                'value'       => 'Le site est temporairement indisponible pour maintenance. Nous revenons très vite.',
                'group'       => 'site',
                'sort_order'  => 3,
                'label'       => 'Message de maintenance',
                'description' => "Affiché sur la page de blocage.",
            ],
            [
                'key'         => 'blog.public_creation',
                'type'        => 'bool',
                'value'       => '1',
                'group'       => 'blog',
                'sort_order'  => 1,
                'label'       => "Création d'articles par les membres",
                'description' => "Si désactivé, seuls les admins peuvent publier.",
            ],
            [
                'key'         => 'blog.require_moderation',
                'type'        => 'bool',
                'value'       => '0',
                'group'       => 'blog',
                'sort_order'  => 2,
                'label'       => 'Modération avant publication',
                'description' => "Les articles créés par des membres restent en brouillon tant qu'un admin n'a pas publié.",
            ],
            [
                'key'         => 'comments.enabled',
                'type'        => 'bool',
                'value'       => '1',
                'group'       => 'comments',
                'sort_order'  => 1,
                'label'       => 'Commentaires activés',
                'description' => "Accepter de nouveaux commentaires sur les articles.",
            ],
            [
                'key'         => 'comments.require_moderation',
                'type'        => 'bool',
                'value'       => '1',
                'group'       => 'comments',
                'sort_order'  => 2,
                'label'       => 'Modération préalable des commentaires',
                'description' => "Les commentaires restent invisibles tant qu'un admin ne les a pas approuvés.",
            ],
        ];

        foreach ($seed as $row) {
            $setting = Setting::firstOrNew(['key' => $row['key']]);

            if (! $setting->exists) {
                // New row — persist with the seeded default value
                $setting->fill($row)->save();
                continue;
            }

            // Existing row — sync metadata but preserve the admin-edited value
            $setting->fill([
                'type'        => $row['type'],
                'group'       => $row['group'],
                'label'       => $row['label'],
                'description' => $row['description'],
                'sort_order'  => $row['sort_order'],
            ])->save();
        }
    }
}
```

The two-branch pattern is deliberate and testable: see
`SettingSeederTest::it_preserves_existing_values_on_rerun` in section 11.

## 7. Helper API

### 7.1 Registration

```json
// composer.json (autoload section)
"autoload": {
    "psr-4": { "App\\": "app/" },
    "files": ["app/helpers.php"]
}
```

Then `composer dump-autoload`.

### 7.2 Implementation

```php
// app/helpers.php

use App\Models\Setting;

if (! function_exists('setting')) {
    function setting(string $key, mixed $default = null): mixed
    {
        $all = cache()->rememberForever('settings.all', function () {
            return Setting::all()->keyBy('key');
        });

        $setting = $all->get($key);

        return $setting?->casted_value ?? $default;
    }
}
```

### 7.3 Semantics

- **First call per HTTP request**: issues one `SELECT * FROM settings` query,
  stores the collection in cache under `settings.all`.
- **Subsequent calls**: zero DB hits, zero cache backend hits within the same
  request (Laravel's cache stores have in-memory lookup for repeated keys).
- **After save/delete**: the model's `booted()` event forgets the cache key.
  Next call rebuilds.
- **Missing key**: returns the `$default` passed by the caller. The site never
  crashes if a setting is deleted manually from SQL.
- **Cast**: applied via `casted_value` accessor based on the `type` column.

### 7.4 Usage conventions

All consumers **must** pass an explicit default matching the "safe" behavior:

```php
setting('blog.public_creation', true)          // default-open
setting('site.maintenance_mode', false)        // default-off
setting('site.maintenance_message', 'Site…')   // default-message
```

Never call `setting()` without a default. This policy ensures the code remains
resilient to missing rows.

## 8. Runtime effects per setting

Each setting maps to exactly one runtime modification of existing behavior.

### 8.1 `site.registration_open`

**Effect when `false`**: the `/inscription` route redirects to `/connexion` with
a flash message.

**Implementation**: new lightweight middleware `CheckRegistrationOpen` applied
to the existing `guest` group containing the `register` route, OR an inline
check in the route closure. Preferred: middleware, to keep the route definition
clean.

**Nav change**: `partials/nav.blade.php` wraps the "Créer un profil" CTA in
`@if (setting('site.registration_open', true))`.

### 8.2 `site.maintenance_mode`

**Effect when `true`**: all public routes return a 503 with
`maintenance.blade.php`.

**Bypass rules**:
1. Users with role `admin` always pass through.
2. The `/connexion` route is always accessible (so the admin can sign in).
3. All `/admin/*` routes are always accessible (auth still enforced).
4. Livewire internal routes (`/livewire/*`) pass through (so the login form and
   the admin pages remain interactive).
5. Vite asset routes (`/build/*`) pass through.

**Implementation**: middleware `App\Http\Middleware\MaintenanceModeCheck`
registered via `$middleware->appendToGroup('web', …)` in `bootstrap/app.php`.

```php
class MaintenanceModeCheck
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! setting('site.maintenance_mode', false)) {
            return $next($request);
        }

        if ($request->user()?->hasRole('admin')) {
            return $next($request);
        }

        $path = $request->path();
        $allowlist = ['connexion'];
        $prefixAllowlist = ['admin', 'livewire', 'build'];

        if (in_array($path, $allowlist, true)) {
            return $next($request);
        }
        foreach ($prefixAllowlist as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return $next($request);
            }
        }

        return response()->view('maintenance', [
            'message' => setting('site.maintenance_message', 'Site temporairement indisponible.'),
        ], 503);
    }
}
```

### 8.3 `site.maintenance_message`

**Effect**: read by `maintenance.blade.php` when rendering the 503 page. No
other consumer.

**View** `resources/views/maintenance.blade.php`: matches the guest layout
aesthetic — navy left panel with logo, white right panel showing the message
centered, no navigation links (deliberately a dead end). ~40 lines of Blade.

### 8.4 `blog.public_creation`

**Effect when `false`**: non-admin users get 403 on `/blog/rediger` and the
"Rédiger un article" link disappears from the nav.

**Implementation**: `app/Policies/BlogPostPolicy.php::create()` becomes:

```php
public function create(User $user): bool
{
    if ($user->hasRole('admin')) {
        return true;
    }
    return setting('blog.public_creation', true);
}
```

The `CreatePost` Livewire component already calls `$this->authorize('create',
BlogPost::class)` in `save()`, so the policy change propagates automatically.
The route also needs a `->middleware('can:create,App\Models\BlogPost')` binding
so unauthorized users hit 403 at route entry rather than at form submit.

### 8.5 `blog.require_moderation`

**Effect when `true`**: non-admin users can save articles but any `status` they
pick is coerced to `draft` at save time.

**Implementation**: in `app/Livewire/Blog/CreatePost.php::save()` and
`EditPost::save()`:

```php
if ($this->status === 'published'
    && setting('blog.require_moderation', false)
    && ! Auth::user()->hasRole('admin')) {
    $this->status = 'draft';
    session()->flash('info', 'Votre article sera visible après validation par un administrateur.');
}
```

### 8.6 `comments.enabled`

**Effect when `false`**:
- `CommentForm::submit()` returns early with a validation error.
- `resources/views/blog/show.blade.php` renders a disabled placeholder instead
  of the form: `<div class="bg-gray-50 border border-gray-200 p-4 text-sm text-gray-500">Les commentaires sont actuellement désactivés.</div>`
- Existing comments on the post remain visible (this setting is about *new*
  comments only).

### 8.7 `comments.require_moderation`

**Effect when `false`**: new comments are saved with `moderated_at = now()`
directly, skipping the moderation queue.

**Implementation**: in `app/Livewire/Blog/CommentForm.php::submit()`:

```php
BlogComment::create([
    ...,
    'moderated_at' => setting('comments.require_moderation', true) ? null : now(),
]);
```

## 9. Admin UI

### 9.1 Route

Added to the existing admin group in `routes/web.php`:

```php
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // … existing routes …
    Route::get('/parametres', AdminSettings::class)->name('settings');
});
```

And `admin.settings` is appended to the `$navItems` array in
`resources/views/layouts/admin.blade.php`.

### 9.2 Component `App\Livewire\Admin\Settings`

```php
namespace App\Livewire\Admin;

use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Spatie\Activitylog\Models\Activity;

class Settings extends Component
{
    /** @var array<string, mixed> */
    public array $values = [];

    public function mount(): void
    {
        $this->values = Setting::query()
            ->get()
            ->mapWithKeys(fn (Setting $s) => [$s->key => $s->casted_value])
            ->all();
    }

    public function save(): void
    {
        foreach ($this->values as $key => $value) {
            $setting = Setting::where('key', $key)->first();
            if (! $setting) continue;

            $serialized = match ($setting->type) {
                'bool'  => $value ? '1' : '0',
                default => (string) $value,
            };

            if ($setting->value !== $serialized) {
                $setting->update([
                    'value'      => $serialized,
                    'updated_by' => Auth::id(),
                ]);
            }
        }
        session()->flash('success', 'Paramètres enregistrés.');
    }

    #[Layout('layouts.admin')]
    public function render()
    {
        $groups = Setting::orderBy('group')->orderBy('sort_order')->get()->groupBy('group');

        $recentChanges = Activity::inLog('settings')
            ->with(['causer', 'subject'])
            ->latest()
            ->limit(20)
            ->get();

        return view('livewire.admin.settings', compact('groups', 'recentChanges'));
    }
}
```

### 9.3 Vue `livewire/admin/settings.blade.php`

Structure:

```
Paramètres
Configurez le comportement global du site.

┌─ Card: "Site" ────────────────────────────┐
│ ☑ Inscriptions ouvertes                   │
│ ☐ Mode maintenance                        │
│ Message de maintenance [text input]       │
└───────────────────────────────────────────┘

┌─ Card: "Blog" ────────────────────────────┐
│ ☑ Création d'articles par les membres     │
│ ☐ Modération avant publication            │
└───────────────────────────────────────────┘

┌─ Card: "Commentaires" ────────────────────┐
│ ☑ Commentaires activés                    │
│ ☑ Modération préalable des commentaires   │
└───────────────────────────────────────────┘

[Enregistrer les modifications]

Historique des modifications
• Admin Bassila a modifié blog.public_creation (true → false) — il y a 2h
• Admin Bassila a modifié comments.enabled (true → false) — il y a 3h
• …
```

Design rules:
- **3 cards** bordered, same styling as the existing admin Dashboard KPI cards
  (`bg-white border border-gray-200 p-6`)
- Group title: textual only, no emoji (`text-sm font-bold uppercase tracking-wider text-[#111827]`)
- Group title label source: `__('settings.groups.'.$groupName)` — a simple
  French translation map in `lang/fr/settings.php`:
  `['groups' => ['site' => 'Site', 'blog' => 'Blog', 'comments' => 'Commentaires']]`
- Bool fields: checkbox + label + optional description
- String fields: full-width `<input type="text">` with label above and
  description between label and input
- Single save button at the bottom, blue (`bg-[#0066CC]`), triggers `save()`
  for **all** modified values at once (not per-field)
- History section: a separate bordered card with a vertical list of the last
  20 changes, each row showing causer name, the key, old→new values, and a
  relative timestamp

### 9.4 Rendering template (excerpt)

```blade
<div>
    <div class="mb-8">
        <p class="text-xs font-semibold text-[#DC143C] uppercase tracking-widest mb-2">Administration</p>
        <h1 class="text-3xl font-bold text-[#111827]">Paramètres</h1>
        <p class="text-gray-500 mt-1">Configurez le comportement global du site.</p>
    </div>

    <form wire:submit.prevent="save">
        @foreach ($groups as $groupName => $groupSettings)
            <div class="bg-white border border-gray-200 p-6 mb-6">
                <h2 class="text-sm font-bold text-[#111827] uppercase tracking-wider mb-5">
                    {{ __('settings.groups.'.$groupName) }}
                </h2>
                <div class="space-y-5">
                    @foreach ($groupSettings as $setting)
                        @if ($setting->type === 'bool')
                            <label class="flex items-start gap-3 cursor-pointer">
                                <input type="checkbox"
                                       wire:model="values.{{ $setting->key }}"
                                       class="mt-0.5 w-4 h-4 text-[#0066CC] border-gray-300">
                                <div>
                                    <p class="text-sm font-semibold text-[#111827]">{{ $setting->label }}</p>
                                    @if ($setting->description)
                                        <p class="text-xs text-gray-500 mt-0.5">{{ $setting->description }}</p>
                                    @endif
                                </div>
                            </label>
                        @elseif ($setting->type === 'string')
                            <div>
                                <label class="block text-sm font-semibold text-[#111827] mb-1">{{ $setting->label }}</label>
                                @if ($setting->description)
                                    <p class="text-xs text-gray-500 mb-2">{{ $setting->description }}</p>
                                @endif
                                <input type="text"
                                       wire:model="values.{{ $setting->key }}"
                                       class="w-full border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:border-[#0066CC]">
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endforeach

        <button type="submit"
                class="bg-[#0066CC] hover:bg-blue-800 text-white font-semibold px-6 py-3 text-sm transition">
            Enregistrer les modifications
        </button>
    </form>

    {{-- History section --}}
    <div class="bg-white border border-gray-200 mt-10">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="text-sm font-bold text-[#111827] uppercase tracking-wider">Historique des modifications</h3>
        </div>
        <ul class="divide-y divide-gray-100">
            @forelse ($recentChanges as $activity)
                <li class="px-6 py-3 flex items-center justify-between text-sm">
                    <span>
                        <strong class="text-[#111827]">{{ $activity->causer?->name ?? 'Système' }}</strong>
                        a modifié
                        <code class="text-xs bg-gray-100 px-1.5 py-0.5">{{ $activity->subject?->key }}</code>
                        @if (isset($activity->properties['old']['value'], $activity->properties['attributes']['value']))
                            ({{ $activity->properties['old']['value'] }} → {{ $activity->properties['attributes']['value'] }})
                        @endif
                    </span>
                    <span class="text-xs text-gray-400">{{ $activity->created_at->diffForHumans() }}</span>
                </li>
            @empty
                <li class="px-6 py-6 text-center text-sm text-gray-400">Aucune modification enregistrée.</li>
            @endforelse
        </ul>
    </div>
</div>
```

## 10. Rollout — files touched by this spec

### 10.1 New files

| Path | Purpose |
|---|---|
| `database/migrations/{ts}_create_settings_table.php` | Settings table |
| `database/migrations/{ts}_create_activity_log_table.php` | Spatie activitylog (published) |
| `database/seeders/SettingSeeder.php` | 7 initial settings |
| `app/Models/Setting.php` | Eloquent model + LogsActivity + cache invalidation |
| `app/helpers.php` | Global `setting()` helper |
| `app/Http/Middleware/MaintenanceModeCheck.php` | 503 gate with admin bypass |
| `app/Http/Middleware/CheckRegistrationOpen.php` | `/inscription` guard |
| `app/Livewire/Admin/Settings.php` | Livewire component for `/admin/parametres` |
| `resources/views/livewire/admin/settings.blade.php` | Admin settings page |
| `resources/views/maintenance.blade.php` | 503 page |
| `lang/fr/settings.php` | Group label translations |
| `tests/Feature/Admin/SettingsTest.php` | Admin page tests |
| `tests/Feature/Settings/MaintenanceModeTest.php` | Middleware tests |
| `tests/Feature/Settings/RegistrationClosedTest.php` | Registration gate tests |
| `tests/Feature/Settings/BlogFlagsTest.php` | Blog policy + moderation tests |
| `tests/Feature/Settings/SettingSeederTest.php` | Seeder idempotence + value preservation |
| `tests/Unit/SettingModelTest.php` | Model cast + cache invalidation |
| `tests/Unit/SettingHelperTest.php` | Helper default fallback |

### 10.2 Modified files

| Path | Change |
|---|---|
| `composer.json` | Add `"files": ["app/helpers.php"]` to autoload |
| `bootstrap/app.php` | Register `MaintenanceModeCheck` in web group + alias |
| `routes/web.php` | Wrap `/inscription` with `CheckRegistrationOpen` + add `admin.settings` route + `can:create,App\Models\BlogPost` on `blog.create` |
| `database/seeders/DatabaseSeeder.php` | Call `SettingSeeder` |
| `app/Policies/BlogPostPolicy.php` | `create()` reads setting |
| `app/Livewire/Blog/CreatePost.php` | Force `draft` if moderation required |
| `app/Livewire/Blog/EditPost.php` | Same as CreatePost |
| `app/Livewire/Blog/CommentForm.php` | Check `comments.enabled`, branch on `comments.require_moderation` |
| `resources/views/blog/show.blade.php` | Conditional render of comment form |
| `resources/views/partials/nav.blade.php` | Conditional "Créer un profil" + "Rédiger un article" CTAs |
| `resources/views/layouts/admin.blade.php` | Add "Paramètres" to `$navItems` |

## 11. Testing strategy

Thirteen tests, split across unit and feature layers:

### Unit

1. **`SettingModelTest::it_casts_value_according_to_type`** — `casted_value`
   returns bool for `type=bool` and string for `type=string`.
2. **`SettingModelTest::it_invalidates_cache_on_save`** — after
   `Setting::first()->update(['value' => '0'])`, `cache()->get('settings.all')`
   is `null`.
3. **`SettingHelperTest::it_returns_default_when_key_missing`** —
   `setting('nonexistent', 'fallback') === 'fallback'`.
4. **`SettingHelperTest::it_returns_casted_value_for_existing_key`** —
   `setting('blog.public_creation')` returns a bool, not a string.

### Feature — Seeder

5. **`SettingSeederTest::it_preserves_existing_values_on_rerun`** — run seeder,
   then manually UPDATE a setting's `value` AND `label` directly via the model,
   then re-run the seeder, and assert: (a) the admin-edited `value` is still
   the manually-set one, (b) the `label` has been reverted to match the seeder
   source of truth.

### Feature — Middleware

6. **`MaintenanceModeTest::guest_is_blocked_when_enabled`** — setting toggle +
   GET `/` → 503 + view `maintenance`.
7. **`MaintenanceModeTest::admin_bypasses`** — admin user GET `/` → 200.
8. **`MaintenanceModeTest::login_route_stays_accessible`** — GET `/connexion` →
   200 even when maintenance is on.

### Feature — Settings gate

9. **`RegistrationClosedTest::redirects_to_login`** — setting false + GET
   `/inscription` → 302 `/connexion` + flash message.
10. **`BlogFlagsTest::policy_blocks_members_when_public_creation_disabled`** —
    setting false + member tries GET `/blog/rediger` → 403. Admin still gets 200.
11. **`BlogFlagsTest::force_draft_when_require_moderation_enabled`** — setting
    true + member calls `CreatePost::save` with `status=published` → DB shows
    `status=draft`.

### Feature — Admin UI

12. **`SettingsTest::admin_can_see_all_groups_and_fields`** — admin GET
    `/admin/parametres` → 200, page contains the 3 group labels and the 7 field
    labels.
13. **`SettingsTest::save_updates_db_and_writes_activity_log`** — admin toggles
    `blog.public_creation`, calls `save` → DB updated + `activity_log` has a
    row with `log_name=settings`, `description=setting.updated`, `properties`
    contains old and new `value`.

## 12. Acceptance criteria

This sub-project is done when:

- [ ] `/admin/parametres` renders 3 grouped cards showing all 7 settings.
- [ ] Toggling a setting and clicking "Enregistrer" persists the change and
      logs an activity entry visible in the history section within the same
      page refresh.
- [ ] `php artisan db:seed --class=SettingSeeder` on a fresh DB creates the 7
      rows; re-running it on a DB with admin-edited values does NOT revert any
      value.
- [ ] `setting('site.maintenance_mode', false) === true` causes every public
      route to return 503 except `/connexion`, `/admin/*`, `/livewire/*`, and
      admin-authenticated sessions.
- [ ] The `setting()` helper executes exactly one DB query per HTTP request
      (measured via `DB::enableQueryLog`).
- [ ] All 13 tests pass.
- [ ] The `admin` access pre-existing test suite still passes (no regression
      on the admin panel built in the prior session).

## 13. Open questions / follow-ups deferred to later sub-projects

- **`settings.manage` permission** → resolved in sub-project ② RBAC. For now,
  access is `hasRole('admin')` only.
- **Additional types** (`int`, `text`, `json`) → added on demand, branch in the
  render loop is the only code touched.
- **Per-setting validation rules** (e.g. maintenance message max length) →
  not in MVP, added when a setting needs it.
- **Settings visibility on the public `/` home page** (show a "maintenance
  planned" banner) → separate feature, tracked as future work.
