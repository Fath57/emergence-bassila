# Profile Contact Visibility Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Allow members to control whether their phone number and contact email are visible on their public profile page.

**Architecture:** Add two boolean columns (`show_phone`, `show_email_contact`) to the `profiles` table defaulting to `false` (privacy-first). Wire these into CreateProfile and EditProfile Livewire components with toggle checkboxes in the forms. Conditionally render the fields on the public profile page.

**Tech Stack:** Laravel 11, Livewire 3, Tailwind CSS, Pest

---

## File Map

| File | Change |
|------|--------|
| `database/migrations/2026_04_13_000000_add_visibility_flags_to_profiles.php` | New migration — two boolean columns |
| `app/Models/Profile.php` | Add `show_phone`, `show_email_contact` to `$fillable` and `$casts` |
| `app/Livewire/Profile/CreateProfile.php` | Add `show_phone`, `show_email_contact` props + rules + save |
| `resources/views/livewire/profile/create-profile.blade.php` | Add toggle checkboxes in step 2 next to phone/email fields |
| `app/Livewire/Profile/EditProfile.php` | Add `phone`, `email_contact`, `show_phone`, `show_email_contact` props + rules + mount + save |
| `resources/views/livewire/profile/edit-profile.blade.php` | Add phone/email section with visibility toggles |
| `resources/views/profile/show.blade.php` | Display phone/email when `show_*` flag is true |
| `tests/Feature/Profile/EditProfileTest.php` | Add tests for saving phone/email/visibility |
| `tests/Feature/Profile/ProfileVisibilityTest.php` | New — tests for show/hide behaviour on profile page |

---

### Task 1: Migration — add visibility flag columns

**Files:**
- Create: `database/migrations/2026_04_13_000000_add_visibility_flags_to_profiles.php`

- [ ] **Step 1: Write the migration**

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
            $table->boolean('show_phone')->default(false)->after('phone');
            $table->boolean('show_email_contact')->default(false)->after('email_contact');
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn(['show_phone', 'show_email_contact']);
        });
    }
};
```

- [ ] **Step 2: Run the migration**

```bash
php artisan migrate
```

Expected: `Migrating: 2026_04_13_000000_add_visibility_flags_to_profiles` then `Migrated`.

- [ ] **Step 3: Commit**

```bash
git add database/migrations/2026_04_13_000000_add_visibility_flags_to_profiles.php
git commit -m "feat(profiles): add show_phone and show_email_contact visibility columns"
```

---

### Task 2: Update Profile model

**Files:**
- Modify: `app/Models/Profile.php`

- [ ] **Step 1: Add `show_phone` and `show_email_contact` to `$fillable`**

In `app/Models/Profile.php`, replace the `$fillable` array:

```php
protected $fillable = [
    'user_id',
    'first_name',
    'last_name',
    'bio',
    'avatar_url',
    'city',
    'country',
    'country_id',
    'job_title',
    'company',
    'sector_id',
    'education_start_year',
    'education_end_year',
    'linkedin_url',
    'portfolio_url',
    'phone',
    'show_phone',
    'email_contact',
    'show_email_contact',
    'is_verified',
    'verified_at',
];
```

- [ ] **Step 2: Add casts for the two new boolean columns**

In `app/Models/Profile.php`, replace `$casts`:

```php
protected $casts = [
    'is_verified'        => 'boolean',
    'verified_at'        => 'datetime',
    'show_phone'         => 'boolean',
    'show_email_contact' => 'boolean',
];
```

- [ ] **Step 3: Commit**

```bash
git add app/Models/Profile.php
git commit -m "feat(profiles): add show_phone/show_email_contact to model fillable and casts"
```

---

### Task 3: Write failing tests for EditProfile visibility

**Files:**
- Modify: `tests/Feature/Profile/EditProfileTest.php`
- Create: `tests/Feature/Profile/ProfileVisibilityTest.php`

- [ ] **Step 1: Add test for saving phone/email/visibility in EditProfile**

Append to `tests/Feature/Profile/EditProfileTest.php`:

```php
it('saves phone, email_contact and visibility flags', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->assignRole('member');
    $profile = Profile::factory()->create(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Profile\EditProfile::class)
        ->set('phone', '+229 01 23 45 67')
        ->set('show_phone', true)
        ->set('email_contact', 'pro@example.com')
        ->set('show_email_contact', false)
        ->call('save');

    $this->assertDatabaseHas('profiles', [
        'id'                 => $profile->id,
        'phone'              => '+229 01 23 45 67',
        'show_phone'         => true,
        'email_contact'      => 'pro@example.com',
        'show_email_contact' => false,
    ]);
});
```

- [ ] **Step 2: Create ProfileVisibilityTest**

Create `tests/Feature/Profile/ProfileVisibilityTest.php`:

```php
<?php

use App\Models\Profile;
use App\Models\User;

it('shows phone on profile page when show_phone is true', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $profile = Profile::factory()->create([
        'user_id'    => $user->id,
        'phone'      => '+229 01 23 45 67',
        'show_phone' => true,
        'is_verified' => true,
    ]);

    $this->get(route('profile.show', $profile))
        ->assertSee('+229 01 23 45 67');
});

it('hides phone on profile page when show_phone is false', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $profile = Profile::factory()->create([
        'user_id'    => $user->id,
        'phone'      => '+229 01 23 45 67',
        'show_phone' => false,
        'is_verified' => true,
    ]);

    $this->get(route('profile.show', $profile))
        ->assertDontSee('+229 01 23 45 67');
});

it('shows email_contact on profile page when show_email_contact is true', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $profile = Profile::factory()->create([
        'user_id'            => $user->id,
        'email_contact'      => 'pro@example.com',
        'show_email_contact' => true,
        'is_verified'        => true,
    ]);

    $this->get(route('profile.show', $profile))
        ->assertSee('pro@example.com');
});

it('hides email_contact on profile page when show_email_contact is false', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $profile = Profile::factory()->create([
        'user_id'            => $user->id,
        'email_contact'      => 'pro@example.com',
        'show_email_contact' => false,
        'is_verified'        => true,
    ]);

    $this->get(route('profile.show', $profile))
        ->assertDontSee('pro@example.com');
});
```

- [ ] **Step 3: Run failing tests**

```bash
php artisan test tests/Feature/Profile/EditProfileTest.php tests/Feature/Profile/ProfileVisibilityTest.php --filter="saves phone|shows phone|hides phone|shows email|hides email"
```

Expected: FAIL — properties not yet wired in EditProfile, columns not rendered in show view.

- [ ] **Step 4: Commit failing tests**

```bash
git add tests/Feature/Profile/EditProfileTest.php tests/Feature/Profile/ProfileVisibilityTest.php
git commit -m "test(profiles): add failing tests for contact visibility flags"
```

---

### Task 4: Wire EditProfile component

**Files:**
- Modify: `app/Livewire/Profile/EditProfile.php`

- [ ] **Step 1: Add properties**

In `EditProfile.php`, after `public string $portfolio_url = '';` add:

```php
public string $phone = '';
public string $email_contact = '';
public bool $show_phone = false;
public bool $show_email_contact = false;
```

- [ ] **Step 2: Add validation rules**

In the `rules()` method, add these entries:

```php
'phone'              => ['nullable', 'string', 'max:30'],
'email_contact'      => ['nullable', 'email', 'max:255'],
'show_phone'         => ['boolean'],
'show_email_contact' => ['boolean'],
```

- [ ] **Step 3: Load values in mount()**

In `mount()`, after `$this->portfolio_url = $profile->portfolio_url ?? '';` add:

```php
$this->phone              = $profile->phone ?? '';
$this->email_contact      = $profile->email_contact ?? '';
$this->show_phone         = (bool) ($profile->show_phone ?? false);
$this->show_email_contact = (bool) ($profile->show_email_contact ?? false);
```

- [ ] **Step 4: Persist in save()**

In `save()`, inside the `$this->profile->update([...])` array, add:

```php
'phone'              => $this->phone ?: null,
'email_contact'      => $this->email_contact ?: null,
'show_phone'         => $this->show_phone,
'show_email_contact' => $this->show_email_contact,
```

- [ ] **Step 5: Run EditProfile tests**

```bash
php artisan test tests/Feature/Profile/EditProfileTest.php
```

Expected: all PASS.

- [ ] **Step 6: Commit**

```bash
git add app/Livewire/Profile/EditProfile.php
git commit -m "feat(profiles): wire phone/email and visibility flags in EditProfile component"
```

---

### Task 5: Update edit-profile.blade.php

**Files:**
- Modify: `resources/views/livewire/profile/edit-profile.blade.php`

- [ ] **Step 1: Add phone/email section with visibility toggles**

In `edit-profile.blade.php`, locate the `<!-- Links -->` section (around line 265). Insert this new section **before** it:

```html
<!-- Contact info -->
<div class="space-y-4">
    <p class="text-sm font-semibold text-gray-700">Coordonnées de contact <span class="text-xs font-normal text-gray-400">(optionnel)</span></p>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label for="email_contact" class="block text-sm font-medium text-gray-700 mb-1">Email de contact</label>
            <input
                wire:model="email_contact"
                id="email_contact"
                type="email"
                placeholder="contact@exemple.com"
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0066CC] @error('email_contact') border-red-400 @enderror"
            >
            @error('email_contact') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            <label class="flex items-center gap-2 mt-2 cursor-pointer select-none">
                <input type="checkbox" wire:model="show_email_contact" class="w-4 h-4 rounded text-[#0066CC] focus:ring-[#0066CC]">
                <span class="text-xs text-gray-500">Afficher sur mon profil public</span>
            </label>
        </div>

        <div>
            <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Téléphone / WhatsApp</label>
            <input
                wire:model="phone"
                id="phone"
                type="tel"
                placeholder="+229 01 00 00 00"
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0066CC] @error('phone') border-red-400 @enderror"
            >
            @error('phone') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            <label class="flex items-center gap-2 mt-2 cursor-pointer select-none">
                <input type="checkbox" wire:model="show_phone" class="w-4 h-4 rounded text-[#0066CC] focus:ring-[#0066CC]">
                <span class="text-xs text-gray-500">Afficher sur mon profil public</span>
            </label>
        </div>
    </div>
</div>
```

- [ ] **Step 2: Commit**

```bash
git add resources/views/livewire/profile/edit-profile.blade.php
git commit -m "feat(profiles): add phone/email fields with visibility toggles to edit-profile form"
```

---

### Task 6: Wire CreateProfile visibility toggles

**Files:**
- Modify: `app/Livewire/Profile/CreateProfile.php`
- Modify: `resources/views/livewire/profile/create-profile.blade.php`

- [ ] **Step 1: Add props to CreateProfile component**

In `CreateProfile.php`, in the "Step 3 — Contact" section, add:

```php
// Step 3 — Contact
public string $phone = '';
public string $email_contact = '';
public bool $show_phone = false;
public bool $show_email_contact = false;
```

(Replace the existing `public string $phone = '';` and `public string $email_contact = '';` lines.)

- [ ] **Step 2: Add to stepRules() for step 2**

In `stepRules()`, in the `2 =>` case, add:

```php
'show_phone'         => ['boolean'],
'show_email_contact' => ['boolean'],
```

- [ ] **Step 3: Add to full rules()**

In `rules()`, add:

```php
'show_phone'         => ['boolean'],
'show_email_contact' => ['boolean'],
```

- [ ] **Step 4: Persist in save()**

In `save()`, inside `Profile::create([...])`, add:

```php
'show_phone'         => $this->show_phone,
'show_email_contact' => $this->show_email_contact,
```

- [ ] **Step 5: Add checkboxes to create-profile.blade.php step 2**

In `create-profile.blade.php`, locate the phone input block (around line 195). After the `@error('email_contact')` line, add a toggle. The two input divs in the contact grid should become:

```html
<div>
    <label for="email_contact" class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">
        Email de contact
    </label>
    <input wire:model="email_contact"
           id="email_contact" type="email"
           placeholder="contact@exemple.com"
           class="w-full border border-gray-200 px-4 py-3 text-sm focus:outline-none focus:border-[#0066CC] transition @error('email_contact') border-red-400 @enderror">
    @error('email_contact') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
    <label class="flex items-center gap-2 mt-2 cursor-pointer select-none">
        <input type="checkbox" wire:model="show_email_contact" class="w-4 h-4 rounded text-[#0066CC] focus:ring-[#0066CC]">
        <span class="text-xs text-gray-400">Visible sur mon profil</span>
    </label>
</div>
<div>
    <label for="phone" class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">
        Téléphone / WhatsApp
    </label>
    <input wire:model="phone"
           id="phone" type="tel"
           placeholder="+229 01 00 00 00"
           class="w-full border border-gray-200 px-4 py-3 text-sm focus:outline-none focus:border-[#0066CC] transition @error('phone') border-red-400 @enderror">
    @error('phone') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
    <label class="flex items-center gap-2 mt-2 cursor-pointer select-none">
        <input type="checkbox" wire:model="show_phone" class="w-4 h-4 rounded text-[#0066CC] focus:ring-[#0066CC]">
        <span class="text-xs text-gray-400">Visible sur mon profil</span>
    </label>
</div>
```

Also remove the note `(optionnel — visibles sur votre profil)` from the section label (line 179) since visibility is now controlled by the checkboxes — replace with just `(optionnel)`.

- [ ] **Step 6: Commit**

```bash
git add app/Livewire/Profile/CreateProfile.php resources/views/livewire/profile/create-profile.blade.php
git commit -m "feat(profiles): add visibility toggles to create-profile step 2"
```

---

### Task 7: Display contact info on public profile page

**Files:**
- Modify: `resources/views/profile/show.blade.php`

- [ ] **Step 1: Add contact info block**

In `profile/show.blade.php`, inside the `<div class="grid grid-cols-1 md:grid-cols-2 gap-5">` section (around line 139), add a contact info card after the existing Skills and Education cards:

```html
{{-- Contact info --}}
@if ($profile->show_phone || $profile->show_email_contact)
    <div class="bg-white border border-gray-200 p-6">
        <h2 class="font-bold text-sm text-[#111827] mb-4 uppercase tracking-wider">Contact direct</h2>
        <div class="space-y-2">
            @if ($profile->show_email_contact && $profile->email_contact)
                <a href="mailto:{{ $profile->email_contact }}"
                   class="flex items-center gap-2 text-sm text-[#0066CC] hover:underline break-all">
                    <svg class="w-4 h-4 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25H4.5a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5H4.5a2.25 2.25 0 00-2.25 2.25m19.5 0l-9.75 6.75L2.25 6.75"/>
                    </svg>
                    {{ $profile->email_contact }}
                </a>
            @endif
            @if ($profile->show_phone && $profile->phone)
                <a href="tel:{{ $profile->phone }}"
                   class="flex items-center gap-2 text-sm text-[#0066CC] hover:underline">
                    <svg class="w-4 h-4 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 6.75z"/>
                    </svg>
                    {{ $profile->phone }}
                </a>
            @endif
        </div>
    </div>
@endif
```

- [ ] **Step 2: Run profile visibility tests**

```bash
php artisan test tests/Feature/Profile/ProfileVisibilityTest.php
```

Expected: all 4 PASS.

- [ ] **Step 3: Run full test suite**

```bash
php artisan test
```

Expected: all PASS.

- [ ] **Step 4: Commit**

```bash
git add resources/views/profile/show.blade.php
git commit -m "feat(profiles): display phone/email on public profile when visibility enabled"
```
