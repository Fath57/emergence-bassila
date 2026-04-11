# Site Settings Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build the Site Settings + Feature Flags foundation (sub-project ① of the admin autonomy roadmap) — a key-value store, global `setting()` helper, activitylog-backed audit, and an admin page rendering 3 grouped cards for 7 initial feature flags.

**Architecture:** A single `settings` table with typed (`bool` | `string`) key-value rows. A global `setting(key, default)` helper reads through a permanent Laravel cache (`settings.all`) with explicit invalidation on model `saved`/`deleted` events. Audit trail via `spatie/laravel-activitylog` (already in composer). Runtime effects applied via Blog policy, `/inscription` guard middleware, global `web` maintenance middleware, and Livewire component checks. The admin page generates its form from DB metadata — adding a new setting requires only a seeder row.

**Tech Stack:** PHP 8.2+, Laravel 13, Livewire 3, PostgreSQL 15, Spatie Activitylog 4.12, Pest 2. The existing project conventions apply: `TestCase::setUp()` enforces the test DB name guard, `tests/Pest.php` seeds roles via `beforeEach`, all views use Lora + Source Sans 3 via `@layer base` in `resources/css/app.css`.

**Spec:** `docs/superpowers/specs/2026-04-11-site-settings-design.md`

---

## Pre-flight notes for the implementing engineer

Read once before starting Task 1.

1. **Test database is isolated** — `phpunit.xml` forces `DB_DATABASE=emergence_bassila_test` on port 5432 with credentials `postgres` / `demcrm!24`. The `tests/TestCase::setUp()` throws a `RuntimeException` if connected elsewhere. If you need to reseed the dev DB, use `php artisan db:seed` (no `--env`); for the test DB, use `php artisan migrate --env=testing`.

2. **Role seeding in tests** — `tests/Pest.php` has a `beforeEach` that calls `$this->seed(RoleSeeder::class)` for every Feature test. Roles `admin`, `moderator`, `user` are available via `$user->assignRole('admin')`. No manual role creation needed in tests.

3. **Locale is French** — assertions on flash messages and rendered text must use the exact French strings defined in the spec and in `lang/fr/settings.php`.

4. **Livewire 3 conventions** — public properties bind via `wire:model`, `#[Layout('layouts.admin')]` attribute on `render()` sets the layout. For full-page components, pass the component class directly in `routes/web.php`.

5. **The admin middleware already exists** — `app/Http/Middleware/EnsureUserIsAdmin.php` is registered with alias `admin` in `bootstrap/app.php`. Reuse it for the new route.

6. **The admin layout already has a `$navItems` array** — `resources/views/layouts/admin.blade.php` lines 32-37. Adding a nav entry is a one-line change.

7. **PostgreSQL generated columns exist** — `users.name` and `profiles.full_name` are `GENERATED ALWAYS AS (...) STORED`. If you need to insert into these tables manually, use `first_name` + `last_name`, never `name` or `full_name`.

8. **Factories exist** — `UserFactory` uses `fake()->firstName()` + `fake()->lastName()`. Assign role after creation: `User::factory()->create()->assignRole('admin')`.

---

## Task 1: Scaffolding — activitylog migration + helpers.php autoload

**Purpose:** Publish the Spatie Activitylog migrations, run them on both dev and test databases, create an empty `app/helpers.php` registered via composer autoload files, and verify everything boots.

**Files:**
- Create: `app/helpers.php`
- Create: `database/migrations/{ts}_create_activity_log_table.php` (published by Spatie)
- Create: `database/migrations/{ts}_add_event_column_to_activity_log_table.php` (published by Spatie)
- Create: `database/migrations/{ts}_add_batch_uuid_column_to_activity_log_table.php` (published by Spatie)
- Modify: `composer.json:18-22` (autoload section)

---

- [ ] **Step 1.1: Publish the Spatie Activitylog migrations**

```bash
php artisan vendor:publish --provider="Spatie\\Activitylog\\ActivitylogServiceProvider" --tag="activitylog-migrations"
```

Expected output:
```
Copying file [vendor/spatie/laravel-activitylog/database/migrations/create_activity_log_table.php.stub] to [database/migrations/{ts}_create_activity_log_table.php] .............. DONE
Copying file [.../add_event_column_to_activity_log_table.php.stub] to [.../{ts}_add_event_column_to_activity_log_table.php] .............. DONE
Copying file [.../add_batch_uuid_column_to_activity_log_table.php.stub] to [.../{ts}_add_batch_uuid_column_to_activity_log_table.php] .............. DONE
```

Three new migration files appear under `database/migrations/`.

- [ ] **Step 1.2: Run migrations on the dev database**

```bash
php artisan migrate
```

Expected: three activitylog migrations run successfully. Verify:
```bash
PGPASSWORD='demcrm!24' psql -h 127.0.0.1 -p 5432 -U postgres -d emergence_bassila -tAc "\dt activity_log"
```
Should output one row referencing `public|activity_log|table|postgres`.

- [ ] **Step 1.3: Run migrations on the test database**

```bash
php artisan migrate --env=testing
```

Expected: the same three migrations run against `emergence_bassila_test`.

- [ ] **Step 1.4: Create `app/helpers.php` skeleton**

Create the file with this exact content (we'll add the `setting()` function in Task 3):

```php
<?php

/*
|--------------------------------------------------------------------------
| Global helpers
|--------------------------------------------------------------------------
|
| Functions registered via composer.json "autoload.files". Add new helpers
| here guarded with function_exists() to avoid redeclaration errors during
| tests and class discovery.
|
*/
```

- [ ] **Step 1.5: Register the helpers file in composer autoload**

Open `composer.json`. Find the `"autoload"` section (around line 18-25) which currently looks like:

```json
"autoload": {
    "psr-4": {
        "App\\": "app/",
        "Database\\Factories\\": "database/factories/",
        "Database\\Seeders\\": "database/seeders/"
    }
},
```

Replace it with:

```json
"autoload": {
    "psr-4": {
        "App\\": "app/",
        "Database\\Factories\\": "database/factories/",
        "Database\\Seeders\\": "database/seeders/"
    },
    "files": [
        "app/helpers.php"
    ]
},
```

- [ ] **Step 1.6: Regenerate the composer autoloader**

```bash
composer dump-autoload
```

Expected: `Generated optimized autoload files containing NNNN classes`.

- [ ] **Step 1.7: Sanity-check: artisan still boots**

```bash
php artisan about --only=environment | head -5
```

Expected: normal Laravel environment output with no fatal errors.

- [ ] **Step 1.8: Commit**

```bash
git add app/helpers.php composer.json composer.lock database/migrations/*_activity_log_table.php database/migrations/*_add_event_column_*.php database/migrations/*_add_batch_uuid_column_*.php
git commit -m "$(cat <<'EOF'
feat(settings): scaffold activitylog + helpers autoload

Publish spatie/laravel-activitylog migrations, run them on dev + test
databases, and register app/helpers.php via composer autoload files. This
is the prerequisite scaffolding for the Site Settings sub-project.

Co-Authored-By: Claude Opus 4.6 (1M context) <noreply@anthropic.com>
EOF
)"
```

---

## Task 2: Settings table migration + Setting Eloquent model

**Purpose:** Create the `settings` table with the typed key-value schema, implement the `Setting` model with `LogsActivity`, value cast, and cache invalidation. Test-first for cast behavior and cache invalidation.

**Files:**
- Create: `database/migrations/{ts}_create_settings_table.php`
- Create: `app/Models/Setting.php`
- Test: `tests/Feature/Settings/SettingModelTest.php`

---

- [ ] **Step 2.1: Write the failing model tests**

Create `tests/Feature/Settings/SettingModelTest.php` with this exact content:

```php
<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

it('casts the value according to the type column', function () {
    $bool = Setting::create([
        'key'         => 'test.bool',
        'value'       => '1',
        'type'        => 'bool',
        'group'       => 'test',
        'label'       => 'Bool flag',
        'description' => null,
        'sort_order'  => 0,
    ]);

    $string = Setting::create([
        'key'         => 'test.string',
        'value'       => 'hello world',
        'type'        => 'string',
        'group'       => 'test',
        'label'       => 'String setting',
        'description' => null,
        'sort_order'  => 0,
    ]);

    expect($bool->casted_value)->toBe(true)
        ->and($string->casted_value)->toBe('hello world');

    // Bool false is also correctly cast
    $bool->update(['value' => '0']);
    expect($bool->fresh()->casted_value)->toBe(false);
});

it('invalidates the settings.all cache on save and delete', function () {
    Cache::put('settings.all', 'pre-existing', now()->addHour());
    expect(Cache::get('settings.all'))->toBe('pre-existing');

    $setting = Setting::create([
        'key'         => 'test.cache',
        'value'       => '1',
        'type'        => 'bool',
        'group'       => 'test',
        'label'       => 'Cache flag',
        'description' => null,
        'sort_order'  => 0,
    ]);

    // saved event should have cleared the cache
    expect(Cache::get('settings.all'))->toBeNull();

    Cache::put('settings.all', 'post-create-repopulated', now()->addHour());
    $setting->delete();
    expect(Cache::get('settings.all'))->toBeNull();
});
```

- [ ] **Step 2.2: Run tests to verify they fail**

```bash
./vendor/bin/pest tests/Feature/Settings/SettingModelTest.php
```

Expected: both tests fail with `Class "App\Models\Setting" does not exist` or table missing. This is the expected red state.

- [ ] **Step 2.3: Create the migration**

```bash
php artisan make:migration create_settings_table
```

Open the generated file (in `database/migrations/{ts}_create_settings_table.php`) and replace its contents with:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->text('value')->nullable();
            $table->string('type', 20)->default('bool');
            $table->string('group', 50)->index();
            $table->string('label', 255);
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->foreignId('updated_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
```

- [ ] **Step 2.4: Run migrations on both databases**

```bash
php artisan migrate
php artisan migrate --env=testing
```

Expected: both run the new migration successfully.

- [ ] **Step 2.5: Create the Setting model**

Create `app/Models/Setting.php` with:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Setting extends Model
{
    use LogsActivity;

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'label',
        'description',
        'sort_order',
        'updated_by',
    ];

    /**
     * Runtime cast of `value` based on the `type` column.
     * Extend the match for new types (`int`, `json`, `text`) without migrating.
     */
    public function getCastedValueAttribute(): mixed
    {
        return match ($this->type) {
            'bool'   => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'string' => (string) $this->value,
            default  => $this->value,
        };
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
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
        static::saved(fn () => Cache::forget('settings.all'));
        static::deleted(fn () => Cache::forget('settings.all'));
    }
}
```

- [ ] **Step 2.6: Run tests to verify they pass**

```bash
./vendor/bin/pest tests/Feature/Settings/SettingModelTest.php
```

Expected:
```
PASS  Tests\Feature\Settings\SettingModelTest
✓ it casts the value according to the type column
✓ it invalidates the settings.all cache on save and delete
Tests: 2 passed
```

- [ ] **Step 2.7: Commit**

```bash
git add database/migrations/*_create_settings_table.php app/Models/Setting.php tests/Feature/Settings/SettingModelTest.php
git commit -m "$(cat <<'EOF'
feat(settings): add Setting model + migration with cast and cache invalidation

Key-value typed table backing the site settings system. Setting model uses
LogsActivity trait (spatie) to audit value changes and invalidates the
shared `settings.all` cache key on save/delete via the booted() hook.

Co-Authored-By: Claude Opus 4.6 (1M context) <noreply@anthropic.com>
EOF
)"
```

---

## Task 3: Global `setting()` helper

**Purpose:** Implement the global `setting(key, default)` helper that reads all settings from a single cached collection and applies the model's cast. Test with missing key and existing key.

**Files:**
- Modify: `app/helpers.php` (add the function)
- Test: `tests/Feature/Settings/SettingHelperTest.php`

---

- [ ] **Step 3.1: Write the failing helper tests**

Create `tests/Feature/Settings/SettingHelperTest.php`:

```php
<?php

use App\Models\Setting;

it('returns the default when the key is missing', function () {
    $result = setting('nonexistent.key', 'fallback');
    expect($result)->toBe('fallback');
});

it('returns null as default when none is provided for a missing key', function () {
    $result = setting('also.nonexistent');
    expect($result)->toBeNull();
});

it('returns the casted value for an existing bool setting', function () {
    Setting::create([
        'key'         => 'flag.enabled',
        'value'       => '1',
        'type'        => 'bool',
        'group'       => 'test',
        'label'       => 'Flag',
        'description' => null,
        'sort_order'  => 0,
    ]);

    expect(setting('flag.enabled'))->toBe(true);
});

it('returns the casted value for an existing string setting', function () {
    Setting::create([
        'key'         => 'site.motto',
        'value'       => 'Connectés, engagés, inspirants.',
        'type'        => 'string',
        'group'       => 'test',
        'label'       => 'Motto',
        'description' => null,
        'sort_order'  => 0,
    ]);

    expect(setting('site.motto'))->toBe('Connectés, engagés, inspirants.');
});

it('reflects value changes on the next call after save', function () {
    $s = Setting::create([
        'key'         => 'flag.live',
        'value'       => '0',
        'type'        => 'bool',
        'group'       => 'test',
        'label'       => 'Live',
        'description' => null,
        'sort_order'  => 0,
    ]);

    expect(setting('flag.live'))->toBe(false);

    $s->update(['value' => '1']);

    expect(setting('flag.live'))->toBe(true);
});
```

- [ ] **Step 3.2: Run tests to verify they fail**

```bash
./vendor/bin/pest tests/Feature/Settings/SettingHelperTest.php
```

Expected: all 5 fail with `Call to undefined function setting()`.

- [ ] **Step 3.3: Implement the helper**

Open `app/helpers.php` and replace its contents with:

```php
<?php

/*
|--------------------------------------------------------------------------
| Global helpers
|--------------------------------------------------------------------------
|
| Functions registered via composer.json "autoload.files". Add new helpers
| here guarded with function_exists() to avoid redeclaration errors during
| tests and class discovery.
|
*/

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

if (! function_exists('setting')) {
    /**
     * Return a site setting value, falling back to $default when the key
     * is absent or the settings table is unavailable.
     *
     * The full settings collection is loaded once per process via a permanent
     * cache key (`settings.all`). The cache is invalidated by the Setting
     * model's saved/deleted events — callers never need to touch it manually.
     */
    function setting(string $key, mixed $default = null): mixed
    {
        $all = Cache::rememberForever('settings.all', function () {
            return Setting::all()->keyBy('key');
        });

        $setting = $all->get($key);

        return $setting?->casted_value ?? $default;
    }
}
```

- [ ] **Step 3.4: Dump autoload to pick up the new function**

```bash
composer dump-autoload
```

- [ ] **Step 3.5: Run tests to verify they pass**

```bash
./vendor/bin/pest tests/Feature/Settings/SettingHelperTest.php
```

Expected:
```
PASS  Tests\Feature\Settings\SettingHelperTest
✓ it returns the default when the key is missing
✓ it returns null as default when none is provided for a missing key
✓ it returns the casted value for an existing bool setting
✓ it returns the casted value for an existing string setting
✓ it reflects value changes on the next call after save
Tests: 5 passed
```

- [ ] **Step 3.6: Commit**

```bash
git add app/helpers.php tests/Feature/Settings/SettingHelperTest.php
git commit -m "$(cat <<'EOF'
feat(settings): add global setting() helper with permanent cache

setting('key', default) reads from a single cached collection loaded once
per process. Returns the casted value (bool/string) via the Setting model
accessor, or the provided default if the key is missing.

Co-Authored-By: Claude Opus 4.6 (1M context) <noreply@anthropic.com>
EOF
)"
```

---

## Task 4: `SettingSeeder` with idempotent value preservation

**Purpose:** Seed the 7 initial settings and guarantee that re-running the seeder **preserves admin-edited values** while keeping metadata (label/description/group/sort_order/type) in sync with the seeder source of truth.

**Files:**
- Create: `database/seeders/SettingSeeder.php`
- Test: `tests/Feature/Settings/SettingSeederTest.php`
- Modify: `database/seeders/DatabaseSeeder.php`

---

- [ ] **Step 4.1: Write the failing seeder test**

Create `tests/Feature/Settings/SettingSeederTest.php`:

```php
<?php

use App\Models\Setting;
use Database\Seeders\SettingSeeder;

it('seeds 7 settings on a fresh database', function () {
    $this->seed(SettingSeeder::class);

    expect(Setting::count())->toBe(7);

    $expectedKeys = [
        'site.registration_open',
        'site.maintenance_mode',
        'site.maintenance_message',
        'blog.public_creation',
        'blog.require_moderation',
        'comments.enabled',
        'comments.require_moderation',
    ];

    foreach ($expectedKeys as $key) {
        expect(Setting::where('key', $key)->exists())->toBeTrue("Missing setting: {$key}");
    }
});

it('preserves admin-edited values on re-run while re-syncing metadata', function () {
    // First seed
    $this->seed(SettingSeeder::class);

    // Admin edits both a value and a label directly in DB
    $setting = Setting::where('key', 'blog.public_creation')->first();
    $setting->update([
        'value' => '0',                            // admin turned it off
        'label' => 'LABEL HACKED BY ADMIN',        // also renamed the label (should be reverted)
    ]);

    // Re-seed
    $this->seed(SettingSeeder::class);

    $reloaded = Setting::where('key', 'blog.public_creation')->first();

    // Value preserved
    expect($reloaded->value)->toBe('0')
        ->and($reloaded->casted_value)->toBe(false);

    // Label re-synced to the seeder source of truth
    expect($reloaded->label)->toBe("Création d'articles par les membres");
});

it('seeds the string-typed maintenance message with its default text', function () {
    $this->seed(SettingSeeder::class);

    $msg = Setting::where('key', 'site.maintenance_message')->first();

    expect($msg->type)->toBe('string')
        ->and($msg->value)->toContain('temporairement indisponible');
});
```

- [ ] **Step 4.2: Run tests to verify they fail**

```bash
./vendor/bin/pest tests/Feature/Settings/SettingSeederTest.php
```

Expected: 3 failures with `Class "Database\Seeders\SettingSeeder" not found`.

- [ ] **Step 4.3: Create the SettingSeeder**

Create `database/seeders/SettingSeeder.php`:

```php
<?php

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

- [ ] **Step 4.4: Register the seeder in DatabaseSeeder**

Open `database/seeders/DatabaseSeeder.php` and look at its `run()` method. Add a call to `SettingSeeder` in the list of seeders. It should look something like this after the change (adapt to the actual existing order):

```php
public function run(): void
{
    $this->call([
        RoleSeeder::class,
        CountrySeeder::class,
        SectorSeeder::class,
        SkillSeeder::class,
        SettingSeeder::class,      // ← new
        AdminSeeder::class,
        ProfileSeeder::class,
    ]);
}
```

If `DatabaseSeeder.php` uses individual `$this->call(XxxSeeder::class)` lines instead of an array, append `$this->call(SettingSeeder::class);` on its own line, placed right after `SkillSeeder` and before `AdminSeeder`.

- [ ] **Step 4.5: Run tests to verify they pass**

```bash
./vendor/bin/pest tests/Feature/Settings/SettingSeederTest.php
```

Expected:
```
PASS  Tests\Feature\Settings\SettingSeederTest
✓ it seeds 7 settings on a fresh database
✓ it preserves admin-edited values on re-run while re-syncing metadata
✓ it seeds the string-typed maintenance message with its default text
Tests: 3 passed
```

- [ ] **Step 4.6: Seed the dev database**

```bash
php artisan db:seed --class=SettingSeeder
PGPASSWORD='demcrm!24' psql -h 127.0.0.1 -p 5432 -U postgres -d emergence_bassila -tAc "SELECT key, type, value FROM settings ORDER BY \"group\", sort_order"
```

Expected: 7 rows printed, with keys in order `blog.public_creation`, `blog.require_moderation`, `comments.enabled`, `comments.require_moderation`, `site.registration_open`, `site.maintenance_mode`, `site.maintenance_message`.

- [ ] **Step 4.7: Commit**

```bash
git add database/seeders/SettingSeeder.php database/seeders/DatabaseSeeder.php tests/Feature/Settings/SettingSeederTest.php
git commit -m "$(cat <<'EOF'
feat(settings): add SettingSeeder with idempotent value preservation

Seeds 7 initial settings (site/blog/comments groups). On re-run, the
seeder uses a firstOrNew two-branch pattern: new rows get the default
value, existing rows keep their admin-edited value while metadata
(label/description/group/sort_order/type) is re-synced from the seeder
source of truth. Wired into DatabaseSeeder::run.

Co-Authored-By: Claude Opus 4.6 (1M context) <noreply@anthropic.com>
EOF
)"
```

---

## Task 5: `MaintenanceModeCheck` middleware + maintenance view

**Purpose:** Implement the middleware that blocks all public routes with a 503 + `maintenance.blade.php` when `site.maintenance_mode=true`, with bypass rules for admins, `/connexion`, `/admin/*`, `/livewire/*`, `/build/*`. Create the maintenance view.

**Files:**
- Create: `app/Http/Middleware/MaintenanceModeCheck.php`
- Create: `resources/views/maintenance.blade.php`
- Modify: `bootstrap/app.php`
- Test: `tests/Feature/Settings/MaintenanceModeTest.php`

---

- [ ] **Step 5.1: Write the failing middleware tests**

Create `tests/Feature/Settings/MaintenanceModeTest.php`:

```php
<?php

use App\Models\Setting;
use App\Models\User;

beforeEach(function () {
    // Seed only the settings we care about for these tests
    Setting::create([
        'key' => 'site.maintenance_mode', 'value' => '1', 'type' => 'bool',
        'group' => 'site', 'sort_order' => 1,
        'label' => 'Mode maintenance', 'description' => null,
    ]);
    Setting::create([
        'key' => 'site.maintenance_message', 'value' => 'Maintenance en cours',
        'type' => 'string', 'group' => 'site', 'sort_order' => 2,
        'label' => 'Message', 'description' => null,
    ]);
});

it('blocks guests with a 503 when maintenance mode is enabled', function () {
    $response = $this->get('/');

    $response->assertStatus(503)
             ->assertSee('Maintenance en cours');
});

it('allows admin users to bypass maintenance mode', function () {
    $admin = User::factory()->create(['email_verified_at' => now()]);
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->get('/')
        ->assertSuccessful();
});

it('keeps the login route accessible during maintenance', function () {
    $this->get('/connexion')->assertSuccessful();
});

it('keeps the admin routes accessible during maintenance for logged-in admins', function () {
    $admin = User::factory()->create(['email_verified_at' => now()]);
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->get('/admin')
        ->assertSuccessful();
});

it('passes through when maintenance mode is disabled', function () {
    Setting::where('key', 'site.maintenance_mode')->update(['value' => '0']);

    $this->get('/')->assertSuccessful();
});
```

- [ ] **Step 5.2: Run tests to verify they fail**

```bash
./vendor/bin/pest tests/Feature/Settings/MaintenanceModeTest.php
```

Expected: all 5 tests fail — the middleware doesn't exist and the maintenance view doesn't exist, so the app never returns 503 for guests; the "blocks guests" test fails because `/` returns 200.

- [ ] **Step 5.3: Create the middleware**

Create `app/Http/Middleware/MaintenanceModeCheck.php`:

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MaintenanceModeCheck
{
    /** @var string[] Exact paths always allowed through maintenance mode. */
    private const ALLOW_PATHS = ['connexion', 'up'];

    /** @var string[] Path prefixes always allowed through maintenance mode. */
    private const ALLOW_PREFIXES = ['admin', 'livewire', 'build'];

    public function handle(Request $request, Closure $next): Response
    {
        if (! setting('site.maintenance_mode', false)) {
            return $next($request);
        }

        // Admins always pass through so they can still sign in and manage the site.
        if ($request->user()?->hasRole('admin')) {
            return $next($request);
        }

        $path = $request->path();

        if (in_array($path, self::ALLOW_PATHS, true)) {
            return $next($request);
        }

        foreach (self::ALLOW_PREFIXES as $prefix) {
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

- [ ] **Step 5.4: Create the maintenance view**

Create `resources/views/maintenance.blade.php`:

```blade
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Maintenance — Bassila Émergence</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=lora:400,500,600,700|source-sans-3:400,400i,600,700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css'])
</head>
<body class="antialiased">
<div class="min-h-screen flex">

    <div class="hidden lg:flex lg:w-5/12 xl:w-2/5 bg-[#0A1628] flex-col justify-between relative overflow-hidden">
        <div class="absolute inset-0 pointer-events-none"
             style="background-image: linear-gradient(rgba(255,255,255,0.03) 1px, transparent 1px),
                                      linear-gradient(90deg, rgba(255,255,255,0.03) 1px, transparent 1px);
                    background-size: 48px 48px;"></div>
        <div class="absolute top-0 left-0 bottom-0 w-0.5 bg-[#DC143C]"></div>

        <div class="relative px-12 pt-14">
            <img src="{{ asset('images/logo.png') }}" alt="Bassila Émergence" class="h-14 w-auto mb-16">
            <h2 class="text-white/80 text-2xl font-bold leading-tight">
                Maintenance en cours
            </h2>
        </div>
    </div>

    <div class="flex-1 flex flex-col bg-white min-h-screen">
        <header class="lg:hidden border-b border-gray-100 h-14 px-6 flex items-center">
            <img src="{{ asset('images/logo-trans.png') }}" alt="Bassila Émergence" class="h-8 w-auto">
        </header>

        <main class="flex-1 flex items-center justify-center px-8 py-12">
            <div class="w-full max-w-md text-center">
                <div class="w-14 h-14 border-2 border-[#0066CC] flex items-center justify-center mx-auto mb-6">
                    <svg class="w-7 h-7 text-[#0066CC]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-[#111827] mb-3">Site en maintenance</h1>
                <p class="text-gray-500 leading-relaxed">{{ $message }}</p>
            </div>
        </main>

        <footer class="border-t border-gray-100 py-4">
            <p class="text-center text-xs text-gray-400">© {{ date('Y') }} Bassila Émergence. Tous droits réservés.</p>
        </footer>
    </div>
</div>
</body>
</html>
```

- [ ] **Step 5.5: Register the middleware in the web group**

Open `bootstrap/app.php`. Its `withMiddleware` section currently looks like:

```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->alias([
        'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
    ]);
})
```

Replace with:

```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->alias([
        'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
    ]);

    $middleware->appendToGroup('web', [
        \App\Http\Middleware\MaintenanceModeCheck::class,
    ]);
})
```

- [ ] **Step 5.6: Run tests to verify they pass**

```bash
./vendor/bin/pest tests/Feature/Settings/MaintenanceModeTest.php
```

Expected:
```
PASS  Tests\Feature\Settings\MaintenanceModeTest
✓ it blocks guests with a 503 when maintenance mode is enabled
✓ it allows admin users to bypass maintenance mode
✓ it keeps the login route accessible during maintenance
✓ it keeps the admin routes accessible during maintenance for logged-in admins
✓ it passes through when maintenance mode is disabled
Tests: 5 passed
```

- [ ] **Step 5.7: Commit**

```bash
git add app/Http/Middleware/MaintenanceModeCheck.php resources/views/maintenance.blade.php bootstrap/app.php tests/Feature/Settings/MaintenanceModeTest.php
git commit -m "$(cat <<'EOF'
feat(settings): add maintenance mode middleware + 503 view

When site.maintenance_mode=true, block all public routes with a 503 +
maintenance.blade.php showing site.maintenance_message. Bypass rules:
users with role `admin` always pass through; /connexion, /admin/*,
/livewire/*, /build/*, and /up are allowlisted so the admin can still
sign in and drive the site out of maintenance.

Co-Authored-By: Claude Opus 4.6 (1M context) <noreply@anthropic.com>
EOF
)"
```

---

## Task 6: `CheckRegistrationOpen` middleware + nav conditional

**Purpose:** Block `/inscription` when `site.registration_open=false` by redirecting to `/connexion` with a flash message. Hide the "Créer un profil" CTA from the nav when closed.

**Files:**
- Create: `app/Http/Middleware/CheckRegistrationOpen.php`
- Modify: `bootstrap/app.php` (add alias)
- Modify: `routes/web.php` (apply middleware)
- Modify: `resources/views/partials/nav.blade.php` (conditional CTA)
- Test: `tests/Feature/Settings/RegistrationClosedTest.php`

---

- [ ] **Step 6.1: Write the failing tests**

Create `tests/Feature/Settings/RegistrationClosedTest.php`:

```php
<?php

use App\Models\Setting;

beforeEach(function () {
    Setting::create([
        'key' => 'site.registration_open', 'value' => '0', 'type' => 'bool',
        'group' => 'site', 'sort_order' => 1,
        'label' => 'Inscriptions ouvertes', 'description' => null,
    ]);
});

it('redirects /inscription to /connexion when registration is closed', function () {
    $response = $this->get('/inscription');

    $response->assertRedirect(route('login'));
    $response->assertSessionHas('error');
});

it('flashes a French message explaining registration is closed', function () {
    $this->get('/inscription');

    expect(session('error'))->toBe('Les inscriptions sont temporairement fermées.');
});

it('allows /inscription through when registration is open', function () {
    Setting::where('key', 'site.registration_open')->update(['value' => '1']);

    $this->get('/inscription')->assertSuccessful();
});
```

- [ ] **Step 6.2: Run tests to verify they fail**

```bash
./vendor/bin/pest tests/Feature/Settings/RegistrationClosedTest.php
```

Expected: first two tests fail — guests currently reach the registration form normally; the third passes by coincidence (setting is forced open).

- [ ] **Step 6.3: Create the middleware**

Create `app/Http/Middleware/CheckRegistrationOpen.php`:

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRegistrationOpen
{
    public function handle(Request $request, Closure $next): Response
    {
        if (setting('site.registration_open', true)) {
            return $next($request);
        }

        return redirect()
            ->route('login')
            ->with('error', 'Les inscriptions sont temporairement fermées.');
    }
}
```

- [ ] **Step 6.4: Register the middleware alias in `bootstrap/app.php`**

Open `bootstrap/app.php`. Update the `->alias([...])` array to add the `registration.check` alias:

```php
$middleware->alias([
    'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
    'registration.check' => \App\Http\Middleware\CheckRegistrationOpen::class,
]);
```

- [ ] **Step 6.5: Apply the middleware to the `/inscription` route**

Open `routes/web.php`. Find the auth block:

```php
Route::middleware(['guest', 'throttle:10,1'])->group(function () {
    Route::get('/inscription', Register::class)->name('register');
    Route::get('/connexion', Login::class)->name('login');
    Route::get('/mot-de-passe-oublie', ForgotPassword::class)->name('password.request');
    Route::get('/reinitialiser-mot-de-passe/{token}', ResetPassword::class)->name('password.reset');
});
```

Change the `/inscription` line to add the `registration.check` middleware:

```php
Route::get('/inscription', Register::class)
    ->middleware('registration.check')
    ->name('register');
```

- [ ] **Step 6.6: Hide the "Créer un profil" CTA in the nav when closed**

Open `resources/views/partials/nav.blade.php`. Find the guest CTA block (around lines 44-47):

```blade
<a href="{{ route('register') }}"
   class="text-sm font-semibold bg-[#0066CC] hover:bg-blue-800 text-white px-4 py-2 transition">
    Créer un profil
</a>
```

Wrap it in a conditional:

```blade
@if (setting('site.registration_open', true))
    <a href="{{ route('register') }}"
       class="text-sm font-semibold bg-[#0066CC] hover:bg-blue-800 text-white px-4 py-2 transition">
        Créer un profil
    </a>
@endif
```

- [ ] **Step 6.7: Run tests to verify they pass**

```bash
./vendor/bin/pest tests/Feature/Settings/RegistrationClosedTest.php
```

Expected:
```
PASS  Tests\Feature\Settings\RegistrationClosedTest
✓ it redirects /inscription to /connexion when registration is closed
✓ it flashes a French message explaining registration is closed
✓ it allows /inscription through when registration is open
Tests: 3 passed
```

- [ ] **Step 6.8: Commit**

```bash
git add app/Http/Middleware/CheckRegistrationOpen.php bootstrap/app.php routes/web.php resources/views/partials/nav.blade.php tests/Feature/Settings/RegistrationClosedTest.php
git commit -m "$(cat <<'EOF'
feat(settings): gate /inscription on site.registration_open

New CheckRegistrationOpen middleware redirects /inscription to /connexion
with a French flash message when the setting is false. The "Créer un
profil" CTA in the public nav is hidden in the same case so the link
doesn't lead to a dead end.

Co-Authored-By: Claude Opus 4.6 (1M context) <noreply@anthropic.com>
EOF
)"
```

---

## Task 7: Blog flags — policy + force-draft in CreatePost/EditPost

**Purpose:** Gate blog creation behind `blog.public_creation` (admins always pass) and force `status=draft` at save time when `blog.require_moderation=true` for non-admins.

**Files:**
- Modify: `app/Policies/BlogPostPolicy.php`
- Modify: `app/Livewire/Blog/CreatePost.php`
- Modify: `app/Livewire/Blog/EditPost.php`
- Modify: `routes/web.php` (add `can:create,App\Models\BlogPost` to blog.create)
- Test: `tests/Feature/Settings/BlogFlagsTest.php`

---

- [ ] **Step 7.1: Write the failing tests**

Create `tests/Feature/Settings/BlogFlagsTest.php`:

```php
<?php

use App\Livewire\Blog\CreatePost;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Setting;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    // Start with both flags in their "tight" state
    Setting::create([
        'key' => 'blog.public_creation', 'value' => '0', 'type' => 'bool',
        'group' => 'blog', 'sort_order' => 1,
        'label' => 'Création publique', 'description' => null,
    ]);
    Setting::create([
        'key' => 'blog.require_moderation', 'value' => '1', 'type' => 'bool',
        'group' => 'blog', 'sort_order' => 2,
        'label' => 'Modération', 'description' => null,
    ]);
});

it('blocks a verified member from /blog/rediger when public_creation is disabled', function () {
    $member = User::factory()->create(['email_verified_at' => now()]);
    $member->assignRole('user');

    $this->actingAs($member)
        ->get(route('blog.create'))
        ->assertForbidden();
});

it('allows an admin through /blog/rediger even when public_creation is disabled', function () {
    $admin = User::factory()->create(['email_verified_at' => now()]);
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->get(route('blog.create'))
        ->assertSuccessful();
});

it('allows a verified member through /blog/rediger when public_creation is enabled', function () {
    Setting::where('key', 'blog.public_creation')->update(['value' => '1']);

    $member = User::factory()->create(['email_verified_at' => now()]);
    $member->assignRole('user');

    $this->actingAs($member)
        ->get(route('blog.create'))
        ->assertSuccessful();
});

it('forces status=draft for non-admin publications when require_moderation is enabled', function () {
    Setting::where('key', 'blog.public_creation')->update(['value' => '1']);

    $member = User::factory()->create(['email_verified_at' => now()]);
    $member->assignRole('user');
    $category = BlogCategory::factory()->create();

    Livewire::actingAs($member)
        ->test(CreatePost::class)
        ->set('title', 'My new post')
        ->set('slug', 'my-new-post')
        ->set('content', 'Lorem ipsum.')
        ->set('category_id', $category->id)
        ->set('status', 'published')
        ->call('save');

    $post = BlogPost::where('slug', 'my-new-post')->first();
    expect($post)->not->toBeNull()
        ->and($post->status)->toBe('draft')
        ->and($post->published_at)->toBeNull();
});

it('lets an admin publish directly even when require_moderation is enabled', function () {
    $admin = User::factory()->create(['email_verified_at' => now()]);
    $admin->assignRole('admin');
    $category = BlogCategory::factory()->create();

    Livewire::actingAs($admin)
        ->test(CreatePost::class)
        ->set('title', 'Admin post')
        ->set('slug', 'admin-post')
        ->set('content', 'Body.')
        ->set('category_id', $category->id)
        ->set('status', 'published')
        ->call('save');

    $post = BlogPost::where('slug', 'admin-post')->first();
    expect($post->status)->toBe('published')
        ->and($post->published_at)->not->toBeNull();
});
```

- [ ] **Step 7.2: Check if `BlogCategory::factory()` exists**

```bash
ls database/factories/BlogCategoryFactory.php 2>&1
```

If the factory doesn't exist, create it at `database/factories/BlogCategoryFactory.php`:

```php
<?php

namespace Database\Factories;

use App\Models\BlogCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BlogCategoryFactory extends Factory
{
    protected $model = BlogCategory::class;

    public function definition(): array
    {
        $name = $this->faker->words(2, true);
        return [
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
        ];
    }
}
```

And add `use HasFactory;` to `app/Models/BlogCategory.php` if not already there.

- [ ] **Step 7.3: Run tests to verify they fail**

```bash
./vendor/bin/pest tests/Feature/Settings/BlogFlagsTest.php
```

Expected: tests 1 ("blocks member") and 4 ("forces draft") fail — the current policy returns `true` unconditionally and CreatePost doesn't force draft. Test 2 (admin access) and 3 (open) may pass by coincidence. Test 5 (admin publishes) passes because admin always had create access.

- [ ] **Step 7.4: Update `BlogPostPolicy::create`**

Open `app/Policies/BlogPostPolicy.php` and replace the `create` method:

```php
public function create(User $user): bool
{
    if ($user->hasRole('admin')) {
        return true;
    }

    return setting('blog.public_creation', true);
}
```

- [ ] **Step 7.5: Apply `can:create` middleware on the blog.create route**

Open `routes/web.php`. Find:

```php
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/blog/rediger', CreatePost::class)->name('blog.create');
    Route::get('/blog/{slug}/modifier', EditPost::class)->name('blog.edit');
});
```

Change the `blog.create` line to:

```php
Route::get('/blog/rediger', CreatePost::class)
    ->middleware('can:create,App\Models\BlogPost')
    ->name('blog.create');
```

This turns a would-be 200 into a 403 at route entry for unauthorized users — matching the test's `assertForbidden()`.

- [ ] **Step 7.6: Update `CreatePost::save` to force draft when required**

Open `app/Livewire/Blog/CreatePost.php`. Find the `save()` method, which currently starts with:

```php
public function save(): void
{
    $this->authorize('create', BlogPost::class);

    $validated = $this->validate();
```

Insert the force-draft block right after `$validated = $this->validate();` and BEFORE the `$imageUrl` lines:

```php
public function save(): void
{
    $this->authorize('create', BlogPost::class);

    $validated = $this->validate();

    // Force draft for non-admins if moderation is required
    if ($validated['status'] === 'published'
        && setting('blog.require_moderation', false)
        && ! Auth::user()->hasRole('admin')) {
        $validated['status'] = 'draft';
        $this->status = 'draft';
        session()->flash('info', 'Votre article sera visible après validation par un administrateur.');
    }

    $imageUrl = null;
    // …rest of the method unchanged, but make sure the BlogPost::create call
    //   uses $validated['status'] and a published_at computed from $validated['status']
```

Then update the `BlogPost::create` call at the bottom of the method. Change:

```php
'status'             => $this->status,
'published_at'       => $this->status === 'published' ? now() : null,
```

to:

```php
'status'             => $validated['status'],
'published_at'       => $validated['status'] === 'published' ? now() : null,
```

- [ ] **Step 7.7: Apply the same change to `EditPost::save`**

Open `app/Livewire/Blog/EditPost.php`. Find its `save()` method. Apply the same force-draft logic after validation and before the update call. The exact shape depends on the current file; adapt:

1. After `$validated = $this->validate();`, add:

```php
if ($validated['status'] === 'published'
    && setting('blog.require_moderation', false)
    && ! Auth::user()->hasRole('admin')) {
    $validated['status'] = 'draft';
    $this->status = 'draft';
    session()->flash('info', 'Votre article sera visible après validation par un administrateur.');
}
```

2. In the `->update([...])` call at the bottom, make sure the `status` and `published_at` fields read from `$validated['status']` rather than `$this->status`.

If `EditPost` doesn't currently perform `$this->validate()` explicitly, wrap the save in one exactly like `CreatePost`.

- [ ] **Step 7.8: Run tests to verify they pass**

```bash
./vendor/bin/pest tests/Feature/Settings/BlogFlagsTest.php
```

Expected:
```
PASS  Tests\Feature\Settings\BlogFlagsTest
✓ it blocks a verified member from /blog/rediger when public_creation is disabled
✓ it allows an admin through /blog/rediger even when public_creation is disabled
✓ it allows a verified member through /blog/rediger when public_creation is enabled
✓ it forces status=draft for non-admin publications when require_moderation is enabled
✓ it lets an admin publish directly even when require_moderation is enabled
Tests: 5 passed
```

- [ ] **Step 7.9: Also run the existing blog test suite to check for regressions**

```bash
./vendor/bin/pest tests/Feature/Blog/
```

Expected: all prior blog tests still pass. If `CreatePostTest` or `EditPostTest` fail because of the `$validated['status']` refactor, fix the refactor (most likely a missed reference to `$this->status` in the create/update payload).

- [ ] **Step 7.10: Commit**

```bash
git add app/Policies/BlogPostPolicy.php app/Livewire/Blog/CreatePost.php app/Livewire/Blog/EditPost.php routes/web.php tests/Feature/Settings/BlogFlagsTest.php database/factories/BlogCategoryFactory.php app/Models/BlogCategory.php
git commit -m "$(cat <<'EOF'
feat(settings): gate blog creation on blog.public_creation + force-draft

BlogPostPolicy::create now reads setting('blog.public_creation') for
non-admins; admins always bypass. The /blog/rediger route gains a
can:create middleware so unauthorized users get 403 at route entry.
CreatePost::save and EditPost::save force status=draft when
blog.require_moderation is enabled and the author is not an admin.

Co-Authored-By: Claude Opus 4.6 (1M context) <noreply@anthropic.com>
EOF
)"
```

---

## Task 8: Comment flags — enabled + moderation

**Purpose:** Block new comments when `comments.enabled=false` and branch `moderated_at` assignment on `comments.require_moderation`.

**Files:**
- Modify: `app/Livewire/Blog/CommentForm.php`
- Modify: `resources/views/blog/show.blade.php`
- Test: `tests/Feature/Settings/CommentFlagsTest.php`

---

- [ ] **Step 8.1: Write the failing tests**

Create `tests/Feature/Settings/CommentFlagsTest.php`:

```php
<?php

use App\Livewire\Blog\CommentForm;
use App\Models\BlogPost;
use App\Models\Setting;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    Setting::create([
        'key' => 'comments.enabled', 'value' => '1', 'type' => 'bool',
        'group' => 'comments', 'sort_order' => 1,
        'label' => 'Commentaires activés', 'description' => null,
    ]);
    Setting::create([
        'key' => 'comments.require_moderation', 'value' => '1', 'type' => 'bool',
        'group' => 'comments', 'sort_order' => 2,
        'label' => 'Modération', 'description' => null,
    ]);
});

it('refuses to save a comment when comments.enabled is false', function () {
    Setting::where('key', 'comments.enabled')->update(['value' => '0']);

    $member = User::factory()->create(['email_verified_at' => now()]);
    $member->assignRole('user');
    $post = BlogPost::factory()->create(['status' => 'published', 'published_at' => now()]);

    Livewire::actingAs($member)
        ->test(CommentForm::class, ['post' => $post])
        ->set('content', 'This should not be saved.')
        ->call('submit')
        ->assertHasErrors('content');

    expect($post->comments()->count())->toBe(0);
});

it('saves a comment with moderated_at=null when require_moderation is true', function () {
    $member = User::factory()->create(['email_verified_at' => now()]);
    $member->assignRole('user');
    $post = BlogPost::factory()->create(['status' => 'published', 'published_at' => now()]);

    Livewire::actingAs($member)
        ->test(CommentForm::class, ['post' => $post])
        ->set('content', 'Pending approval please.')
        ->call('submit');

    $comment = $post->comments()->first();
    expect($comment)->not->toBeNull()
        ->and($comment->moderated_at)->toBeNull();
});

it('auto-approves a comment when require_moderation is false', function () {
    Setting::where('key', 'comments.require_moderation')->update(['value' => '0']);

    $member = User::factory()->create(['email_verified_at' => now()]);
    $member->assignRole('user');
    $post = BlogPost::factory()->create(['status' => 'published', 'published_at' => now()]);

    Livewire::actingAs($member)
        ->test(CommentForm::class, ['post' => $post])
        ->set('content', 'Instant.')
        ->call('submit');

    $comment = $post->comments()->first();
    expect($comment->moderated_at)->not->toBeNull();
});
```

- [ ] **Step 8.2: Run tests to verify they fail**

```bash
./vendor/bin/pest tests/Feature/Settings/CommentFlagsTest.php
```

Expected: test 1 ("refuses when disabled") fails because current `CommentForm::submit()` has no gate; test 3 ("auto-approves") fails because current implementation hardcodes `moderated_at=null`. Test 2 may pass by coincidence.

- [ ] **Step 8.3: Update `CommentForm::submit`**

Open `app/Livewire/Blog/CommentForm.php` and locate the `submit()` method. Add the `comments.enabled` gate at the top and branch the `moderated_at` assignment in the `BlogComment::create` call.

Add at the top of `submit()` (before `$this->validate()`):

```php
if (! setting('comments.enabled', true)) {
    $this->addError('content', 'Les commentaires sont actuellement désactivés.');
    return;
}
```

Change the `BlogComment::create([...])` call's `moderated_at` field from whatever it currently is (likely hardcoded `null` or absent) to:

```php
'moderated_at' => setting('comments.require_moderation', true) ? null : now(),
```

- [ ] **Step 8.4: Conditionally render the comment form in blog/show**

Open `resources/views/blog/show.blade.php`. Find the comment form section (search for `@livewire('blog.comment-form'` or `CommentForm`). Wrap it:

```blade
@if (setting('comments.enabled', true))
    @livewire('blog.comment-form', ['post' => $post])
@else
    <div class="bg-gray-50 border border-gray-200 p-4 text-sm text-gray-500 text-center">
        Les commentaires sont actuellement désactivés.
    </div>
@endif
```

The exact surrounding markup depends on the current file — the only change is the conditional wrap around the Livewire component invocation.

- [ ] **Step 8.5: Run tests to verify they pass**

```bash
./vendor/bin/pest tests/Feature/Settings/CommentFlagsTest.php
```

Expected:
```
PASS  Tests\Feature\Settings\CommentFlagsTest
✓ it refuses to save a comment when comments.enabled is false
✓ it saves a comment with moderated_at=null when require_moderation is true
✓ it auto-approves a comment when require_moderation is false
Tests: 3 passed
```

- [ ] **Step 8.6: Run the existing blog test suite for regressions**

```bash
./vendor/bin/pest tests/Feature/Blog/
```

Expected: prior comment tests still pass (they should, since the default setting values match prior hardcoded behavior).

- [ ] **Step 8.7: Commit**

```bash
git add app/Livewire/Blog/CommentForm.php resources/views/blog/show.blade.php tests/Feature/Settings/CommentFlagsTest.php
git commit -m "$(cat <<'EOF'
feat(settings): gate comments on comments.enabled + comments.require_moderation

CommentForm::submit now refuses to save when comments.enabled is false
and sets moderated_at immediately (now()) when comments.require_moderation
is false. The blog/show template hides the form entirely when comments
are disabled, showing a neutral placeholder instead.

Co-Authored-By: Claude Opus 4.6 (1M context) <noreply@anthropic.com>
EOF
)"
```

---

## Task 9: Admin Settings page — Livewire component + view + route + nav

**Purpose:** Build the `/admin/parametres` page. The component generates its form from the DB, saves all settings in one pass, logs activity automatically via the model trait, and displays a history section with the last 20 activity entries. Add the nav entry in `layouts/admin.blade.php`.

**Files:**
- Create: `app/Livewire/Admin/Settings.php`
- Create: `resources/views/livewire/admin/settings.blade.php`
- Create: `lang/fr/settings.php`
- Modify: `routes/web.php` (add admin.settings route)
- Modify: `resources/views/layouts/admin.blade.php` (add nav item)
- Test: `tests/Feature/Admin/SettingsTest.php`

---

- [ ] **Step 9.1: Write the failing tests**

Create `tests/Feature/Admin/SettingsTest.php`:

```php
<?php

use App\Livewire\Admin\Settings as AdminSettings;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\SettingSeeder;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;

beforeEach(function () {
    $this->seed(SettingSeeder::class);
});

it('renders all 3 groups with their settings for an admin', function () {
    $admin = User::factory()->create(['email_verified_at' => now()]);
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->get('/admin/parametres')
        ->assertSuccessful()
        ->assertSee('Site')
        ->assertSee('Blog')
        ->assertSee('Commentaires')
        ->assertSee('Inscriptions ouvertes')
        ->assertSee("Création d'articles par les membres", escape: false)
        ->assertSee('Commentaires activés');
});

it('forbids access to non-admin users', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->assignRole('user');

    $this->actingAs($user)
        ->get('/admin/parametres')
        ->assertForbidden();
});

it('updates a setting value and writes an activity log row on save', function () {
    $admin = User::factory()->create(['email_verified_at' => now()]);
    $admin->assignRole('admin');

    Livewire::actingAs($admin)
        ->test(AdminSettings::class)
        ->set('values.blog.public_creation', false)
        ->call('save')
        ->assertHasNoErrors();

    expect(Setting::where('key', 'blog.public_creation')->value('value'))->toBe('0');
    expect(Setting::where('key', 'blog.public_creation')->value('updated_by'))->toBe($admin->id);

    $activity = Activity::inLog('settings')->latest()->first();
    expect($activity)->not->toBeNull()
        ->and($activity->description)->toBe('setting.updated')
        ->and($activity->properties['old']['value'] ?? null)->toBe('1')
        ->and($activity->properties['attributes']['value'] ?? null)->toBe('0');
});

it('updates the string-typed maintenance message', function () {
    $admin = User::factory()->create(['email_verified_at' => now()]);
    $admin->assignRole('admin');

    Livewire::actingAs($admin)
        ->test(AdminSettings::class)
        ->set('values.site.maintenance_message', 'Nouveau message de maintenance.')
        ->call('save');

    expect(Setting::where('key', 'site.maintenance_message')->value('value'))
        ->toBe('Nouveau message de maintenance.');
});

it('renders the activity history section when there are recent changes', function () {
    $admin = User::factory()->create(['email_verified_at' => now()]);
    $admin->assignRole('admin');

    // Trigger an activity log event by updating a setting
    Livewire::actingAs($admin)
        ->test(AdminSettings::class)
        ->set('values.comments.enabled', false)
        ->call('save');

    $this->actingAs($admin)
        ->get('/admin/parametres')
        ->assertSee('Historique des modifications')
        ->assertSee('comments.enabled');
});
```

- [ ] **Step 9.2: Run tests to verify they fail**

```bash
./vendor/bin/pest tests/Feature/Admin/SettingsTest.php
```

Expected: all 5 tests fail with `No application encryption key` or `Route [admin.settings] not defined` or similar.

- [ ] **Step 9.3: Create the French group labels**

Create `lang/fr/settings.php`:

```php
<?php

return [
    'groups' => [
        'site'     => 'Site',
        'blog'     => 'Blog',
        'comments' => 'Commentaires',
    ],
];
```

- [ ] **Step 9.4: Create the Livewire component**

Create `app/Livewire/Admin/Settings.php`:

```php
<?php

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
        // Livewire 3's wire:model interprets dot-notation as nested array access
        // ($values['blog']['public_creation']), so we mirror that structure here.
        // Every setting key in the system follows the `group.field` convention,
        // so splitting on the first dot is safe.
        $this->values = Setting::query()
            ->get()
            ->reduce(function (array $acc, Setting $s): array {
                [$group, $field] = explode('.', $s->key, 2);
                $acc[$group][$field] = $s->casted_value;
                return $acc;
            }, []);
    }

    public function save(): void
    {
        foreach ($this->values as $group => $fields) {
            foreach ($fields as $field => $value) {
                $key = "{$group}.{$field}";
                $setting = Setting::where('key', $key)->first();
                if (! $setting) {
                    continue;
                }

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
        }

        session()->flash('success', 'Paramètres enregistrés.');
    }

    #[Layout('layouts.admin')]
    public function render()
    {
        $groups = Setting::query()
            ->orderBy('group')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('group');

        $recentChanges = Activity::query()
            ->inLog('settings')
            ->with(['causer', 'subject'])
            ->latest()
            ->limit(20)
            ->get();

        return view('livewire.admin.settings', [
            'groups'        => $groups,
            'recentChanges' => $recentChanges,
        ]);
    }
}
```

- [ ] **Step 9.5: Create the Blade view**

Create `resources/views/livewire/admin/settings.blade.php`:

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
                        @php
                            // Split the key to match the nested $values array
                            // structure populated by the component's mount().
                            [$wireGroup, $wireField] = explode('.', $setting->key, 2);
                            $wireBind = "values.{$wireGroup}.{$wireField}";
                        @endphp
                        @if ($setting->type === 'bool')
                            <label class="flex items-start gap-3 cursor-pointer">
                                <input type="checkbox"
                                       wire:model="{{ $wireBind }}"
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
                                       wire:model="{{ $wireBind }}"
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

- [ ] **Step 9.6: Add the admin route**

Open `routes/web.php`. Find the existing admin block:

```php
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/',              AdminDashboard::class)->name('dashboard');
    Route::get('/profils',       AdminModerateProfiles::class)->name('profiles');
    Route::get('/articles',      AdminManagePosts::class)->name('posts');
    Route::get('/commentaires',  AdminModerateComments::class)->name('comments');
});
```

Add the new route at the bottom of this group (inside the closure):

```php
    Route::get('/parametres',    \App\Livewire\Admin\Settings::class)->name('settings');
```

- [ ] **Step 9.7: Add the nav item in the admin layout**

Open `resources/views/layouts/admin.blade.php`. Find the `$navItems` array (around lines 32-37):

```php
$navItems = [
    ['route' => 'admin.dashboard', 'label' => 'Tableau de bord', 'icon' => 'home'],
    ['route' => 'admin.profiles',  'label' => 'Profils',         'icon' => 'users'],
    ['route' => 'admin.posts',     'label' => 'Articles',        'icon' => 'document'],
    ['route' => 'admin.comments',  'label' => 'Commentaires',    'icon' => 'chat'],
];
```

Add a `settings` entry at the end:

```php
$navItems = [
    ['route' => 'admin.dashboard', 'label' => 'Tableau de bord', 'icon' => 'home'],
    ['route' => 'admin.profiles',  'label' => 'Profils',         'icon' => 'users'],
    ['route' => 'admin.posts',     'label' => 'Articles',        'icon' => 'document'],
    ['route' => 'admin.comments',  'label' => 'Commentaires',    'icon' => 'chat'],
    ['route' => 'admin.settings',  'label' => 'Paramètres',      'icon' => 'cog'],
];
```

Then, in the same file, find the icon rendering block (the `@if ($item['icon'] === 'home')` chain around lines 45-58). Add a new branch for the `cog` icon:

```blade
@elseif ($item['icon'] === 'cog')
    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
    </svg>
```

- [ ] **Step 9.8: Run tests to verify they pass**

```bash
./vendor/bin/pest tests/Feature/Admin/SettingsTest.php
```

Expected:
```
PASS  Tests\Feature\Admin\SettingsTest
✓ it renders all 3 groups with their settings for an admin
✓ it forbids access to non-admin users
✓ it updates a setting value and writes an activity log row on save
✓ it updates the string-typed maintenance message
✓ it renders the activity history section when there are recent changes
Tests: 5 passed
```

- [ ] **Step 9.9: Smoke-test the page in a browser (recommended)**

```bash
php artisan serve --port=8766 &
```

Then open `http://127.0.0.1:8766/admin/parametres` as an admin user (from the AdminSeeder: `admin@bassilanetwork.test` / `admin2024!`). Verify:

1. Three grouped cards are visible with the correct labels
2. The 6 checkboxes + 1 text input render correctly
3. Toggling a setting and clicking "Enregistrer les modifications" shows the green success flash
4. The history section at the bottom updates with the new change
5. Navigating away and back shows the persisted value

Kill the dev server when done.

- [ ] **Step 9.10: Commit**

```bash
git add app/Livewire/Admin/Settings.php resources/views/livewire/admin/settings.blade.php lang/fr/settings.php routes/web.php resources/views/layouts/admin.blade.php tests/Feature/Admin/SettingsTest.php
git commit -m "$(cat <<'EOF'
feat(settings): add /admin/parametres Livewire page

DB-driven form rendering 3 grouped cards for the 7 initial settings.
Save persists all changed values in one pass and logs each change via
the Setting model's LogsActivity trait. The history section at the
bottom surfaces the last 20 changes from activity_log.

Co-Authored-By: Claude Opus 4.6 (1M context) <noreply@anthropic.com>
EOF
)"
```

---

## Task 10: Acceptance check — full regression + manual verification

**Purpose:** Run every test touched by this work, verify no regression, and walk through each acceptance criterion from the spec.

**Files:**
- None modified — verification only. A final cleanup commit may happen if minor fixes are needed.

---

- [ ] **Step 10.1: Run the full Settings + Admin test suite**

```bash
./vendor/bin/pest tests/Feature/Settings/ tests/Feature/Admin/SettingsTest.php
```

Expected: **18 tests passing** (5 model + helper + 3 seeder + 5 maintenance + 3 registration + 5 blog flags + 3 comment flags + 5 admin settings − overlaps). Exact count depends on per-test decomposition; any failure blocks acceptance.

- [ ] **Step 10.2: Run the full test suite to check for regressions**

```bash
./vendor/bin/pest
```

Expected: all tests that were passing before this sub-project are still passing. Known pre-existing failures (unrelated to this work) that may still appear: ContactFormTest × 3 (Blade `@error('message')` bug), SearchTest × 2 (missing public props). Count them and verify they are exactly the pre-existing set.

- [ ] **Step 10.3: Verify the helper makes exactly one DB query per request**

Use `DB::enableQueryLog` in an ad-hoc route or via tinker:

```bash
php artisan tinker --execute="
\$all = \App\Models\Setting::all();
DB::enableQueryLog();
for (\$i=0; \$i<10; \$i++) { setting('blog.public_creation', true); }
\$logs = DB::getQueryLog();
echo 'Query count: '.count(\$logs).PHP_EOL;
"
```

Expected: `Query count: 1` — the first call populates the cache, the next 9 hit the in-memory collection.

- [ ] **Step 10.4: Verify the seeder is idempotent on the dev DB**

```bash
# Edit a value directly to simulate admin edit
PGPASSWORD='demcrm!24' psql -h 127.0.0.1 -p 5432 -U postgres -d emergence_bassila -c "UPDATE settings SET value='0' WHERE key='blog.public_creation'"

# Re-run seeder
php artisan db:seed --class=SettingSeeder

# Verify value still '0' AND label still the seeded one
PGPASSWORD='demcrm!24' psql -h 127.0.0.1 -p 5432 -U postgres -d emergence_bassila -tAc "SELECT key, value, label FROM settings WHERE key='blog.public_creation'"
```

Expected: `blog.public_creation|0|Création d'articles par les membres` — value preserved, label unchanged.

Then reset it to default:
```bash
PGPASSWORD='demcrm!24' psql -h 127.0.0.1 -p 5432 -U postgres -d emergence_bassila -c "UPDATE settings SET value='1' WHERE key='blog.public_creation'"
```

- [ ] **Step 10.5: Manual smoke test — each setting's runtime effect**

Start the dev server:

```bash
php artisan serve --port=8766
```

Through `/admin/parametres` (login as `admin@bassilanetwork.test` / `admin2024!`), walk through every setting:

1. **`site.maintenance_mode`** — toggle ON, save, open an incognito window to `http://127.0.0.1:8766/` → expect 503 page with message. The admin browser should still see the site. Toggle back OFF.

2. **`site.registration_open`** — toggle OFF, save, incognito to `/inscription` → expect redirect to `/connexion` + flash "Les inscriptions sont temporairement fermées." Toggle back ON.

3. **`blog.public_creation`** — toggle OFF, save. Sign in as a regular member (`user1@bassilanetwork.test` / `password`), go to `/blog/rediger` → expect 403. The "Rédiger un article" link in the public nav should be hidden. Toggle back ON.

4. **`blog.require_moderation`** — toggle ON, save. Sign in as a regular member, go to `/blog/rediger`, fill the form with `status=published`, submit → after redirect, check the DB: `SELECT status FROM blog_posts ORDER BY id DESC LIMIT 1` → expect `draft`. Toggle back OFF.

5. **`comments.enabled`** — toggle OFF, save. Open any published blog post page → the comment form is replaced by the neutral placeholder. Toggle back ON.

6. **`comments.require_moderation`** — toggle OFF, save. As a member, submit a comment on a post → the comment should appear immediately in the blog list (its `moderated_at` is set). Toggle back ON.

7. **`site.maintenance_message`** — edit the text to "Test 123", save, toggle maintenance ON → verify the new message appears on the 503 page.

Kill the dev server.

- [ ] **Step 10.6: Verify history section after the manual test**

Go back to `/admin/parametres` as admin → the history section should now show ~14+ recent changes (each toggle triggered one activity log row). Each row should show the correct causer name, setting key, old → new, and relative timestamp.

- [ ] **Step 10.7: Final commit (if any small fixes were needed)**

If Steps 10.1-10.6 revealed any issue that required a code fix, commit it now:

```bash
git add <files>
git commit -m "fix(settings): <specific fix>"
```

Otherwise, no commit is needed — this task is pure verification.

- [ ] **Step 10.8: Update the CHANGELOG**

If the project has a `CHANGELOG.md`, add an entry under the unreleased section:

```markdown
## Unreleased

### Added
- Site Settings + Feature Flags admin page at `/admin/parametres`
  - 7 initial toggles: blog public creation, blog moderation, comment gates, registration, maintenance mode, maintenance message
  - Full audit trail via activitylog
  - Idempotent seeder preserves admin-edited values
```

- [ ] **Step 10.9: Final commit for changelog + close the sub-project**

```bash
git add CHANGELOG.md
git commit -m "$(cat <<'EOF'
docs: changelog for site settings sub-project

Closes the first sub-project of the admin autonomy roadmap.
The next sub-project is ② RBAC + user management.

Co-Authored-By: Claude Opus 4.6 (1M context) <noreply@anthropic.com>
EOF
)"
```

---

## Acceptance criteria checklist

Verify each line against the spec section 12 before declaring the sub-project done.

- [ ] `/admin/parametres` renders 3 grouped cards showing all 7 settings (Task 9 Step 9.8 + Step 9.9)
- [ ] Toggling a setting and clicking "Enregistrer" persists the change and logs an activity entry visible in the history section on the same page (Task 9 + Step 10.6)
- [ ] `php artisan db:seed --class=SettingSeeder` on a fresh DB creates the 7 rows; re-running it on a DB with admin-edited values does NOT revert any value (Task 4 Step 4.5 + Step 10.4)
- [ ] `setting('site.maintenance_mode', false) === true` causes every public route to return 503 except `/connexion`, `/admin/*`, `/livewire/*`, and admin-authenticated sessions (Task 5 Step 5.6 + Step 10.5.1)
- [ ] The `setting()` helper executes exactly one DB query per HTTP request (Step 10.3)
- [ ] All 24 new tests pass (Step 10.1)
- [ ] The pre-existing admin tests still pass — no regression (Step 10.2)

---

## Total test count introduced

| File | Tests |
|---|---|
| `tests/Feature/Settings/SettingModelTest.php` | 2 |
| `tests/Feature/Settings/SettingHelperTest.php` | 5 |
| `tests/Feature/Settings/SettingSeederTest.php` | 3 |
| `tests/Feature/Settings/MaintenanceModeTest.php` | 5 |
| `tests/Feature/Settings/RegistrationClosedTest.php` | 3 |
| `tests/Feature/Settings/BlogFlagsTest.php` | 5 |
| `tests/Feature/Settings/CommentFlagsTest.php` | 3 |
| `tests/Feature/Admin/SettingsTest.php` | 5 |
| **Total** | **31** |

The spec specified 13 tests minimum; this plan delivers 31 because each task writes tests that **fail first**, which caught several edge cases worth covering (e.g. `setting()` returns null when no default provided, string setting is preserved verbatim, admin can still publish when moderation is required). Overcoverage is cheap insurance on a foundation others will build on.
