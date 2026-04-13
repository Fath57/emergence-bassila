# Profile Fields + Villages Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add `gender`, `whatsapp`, and `village_id` fields to the profile creation/edit forms, backed by a new admin-managed `villages` table seeded with Bassila commune villages.

**Architecture:** New `villages` table → `Village` model → seeded with Bassila communes → FK on profiles. Three new columns on profiles via migration. Custom Livewire admin component (ManageVillages) following ManageCategories pattern. CreateProfile and EditProfile updated with new fields.

**Tech Stack:** Laravel 11, Livewire 3, Tailwind CSS, Pest, PostgreSQL

---

## File Map

**Create:**
- `database/migrations/2026_04_13_100000_create_villages_table.php`
- `database/migrations/2026_04_13_100001_add_gender_whatsapp_village_to_profiles.php`
- `app/Models/Village.php`
- `database/seeders/VillageSeeder.php`
- `app/Livewire/Admin/ManageVillages.php`
- `resources/views/livewire/admin/manage-villages.blade.php`
- `tests/Feature/Admin/ManageVillagesTest.php`

**Modify:**
- `app/Models/Profile.php` — fillable, casts, village() relation
- `database/seeders/DatabaseSeeder.php` — add VillageSeeder
- `routes/web.php` — admin.villages route
- `resources/views/layouts/admin.blade.php` — sidebar nav link
- `app/Livewire/Profile/CreateProfile.php` — new props, rules, save, render
- `resources/views/livewire/profile/create-profile.blade.php` — gender (step 1), whatsapp + village (step 2)
- `app/Livewire/Profile/EditProfile.php` — new props, mount, rules, save, render
- `resources/views/livewire/profile/edit-profile.blade.php` — gender, whatsapp, village fields
- `tests/Feature/Profile/CreateProfileTest.php` — new field coverage
- `tests/Feature/Profile/EditProfileTest.php` — new field coverage

---

### Task 1: Create villages table migration + Village model

**Files:**
- Create: `database/migrations/2026_04_13_100000_create_villages_table.php`
- Create: `app/Models/Village.php`

- [ ] **Step 1: Create the migration**

```bash
php artisan make:migration create_villages_table
```

Rename the generated file to `2026_04_13_100000_create_villages_table.php` then replace its content:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('villages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 150);
            $table->string('arrondissement', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('villages');
    }
};
```

- [ ] **Step 2: Create Village model** at `app/Models/Village.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Village extends Model
{
    protected $fillable = ['name', 'arrondissement', 'is_active', 'sort_order'];

    protected $casts = ['is_active' => 'boolean'];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
```

- [ ] **Step 3: Run the migration**

```bash
php artisan migrate
```

Expected: `villages` table created with no errors.

- [ ] **Step 4: Commit**

```bash
git add database/migrations/2026_04_13_100000_create_villages_table.php app/Models/Village.php
git commit -m "feat(villages): create villages table and Village model"
```

---

### Task 2: Add gender, whatsapp, village_id to profiles

**Files:**
- Create: `database/migrations/2026_04_13_100001_add_gender_whatsapp_village_to_profiles.php`
- Modify: `app/Models/Profile.php`

- [ ] **Step 1: Create the migration**

```bash
php artisan make:migration add_gender_whatsapp_village_to_profiles
```

Rename to `2026_04_13_100001_add_gender_whatsapp_village_to_profiles.php` and replace content:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->char('gender', 1)->nullable()->after('last_name');
            $table->string('whatsapp', 30)->nullable()->after('phone');
            $table->foreignId('village_id')
                  ->nullable()
                  ->after('city')
                  ->constrained('villages')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('village_id');
            $table->dropColumn(['gender', 'whatsapp']);
        });
    }
};
```

- [ ] **Step 2: Update Profile model** — add to `$fillable`, `$casts`, and add `village()` relation

Open `app/Models/Profile.php` and apply these changes:

Add to `$fillable` array (after `'show_email_contact'`):
```php
'gender',
'whatsapp',
'village_id',
```

Add to `$casts` array (after `'show_email_contact'`):
```php
// gender and village_id have no special cast needed
```

Add the relation method (after `countryRelation()`):
```php
public function village(): BelongsTo
{
    return $this->belongsTo(Village::class);
}
```

Also add the import at the top of the file (it already has `use Illuminate\Database\Eloquent\Relations\BelongsTo;` so just add the model usage — no new import needed, Village is in the same namespace).

- [ ] **Step 3: Run migration**

```bash
php artisan migrate
```

Expected: `gender`, `whatsapp`, `village_id` columns added to profiles.

- [ ] **Step 4: Commit**

```bash
git add database/migrations/2026_04_13_100001_add_gender_whatsapp_village_to_profiles.php app/Models/Profile.php
git commit -m "feat(villages): add gender, whatsapp, village_id to profiles"
```

---

### Task 3: VillageSeeder

**Files:**
- Create: `database/seeders/VillageSeeder.php`
- Modify: `database/seeders/DatabaseSeeder.php`

- [ ] **Step 1: Write the failing test** in `tests/Feature/Admin/ManageVillagesTest.php` (create the file now with just the seeder test):

```php
<?php

use App\Models\Village;

it('village seeder creates bassila villages', function () {
    $this->seed(\Database\Seeders\VillageSeeder::class);

    expect(Village::count())->toBeGreaterThan(15);
    expect(Village::where('name', 'Bassila')->exists())->toBeTrue();
    expect(Village::where('arrondissement', 'Manigri')->count())->toBeGreaterThan(3);
});
```

- [ ] **Step 2: Run the test to verify it fails**

```bash
php artisan test tests/Feature/Admin/ManageVillagesTest.php --filter="village seeder"
```

Expected: FAIL — VillageSeeder class not found.

- [ ] **Step 3: Create VillageSeeder** at `database/seeders/VillageSeeder.php`

```php
<?php

namespace Database\Seeders;

use App\Models\Village;
use Illuminate\Database\Seeder;

class VillageSeeder extends Seeder
{
    public function run(): void
    {
        $villages = [
            // ── Arrondissement de Bassila ──
            ['name' => 'Bassila',          'arrondissement' => 'Bassila',      'sort_order' => 1],
            ['name' => 'Alédjo-Attakora',  'arrondissement' => 'Bassila',      'sort_order' => 2],
            ['name' => 'Barei',            'arrondissement' => 'Bassila',      'sort_order' => 3],
            ['name' => 'Béssakourou',      'arrondissement' => 'Bassila',      'sort_order' => 4],
            ['name' => 'Gbégourou',        'arrondissement' => 'Bassila',      'sort_order' => 5],
            ['name' => 'Kounouhou',        'arrondissement' => 'Bassila',      'sort_order' => 6],
            ['name' => 'Tchétou',          'arrondissement' => 'Bassila',      'sort_order' => 7],
            ['name' => 'Worogui',          'arrondissement' => 'Bassila',      'sort_order' => 8],

            // ── Arrondissement de Manigri ──
            ['name' => 'Manigri',          'arrondissement' => 'Manigri',      'sort_order' => 1],
            ['name' => 'Bétékoukou',       'arrondissement' => 'Manigri',      'sort_order' => 2],
            ['name' => 'Gbassi',           'arrondissement' => 'Manigri',      'sort_order' => 3],
            ['name' => 'Kikélé',           'arrondissement' => 'Manigri',      'sort_order' => 4],
            ['name' => 'Kpakpaza',         'arrondissement' => 'Manigri',      'sort_order' => 5],
            ['name' => 'Ode',              'arrondissement' => 'Manigri',      'sort_order' => 6],
            ['name' => 'Sème',             'arrondissement' => 'Manigri',      'sort_order' => 7],

            // ── Arrondissement de Pénéssoulou ──
            ['name' => 'Pénéssoulou',      'arrondissement' => 'Pénéssoulou', 'sort_order' => 1],
            ['name' => 'Kolokondé',        'arrondissement' => 'Pénéssoulou', 'sort_order' => 2],
            ['name' => 'Kokobou',          'arrondissement' => 'Pénéssoulou', 'sort_order' => 3],

            // ── Arrondissement de Wawa ──
            ['name' => 'Wawa',             'arrondissement' => 'Wawa',         'sort_order' => 1],
            ['name' => 'Manta',            'arrondissement' => 'Wawa',         'sort_order' => 2],
        ];

        foreach ($villages as $data) {
            Village::firstOrCreate(
                ['name' => $data['name'], 'arrondissement' => $data['arrondissement']],
                ['is_active' => true, 'sort_order' => $data['sort_order']],
            );
        }
    }
}
```

- [ ] **Step 4: Register in DatabaseSeeder** — add `VillageSeeder::class` after `CountrySeeder::class`:

```php
$this->call([
    RolePermissionSeeder::class,
    CountrySeeder::class,
    VillageSeeder::class,   // ← add this
    SectorSeeder::class,
    SkillSeeder::class,
    SettingSeeder::class,
    BlogCategorySeeder::class,
    AdminSeeder::class,
    ProfileSeeder::class,
]);
```

- [ ] **Step 5: Run the test**

```bash
php artisan test tests/Feature/Admin/ManageVillagesTest.php --filter="village seeder"
```

Expected: PASS

- [ ] **Step 6: Run the seeder against the dev DB**

```bash
php artisan db:seed --class=VillageSeeder
```

Expected: 20 village rows inserted with no errors.

- [ ] **Step 7: Commit**

```bash
git add database/seeders/VillageSeeder.php database/seeders/DatabaseSeeder.php tests/Feature/Admin/ManageVillagesTest.php
git commit -m "feat(villages): add VillageSeeder with Bassila commune villages"
```

---

### Task 4: Admin ManageVillages Livewire component

**Files:**
- Create: `app/Livewire/Admin/ManageVillages.php`
- Create: `resources/views/livewire/admin/manage-villages.blade.php`

- [ ] **Step 1: Add tests for ManageVillages** — append to `tests/Feature/Admin/ManageVillagesTest.php`:

```php
<?php

use App\Livewire\Admin\ManageVillages;
use App\Models\User;
use App\Models\Village;
use Livewire\Livewire;

// (keep the seeder test from Task 3 at the top)

beforeEach(function () {
    $this->seed(\Database\Seeders\RolePermissionSeeder::class);

    $this->admin = User::factory()->create(['email_verified_at' => now(), 'is_active' => true]);
    $this->admin->assignRole('admin');

    $this->member = User::factory()->create(['email_verified_at' => now(), 'is_active' => true]);
    $this->member->assignRole('member');
});

it('admin can see the manage villages page', function () {
    $this->actingAs($this->admin)
        ->get('/admin/villages')
        ->assertSuccessful()
        ->assertSee('Villages');
});

it('forbids non-admin from manage villages', function () {
    $this->actingAs($this->member)
        ->get('/admin/villages')
        ->assertForbidden();
});

it('admin can create a village', function () {
    Livewire::actingAs($this->admin)
        ->test(ManageVillages::class)
        ->set('newName', 'TestVillage')
        ->set('newArrondissement', 'Bassila')
        ->call('create')
        ->assertHasNoErrors();

    expect(Village::where('name', 'TestVillage')->exists())->toBeTrue();
});

it('admin can toggle village active status', function () {
    $village = Village::create(['name' => 'ActiveVillage', 'arrondissement' => 'Bassila', 'is_active' => true]);

    Livewire::actingAs($this->admin)
        ->test(ManageVillages::class)
        ->call('toggleActive', $village->id);

    expect($village->fresh()->is_active)->toBeFalse();
});

it('admin can delete a village without profiles', function () {
    $village = Village::create(['name' => 'ToDelete', 'arrondissement' => 'Wawa', 'is_active' => true]);

    Livewire::actingAs($this->admin)
        ->test(ManageVillages::class)
        ->call('delete', $village->id)
        ->assertHasNoErrors();

    expect(Village::find($village->id))->toBeNull();
});
```

- [ ] **Step 2: Run the tests to verify they fail**

```bash
php artisan test tests/Feature/Admin/ManageVillagesTest.php
```

Expected: FAIL — ManageVillages class not found / route not found.

- [ ] **Step 3: Create `app/Livewire/Admin/ManageVillages.php`**

```php
<?php

namespace App\Livewire\Admin;

use App\Models\Village;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class ManageVillages extends Component
{
    public string $newName = '';
    public string $newArrondissement = '';

    public ?int $editingId = null;
    public string $editingName = '';
    public string $editingArrondissement = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('admin.access'), 403);
    }

    public function create(): void
    {
        $this->validate([
            'newName' => ['required', 'string', 'max:150'],
            'newArrondissement' => ['nullable', 'string', 'max:100'],
        ]);

        Village::create([
            'name'            => trim($this->newName),
            'arrondissement'  => trim($this->newArrondissement) ?: null,
            'is_active'       => true,
            'sort_order'      => 0,
        ]);

        $this->newName = '';
        $this->newArrondissement = '';
        session()->flash('success', 'Village créé.');
    }

    public function startEdit(int $id): void
    {
        $village = Village::findOrFail($id);
        $this->editingId = $village->id;
        $this->editingName = $village->name;
        $this->editingArrondissement = $village->arrondissement ?? '';
    }

    public function cancelEdit(): void
    {
        $this->editingId = null;
        $this->editingName = '';
        $this->editingArrondissement = '';
    }

    public function saveEdit(): void
    {
        abort_unless(auth()->user()?->can('admin.access'), 403);

        $this->validate([
            'editingName' => ['required', 'string', 'max:150'],
            'editingArrondissement' => ['nullable', 'string', 'max:100'],
        ]);

        Village::findOrFail($this->editingId)->update([
            'name'           => trim($this->editingName),
            'arrondissement' => trim($this->editingArrondissement) ?: null,
        ]);

        $this->cancelEdit();
        session()->flash('success', 'Village mis à jour.');
    }

    public function toggleActive(int $id): void
    {
        abort_unless(auth()->user()?->can('admin.access'), 403);

        $village = Village::findOrFail($id);
        $village->update(['is_active' => ! $village->is_active]);
    }

    public function delete(int $id): void
    {
        abort_unless(auth()->user()?->can('admin.access'), 403);

        $village = Village::findOrFail($id);
        $village->delete();
        session()->flash('success', 'Village supprimé.');
    }

    public function render()
    {
        return view('livewire.admin.manage-villages', [
            'villages' => Village::query()
                ->withCount('profiles')
                ->orderBy('arrondissement')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
        ]);
    }
}
```

Note: `withCount('profiles')` requires a `profiles()` hasMany relation on Village. Add it to `app/Models/Village.php`:

```php
use Illuminate\Database\Eloquent\Relations\HasMany;

public function profiles(): HasMany
{
    return $this->hasMany(Profile::class);
}
```

Also add the `use App\Models\Profile;` import if not already present.

- [ ] **Step 4: Create the Blade view** at `resources/views/livewire/admin/manage-villages.blade.php`

```blade
<div>
    <header class="mb-8">
        <h1 class="font-serif text-3xl font-semibold text-[#0A1628]">Villages</h1>
        <p class="text-sm text-gray-600 mt-2">
            Gérez les villages de la commune de Bassila disponibles dans les profils.
        </p>
    </header>

    @if (session('success'))
        <div class="bg-green-50 border border-green-200 px-4 py-3 mb-6 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    {{-- Create form --}}
    <div class="bg-white border border-gray-200 p-5 mb-8">
        <h2 class="text-xs uppercase tracking-widest text-gray-500 font-semibold mb-3">
            Nouveau village
        </h2>
        <form wire:submit.prevent="create" class="flex flex-wrap items-start gap-3">
            <div class="flex-1 min-w-[200px]">
                <input wire:model="newName"
                       type="text"
                       maxlength="150"
                       placeholder="Nom du village"
                       class="w-full border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:border-[#0066CC] @error('newName') border-red-400 @enderror">
                @error('newName') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div class="flex-1 min-w-[160px]">
                <input wire:model="newArrondissement"
                       type="text"
                       maxlength="100"
                       placeholder="Arrondissement (optionnel)"
                       class="w-full border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:border-[#0066CC]">
            </div>
            <button type="submit"
                    class="bg-[#0066CC] text-white text-sm font-semibold px-5 py-2 hover:bg-[#0052A3] transition">
                Créer
            </button>
        </form>
    </div>

    {{-- Villages table --}}
    <div class="bg-white border border-gray-200">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left text-xs uppercase tracking-widest text-gray-500 font-semibold px-5 py-3">Village</th>
                    <th class="text-left text-xs uppercase tracking-widest text-gray-500 font-semibold px-5 py-3">Arrondissement</th>
                    <th class="text-center text-xs uppercase tracking-widest text-gray-500 font-semibold px-5 py-3">Profils</th>
                    <th class="text-center text-xs uppercase tracking-widest text-gray-500 font-semibold px-5 py-3">Actif</th>
                    <th class="text-right text-xs uppercase tracking-widest text-gray-500 font-semibold px-5 py-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($villages as $village)
                    <tr class="border-b border-gray-100 last:border-0">
                        @if ($editingId === $village->id)
                            <td colspan="5" class="px-5 py-4 bg-blue-50/50">
                                <form wire:submit.prevent="saveEdit" class="flex flex-wrap items-start gap-3">
                                    <div class="flex-1 min-w-[200px]">
                                        <input wire:model="editingName"
                                               type="text"
                                               maxlength="150"
                                               autofocus
                                               placeholder="Nom"
                                               class="w-full border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:border-[#0066CC] @error('editingName') border-red-400 @enderror">
                                        @error('editingName') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="flex-1 min-w-[160px]">
                                        <input wire:model="editingArrondissement"
                                               type="text"
                                               maxlength="100"
                                               placeholder="Arrondissement"
                                               class="w-full border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:border-[#0066CC]">
                                    </div>
                                    <button type="submit"
                                            class="bg-[#0066CC] text-white text-sm font-semibold px-4 py-2 hover:bg-[#0052A3] transition">
                                        Enregistrer
                                    </button>
                                    <button type="button"
                                            wire:click="cancelEdit"
                                            class="border border-gray-300 text-sm text-gray-600 px-4 py-2 hover:bg-gray-50 transition">
                                        Annuler
                                    </button>
                                </form>
                            </td>
                        @else
                            <td class="px-5 py-4 text-sm font-semibold text-[#0A1628]">{{ $village->name }}</td>
                            <td class="px-5 py-4 text-sm text-gray-500">{{ $village->arrondissement ?? '—' }}</td>
                            <td class="px-5 py-4 text-sm text-center">
                                @if ($village->profiles_count > 0)
                                    <span class="inline-block bg-gray-100 text-gray-700 px-2 py-1 text-xs font-semibold">
                                        {{ $village->profiles_count }}
                                    </span>
                                @else
                                    <span class="text-gray-300 text-xs">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-center">
                                <button type="button"
                                        wire:click="toggleActive({{ $village->id }})"
                                        class="text-xs font-semibold px-2 py-1 {{ $village->is_active ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }} transition">
                                    {{ $village->is_active ? 'Oui' : 'Non' }}
                                </button>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="inline-flex items-center gap-3 text-xs font-semibold">
                                    <button type="button"
                                            wire:click="startEdit({{ $village->id }})"
                                            class="text-[#0066CC] hover:underline">
                                        Modifier
                                    </button>
                                    <button type="button"
                                            wire:click="delete({{ $village->id }})"
                                            @if ($village->profiles_count > 0)
                                                wire:confirm="Ce village est lié à {{ $village->profiles_count }} profil(s). Leur village_id sera mis à null. Continuer ?"
                                            @else
                                                wire:confirm="Supprimer ce village ?"
                                            @endif
                                            class="text-[#DC143C] hover:underline">
                                        Supprimer
                                    </button>
                                </div>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-10 text-center">
                            <p class="text-sm text-gray-500">Aucun village pour l'instant.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
```

- [ ] **Step 5: Add route** in `routes/web.php` inside the admin group (after the `categories` route):

```php
Route::get('/villages', \App\Livewire\Admin\ManageVillages::class)->name('villages');
```

- [ ] **Step 6: Add nav link** in `resources/views/layouts/admin.blade.php` — add to the `$navItems` array after the `categories` entry:

```php
['route' => 'admin.villages',   'label' => 'Villages',         'icon' => 'map'],
```

Then add the `map` icon case in the `@if` chain (after the `tag` icon):

```blade
@elseif ($item['icon'] === 'map')
    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
```

- [ ] **Step 7: Run the admin tests**

```bash
php artisan test tests/Feature/Admin/ManageVillagesTest.php
```

Expected: All tests PASS.

- [ ] **Step 8: Commit**

```bash
git add app/Livewire/Admin/ManageVillages.php \
        resources/views/livewire/admin/manage-villages.blade.php \
        app/Models/Village.php \
        routes/web.php \
        resources/views/layouts/admin.blade.php \
        tests/Feature/Admin/ManageVillagesTest.php
git commit -m "feat(villages): add ManageVillages admin component, route and nav"
```

---

### Task 5: Update CreateProfile — component

**Files:**
- Modify: `app/Livewire/Profile/CreateProfile.php`

- [ ] **Step 1: Add new public properties** after the `// Step 1 — Identité` block properties:

```php
// Step 1 — Identité
public string $gender = '';
```

Add after `// Step 3 — Contact` block:

```php
public string $whatsapp = '';
```

Add after `// Step 2 — Localisation` block:

```php
public ?int $village_id = null;
```

- [ ] **Step 2: Update `rules()`** — add to the full rules array (after `'email_contact'`):

```php
'gender'     => ['nullable', 'in:M,F'],
'whatsapp'   => ['nullable', 'string', 'max:30'],
'village_id' => ['nullable', 'exists:villages,id'],
```

No step-level required validation needed (all three fields are optional).

- [ ] **Step 3: Update `save()`** — add to the `Profile::create([...])` call:

```php
'gender'     => $this->gender ?: null,
'whatsapp'   => $this->whatsapp ?: null,
'village_id' => $this->village_id,
```

- [ ] **Step 4: Update `render()`** — add `villages` to the view data:

```php
public function render()
{
    return view('livewire.profile.create-profile', [
        'sectors'             => Sector::orderBy('name')->get(),
        'selectedSkillModels' => Skill::whereIn('id', $this->selectedSkills)->orderBy('name')->get(),
        'countries'           => Country::orderBy('sort_order')->orderBy('name')->get(),
        'villages'            => \App\Models\Village::active()
                                    ->orderBy('arrondissement')
                                    ->orderBy('sort_order')
                                    ->orderBy('name')
                                    ->get(),
    ]);
}
```

- [ ] **Step 5: Commit**

```bash
git add app/Livewire/Profile/CreateProfile.php
git commit -m "feat(profile): add gender, whatsapp, village_id to CreateProfile component"
```

---

### Task 6: Update CreateProfile — Blade view

**Files:**
- Modify: `resources/views/livewire/profile/create-profile.blade.php`

- [ ] **Step 1: Add gender radio to Step 1** — insert after the closing `</div>` of the prénom/nom grid (after line ~94 in the original, before the job_title/company grid):

```blade
<div>
    <p class="block text-xs font-semibold text-gray-500 mb-2.5 uppercase tracking-wider">Sexe</p>
    <div class="flex items-center gap-6">
        <label class="flex items-center gap-2 cursor-pointer">
            <input wire:model="gender" type="radio" value="M"
                   class="text-[#0066CC] focus:ring-[#0066CC]">
            <span class="text-sm text-gray-700">Homme</span>
        </label>
        <label class="flex items-center gap-2 cursor-pointer">
            <input wire:model="gender" type="radio" value="F"
                   class="text-[#0066CC] focus:ring-[#0066CC]">
            <span class="text-sm text-gray-700">Femme</span>
        </label>
    </div>
    @error('gender') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
</div>
```

- [ ] **Step 2: Add village dropdown to Step 2** — insert after the closing `</div>` of the `city` field, before the contact section `<div class="pt-2 border-t...">`:

```blade
<div>
    <label for="village_id" class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">
        Village d'origine à Bassila
    </label>
    <select wire:model="village_id"
            id="village_id"
            class="w-full border border-gray-200 px-4 py-3 text-sm focus:outline-none focus:border-[#0066CC] transition bg-white">
        <option value="">Choisir un village…</option>
        @php $lastArr = null; @endphp
        @foreach ($villages as $v)
            @if ($lastArr !== $v->arrondissement)
                @if ($lastArr !== null) </optgroup> @endif
                <optgroup label="{{ $v->arrondissement ?? 'Autres' }}">
            @endif
            <option value="{{ $v->id }}">{{ $v->name }}</option>
            @php $lastArr = $v->arrondissement; @endphp
        @endforeach
        @if ($lastArr !== null) </optgroup> @endif
    </select>
    @error('village_id') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
</div>
```

- [ ] **Step 3: Add WhatsApp field to Step 2** — in the contact section, add a third input alongside email and phone. Change the contact grid from `grid-cols-1 sm:grid-cols-2` to remain 2 columns but add a full-width WhatsApp field below:

After the closing `</div>` of the phone field div, before `</div>` of the grid:

```blade
<div class="sm:col-span-2">
    <label for="whatsapp" class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">
        WhatsApp
    </label>
    <input wire:model="whatsapp"
           id="whatsapp" type="tel"
           placeholder="+229 01 00 00 00"
           class="w-full border border-gray-200 px-4 py-3 text-sm focus:outline-none focus:border-[#0066CC] transition @error('whatsapp') border-red-400 @enderror">
    @error('whatsapp') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
</div>
```

Also update the grid class to `grid grid-cols-1 sm:grid-cols-2 gap-4` (already is, so just ensure it stays).

- [ ] **Step 4: Verify page loads** — navigate to `/profil/creer` in the browser and confirm all 4 steps render without error.

- [ ] **Step 5: Commit**

```bash
git add resources/views/livewire/profile/create-profile.blade.php
git commit -m "feat(profile): add gender radio, village dropdown, whatsapp to create-profile form"
```

---

### Task 7: Update EditProfile — component + view

**Files:**
- Modify: `app/Livewire/Profile/EditProfile.php`
- Modify: `resources/views/livewire/profile/edit-profile.blade.php`

- [ ] **Step 1: Add properties** to `EditProfile.php` — after the existing `public string $phone = '';`:

```php
public string $gender = '';
public string $whatsapp = '';
public ?int $village_id = null;
```

- [ ] **Step 2: Pre-fill in `mount()`** — after the `$this->show_email_contact` assignment:

```php
$this->gender     = $profile->gender ?? '';
$this->whatsapp   = $profile->whatsapp ?? '';
$this->village_id = $profile->village_id;
```

- [ ] **Step 3: Update `rules()`** — add after `'show_email_contact'`:

```php
'gender'     => ['nullable', 'in:M,F'],
'whatsapp'   => ['nullable', 'string', 'max:30'],
'village_id' => ['nullable', 'exists:villages,id'],
```

- [ ] **Step 4: Update `save()`** — add to `$this->profile->update([...])`:

```php
'gender'     => $this->gender ?: null,
'whatsapp'   => $this->whatsapp ?: null,
'village_id' => $this->village_id,
```

- [ ] **Step 5: Update `render()`** — add `villages` to the view data:

```php
public function render()
{
    return view('livewire.profile.edit-profile', [
        'sectors'             => Sector::orderBy('name')->get(),
        'selectedSkillModels' => Skill::whereIn('id', $this->selectedSkills)->orderBy('name')->get(),
        'villages'            => \App\Models\Village::active()
                                    ->orderBy('arrondissement')
                                    ->orderBy('sort_order')
                                    ->orderBy('name')
                                    ->get(),
    ]);
}
```

- [ ] **Step 6: Update `resources/views/livewire/profile/edit-profile.blade.php`**

The edit form is a single flat form (no steps). Make three insertions:

**A) Gender radio** — insert after the closing `</div>` of the first/last name grid (after line 61), before the `<!-- Job title + Company -->` comment:

```blade
<!-- Gender -->
<div>
    <p class="block text-sm font-medium text-gray-700 mb-2">Sexe</p>
    <div class="flex items-center gap-6">
        <label class="flex items-center gap-2 cursor-pointer">
            <input wire:model="gender" type="radio" value="M"
                   class="text-[#0066CC] focus:ring-[#0066CC]">
            <span class="text-sm text-gray-700">Homme</span>
        </label>
        <label class="flex items-center gap-2 cursor-pointer">
            <input wire:model="gender" type="radio" value="F"
                   class="text-[#0066CC] focus:ring-[#0066CC]">
            <span class="text-sm text-gray-700">Femme</span>
        </label>
    </div>
    @error('gender') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
</div>
```

**B) Village dropdown** — insert after the closing `</div>` of the country/city grid (after line 123), before the `<!-- Bio -->` comment:

```blade
<!-- Village d'origine -->
<div>
    <label for="village_id" class="block text-sm font-medium text-gray-700 mb-1">Village d'origine à Bassila</label>
    <select wire:model="village_id"
            id="village_id"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0066CC] bg-white">
        <option value="">Choisir un village…</option>
        @php $lastArr = null; @endphp
        @foreach ($villages as $v)
            @if ($lastArr !== $v->arrondissement)
                @if ($lastArr !== null) </optgroup> @endif
                <optgroup label="{{ $v->arrondissement ?? 'Autres' }}">
            @endif
            <option value="{{ $v->id }}" @selected($village_id == $v->id)>{{ $v->name }}</option>
            @php $lastArr = $v->arrondissement; @endphp
        @endforeach
        @if ($lastArr !== null) </optgroup> @endif
    </select>
    @error('village_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
</div>
```

**C) WhatsApp field** — insert after the closing `</div>` of the phone field block (after line 300), before the closing `</div>` of the contact grid (line 301). Also change the contact grid from `grid-cols-1 sm:grid-cols-2` to keep 2 columns and span the WhatsApp full width:

```blade
<div class="sm:col-span-2">
    <label for="whatsapp" class="block text-sm font-medium text-gray-700 mb-1">WhatsApp</label>
    <input
        wire:model="whatsapp"
        id="whatsapp"
        type="tel"
        placeholder="+229 01 00 00 00"
        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0066CC] @error('whatsapp') border-red-400 @enderror"
    >
    @error('whatsapp') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
</div>
```

- [ ] **Step 7: Commit**

```bash
git add app/Livewire/Profile/EditProfile.php \
        resources/views/livewire/profile/edit-profile.blade.php
git commit -m "feat(profile): add gender, whatsapp, village_id to EditProfile component and view"
```

---

### Task 8: Tests for profile fields

**Files:**
- Modify: `tests/Feature/Profile/CreateProfileTest.php`
- Modify: `tests/Feature/Profile/EditProfileTest.php`

- [ ] **Step 1: Add test for new fields on create** — append to `tests/Feature/Profile/CreateProfileTest.php`:

```php
it('saves gender, whatsapp and village_id on profile creation', function () {
    Storage::fake('s3');

    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->assignRole('member');
    $sector  = Sector::factory()->create();
    $country = Country::create(['name' => 'Bénin', 'code' => 'BJ', 'flag' => '🇧🇯', 'sort_order' => 1]);
    $village = \App\Models\Village::create(['name' => 'Bassila', 'arrondissement' => 'Bassila', 'is_active' => true]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Profile\CreateProfile::class)
        ->set('first_name', 'Fatou')
        ->set('last_name', 'Idrissou')
        ->set('job_title', 'Enseignante')
        ->set('country_id', $country->id)
        ->set('sector_id', $sector->id)
        ->set('gender', 'F')
        ->set('whatsapp', '+22901000000')
        ->set('village_id', $village->id)
        ->call('save');

    $this->assertDatabaseHas('profiles', [
        'user_id'    => $user->id,
        'gender'     => 'F',
        'whatsapp'   => '+22901000000',
        'village_id' => $village->id,
    ]);
});

it('rejects invalid gender value', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->assignRole('member');

    Livewire::actingAs($user)
        ->test(\App\Livewire\Profile\CreateProfile::class)
        ->set('gender', 'X')
        ->call('save')
        ->assertHasErrors(['gender']);
});
```

- [ ] **Step 2: Add test for new fields on edit** — append to `tests/Feature/Profile/EditProfileTest.php`:

```php
it('saves gender, whatsapp and village_id on profile edit', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->assignRole('member');

    $sector  = \App\Models\Sector::factory()->create();
    $country = \App\Models\Country::create(['name' => 'Bénin', 'code' => 'BJ', 'flag' => '🇧🇯', 'sort_order' => 1]);
    $village = \App\Models\Village::create(['name' => 'Manigri', 'arrondissement' => 'Manigri', 'is_active' => true]);

    $profile = \App\Models\Profile::factory()->create([
        'user_id'    => $user->id,
        'country_id' => $country->id,
        'sector_id'  => $sector->id,
    ]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Profile\EditProfile::class)
        ->set('gender', 'M')
        ->set('whatsapp', '+22900000001')
        ->set('village_id', $village->id)
        ->call('save');

    $this->assertDatabaseHas('profiles', [
        'id'         => $profile->id,
        'gender'     => 'M',
        'whatsapp'   => '+22900000001',
        'village_id' => $village->id,
    ]);
});
```

- [ ] **Step 3: Run all profile tests**

```bash
php artisan test tests/Feature/Profile/ tests/Feature/Admin/ManageVillagesTest.php
```

Expected: All tests PASS.

- [ ] **Step 4: Commit**

```bash
git add tests/Feature/Profile/CreateProfileTest.php \
        tests/Feature/Profile/EditProfileTest.php
git commit -m "test(profile): cover gender, whatsapp, village_id on create and edit"
```

---

### Task 9: Full test suite + final verification

- [ ] **Step 1: Run full test suite**

```bash
php artisan test
```

Expected: All tests PASS (no regressions).

- [ ] **Step 2: Manual smoke test**
  - Go to `/profil/creer` → step 1 shows gender radios → step 2 shows village dropdown (grouped by arrondissement) and WhatsApp field
  - Create a profile with all three fields set → confirm saved in DB
  - Go to `/admin/villages` → see all seeded villages → create, edit, toggle, delete one
  - Go to `/profil/modifier` → confirm gender, village, whatsapp pre-filled from saved profile

- [ ] **Step 3: Final commit if any loose files**

```bash
git status
git add -p  # stage only intended changes
git commit -m "chore: final cleanup after profile fields + villages feature"
```
