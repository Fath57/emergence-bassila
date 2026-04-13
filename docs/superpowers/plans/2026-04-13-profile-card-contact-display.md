# Profile Card Contact Display Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add contact info (email + phone with icons) and a "Contacter" shortcut button to the profile cards in the annuaire, respecting each member's visibility settings.

**Architecture:** Pure Blade changes to `profile-card.blade.php` — no new PHP classes, no migrations. The `show_phone`, `phone`, `show_email_contact`, `email_contact` attributes are already on the Profile model. Auth checks use `@auth`/`@guest` directives. The "Contacter" button links to `profile.show#contact-form` for authenticated visitors, or to `login` for guests.

**Tech Stack:** Laravel 11, Livewire 3, Tailwind CSS, Pest

---

## File Map

| File | Change |
|------|--------|
| `resources/views/livewire/profile/profile-card.blade.php` | Add contact strip + dual action buttons |
| `tests/Feature/Directory/ProfileCardTest.php` | New — 6 test cases |

---

### Task 1: Write failing tests

**Files:**
- Create: `tests/Feature/Directory/ProfileCardTest.php`

- [ ] **Step 1: Create the test file**

```php
<?php

use App\Livewire\Profile\ProfileCard;
use App\Models\Profile;
use App\Models\User;
use Livewire\Livewire;

it('shows email with icon when show_email_contact is true', function () {
    $user    = User::factory()->create(['is_active' => true]);
    $profile = Profile::factory()->create([
        'user_id'            => $user->id,
        'email_contact'      => 'pro@example.com',
        'show_email_contact' => true,
        'is_verified'        => true,
    ]);

    Livewire::test(ProfileCard::class, ['profile' => $profile])
        ->assertSee('pro@example.com');
});

it('hides email when show_email_contact is false', function () {
    $user    = User::factory()->create(['is_active' => true]);
    $profile = Profile::factory()->create([
        'user_id'            => $user->id,
        'email_contact'      => 'pro@example.com',
        'show_email_contact' => false,
        'is_verified'        => true,
    ]);

    Livewire::test(ProfileCard::class, ['profile' => $profile])
        ->assertDontSee('pro@example.com');
});

it('shows phone with icon when show_phone is true', function () {
    $user    = User::factory()->create(['is_active' => true]);
    $profile = Profile::factory()->create([
        'user_id'    => $user->id,
        'phone'      => '+22960000000',
        'show_phone' => true,
        'is_verified' => true,
    ]);

    Livewire::test(ProfileCard::class, ['profile' => $profile])
        ->assertSee('+22960000000');
});

it('hides phone when show_phone is false', function () {
    $user    = User::factory()->create(['is_active' => true]);
    $profile = Profile::factory()->create([
        'user_id'    => $user->id,
        'phone'      => '+22960000000',
        'show_phone' => false,
        'is_verified' => true,
    ]);

    Livewire::test(ProfileCard::class, ['profile' => $profile])
        ->assertDontSee('+22960000000');
});

it('authenticated user sees Contacter button linking to contact form', function () {
    $viewer  = User::factory()->create(['is_active' => true]);
    $owner   = User::factory()->create(['is_active' => true]);
    $profile = Profile::factory()->create(['user_id' => $owner->id]);

    Livewire::actingAs($viewer)
        ->test(ProfileCard::class, ['profile' => $profile])
        ->assertSee('Contacter')
        ->assertSee(route('profile.show', $profile) . '#contact-form');
});

it('guest sees Contacter button linking to login', function () {
    $owner   = User::factory()->create(['is_active' => true]);
    $profile = Profile::factory()->create(['user_id' => $owner->id]);

    Livewire::test(ProfileCard::class, ['profile' => $profile])
        ->assertSee('Contacter')
        ->assertSee(route('login'));
});

it('profile owner does not see Contacter button', function () {
    $owner   = User::factory()->create(['is_active' => true]);
    $profile = Profile::factory()->create(['user_id' => $owner->id]);

    Livewire::actingAs($owner)
        ->test(ProfileCard::class, ['profile' => $profile])
        ->assertDontSee('Contacter');
});
```

- [ ] **Step 2: Run tests to confirm they fail**

```bash
php artisan test tests/Feature/Directory/ProfileCardTest.php
```

Expected: all 7 FAIL (DB connection error counts as RED — functionality not yet implemented).

- [ ] **Step 3: Commit failing tests**

```bash
git add tests/Feature/Directory/ProfileCardTest.php
git commit -m "test(directory): add failing tests for profile card contact display"
```

---

### Task 2: Add contact strip and dual action buttons to profile card

**Files:**
- Modify: `resources/views/livewire/profile/profile-card.blade.php`

The current file structure (read it before editing):
- Lines 1–42: card wrapper, avatar, info block
- Lines 44–54: skills preview (border-t, `mt-4 pt-3`)
- Lines 56–62: action — single `<a>` "Voir le profil" (full-width)

- [ ] **Step 1: Replace the action block (lines 56–62) with the dual-button block**

Remove:
```html
    {{-- Link --}}
    <div class="mt-4">
        <a href="{{ route('profile.show', $profile) }}"
           class="block text-center text-sm font-semibold text-[#0066CC] border border-[#0066CC] py-1.5 hover:bg-[#0066CC] hover:text-white transition">
            Voir le profil
        </a>
    </div>
```

Replace with:
```html
    {{-- Actions --}}
    <div class="mt-4">
        @if (auth()->check() && auth()->id() === $profile->user_id)
            {{-- Own profile: full-width "Voir le profil" --}}
            <a href="{{ route('profile.show', $profile) }}"
               class="block text-center text-sm font-semibold text-[#0066CC] border border-[#0066CC] py-1.5 hover:bg-[#0066CC] hover:text-white transition">
                Voir le profil
            </a>
        @else
            <div class="flex gap-2">
                <a href="{{ route('profile.show', $profile) }}"
                   class="flex-1 text-center text-sm font-semibold text-[#0066CC] border border-[#0066CC] py-1.5 hover:bg-[#0066CC] hover:text-white transition">
                    Voir le profil
                </a>
                @auth
                    <a href="{{ route('profile.show', $profile) }}#contact-form"
                       class="flex-1 text-center text-sm font-semibold bg-[#0066CC] text-white py-1.5 hover:bg-blue-800 transition">
                        Contacter
                    </a>
                @else
                    <a href="{{ route('login') }}"
                       class="flex-1 text-center text-sm font-semibold bg-[#0066CC] text-white py-1.5 hover:bg-blue-800 transition">
                        Contacter
                    </a>
                @endauth
            </div>
        @endif
    </div>
```

- [ ] **Step 2: Add the contact strip between skills and actions**

Insert the following block **between** the skills block (`@if ($profile->relationLoaded('skills')...`) and the new actions block added in Step 1:

```html
    {{-- Contact info --}}
    @if (($profile->show_phone && $profile->phone) || ($profile->show_email_contact && $profile->email_contact))
        <div class="mt-3 pt-3 border-t border-gray-100 space-y-1.5">
            @if ($profile->show_email_contact && $profile->email_contact)
                <a href="mailto:{{ $profile->email_contact }}"
                   class="flex items-center gap-1.5 text-xs text-[#0066CC] hover:underline min-w-0">
                    <svg class="w-3.5 h-3.5 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25H4.5a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5H4.5a2.25 2.25 0 00-2.25 2.25m19.5 0l-9.75 6.75L2.25 6.75"/>
                    </svg>
                    <span class="truncate">{{ $profile->email_contact }}</span>
                </a>
            @endif
            @if ($profile->show_phone && $profile->phone)
                <a href="tel:{{ preg_replace('/\s+/', '', $profile->phone) }}"
                   class="flex items-center gap-1.5 text-xs text-[#0066CC] hover:underline">
                    <svg class="w-3.5 h-3.5 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 6.75z"/>
                    </svg>
                    <span>{{ $profile->phone }}</span>
                </a>
            @endif
        </div>
    @endif
```

- [ ] **Step 3: Run all ProfileCard tests**

```bash
php artisan test tests/Feature/Directory/ProfileCardTest.php
```

Expected: all 7 PASS (or DB connection error if test DB unavailable — verify logic by reading the blade output manually if needed).

- [ ] **Step 4: Commit**

```bash
git add resources/views/livewire/profile/profile-card.blade.php
git commit -m "feat(directory): add contact strip and Contacter button to profile card"
```
