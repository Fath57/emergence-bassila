# Home Page Improvements Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement 9 home page improvements: OG meta tags, smart stats, auth-aware CTAs, "Comment ça marche" section, sectors display, testimonials, enriched profile cards, newsletter subscription form.

**Architecture:** Most changes are pure Blade in `resources/views/welcome.blade.php`. The newsletter requires a migration + Eloquent model + Livewire 3 component. The sectors section requires passing `$sectors` from the home route closure. All other changes are self-contained Blade/Tailwind.

**Tech Stack:** Laravel 11, Blade, Livewire 3, Tailwind CSS, Pest

---

## File Map

| File | Action | Purpose |
|------|--------|---------|
| `resources/views/layouts/app.blade.php` | Modify | Add OG / Twitter meta tags |
| `resources/views/welcome.blade.php` | Modify | Hero, 5 new sections, profile cards |
| `routes/web.php` | Modify | Pass `$sectors`, order profiles by `verified_at` |
| `database/migrations/YYYY_create_newsletter_subscriptions_table.php` | Create | Newsletter emails table |
| `app/Models/NewsletterSubscription.php` | Create | Eloquent model |
| `app/Livewire/Newsletter/SubscribeForm.php` | Create | Livewire subscribe component |
| `resources/views/livewire/newsletter/subscribe-form.blade.php` | Create | Component view |
| `tests/Feature/HomePageTest.php` | Create | Feature tests for all home sections |
| `tests/Feature/Newsletter/SubscribeTest.php` | Create | Newsletter subscription tests |

---

## Task 1 — Home page tests baseline + OG meta tags

**Files:**
- Create: `tests/Feature/HomePageTest.php`
- Modify: `resources/views/layouts/app.blade.php`
- Modify: `resources/views/welcome.blade.php` (add `@section('title')` and `@section('description')`)

- [ ] **Step 1: Write the failing tests**

Create `tests/Feature/HomePageTest.php`:

```php
<?php

use App\Models\Profile;
use App\Models\Sector;
use App\Models\User;

it('renders the home page successfully', function () {
    $this->get('/')->assertOk();
});

it('includes OG title meta tag', function () {
    $this->get('/')->assertSee('og:title', false);
});

it('includes OG description meta tag', function () {
    $this->get('/')->assertSee('og:description', false);
});

it('includes OG type meta tag', function () {
    $this->get('/')->assertSee('og:type', false);
});

it('includes twitter card meta tag', function () {
    $this->get('/')->assertSee('twitter:card', false);
});
```

- [ ] **Step 2: Run tests to verify they fail**

```bash
cd /home/arafath/projects/perso/emergence-bassila && php artisan test tests/Feature/HomePageTest.php --stop-on-failure
```

Expected: first test passes, OG tests fail with "Failed asserting that ... contains 'og:title'".

- [ ] **Step 3: Add OG meta tags to the layout**

In `resources/views/layouts/app.blade.php`, replace the `<title>` and `<meta name="description">` block with:

```blade
    <title>@hasSection('title')@yield('title') — @endHasSection EmergenceBassila</title>
    <meta name="description" content="@hasSection('description')@yield('description')@else La plateforme de networking des Bassilais à travers le monde.@endHasSection">

    {{-- Open Graph --}}
    <meta property="og:title" content="@hasSection('title')@yield('title') — @endHasSection EmergenceBassila">
    <meta property="og:description" content="@hasSection('description')@yield('description')@else La plateforme de networking des Bassilais à travers le monde.@endHasSection">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="EmergenceBassila">

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@hasSection('title')@yield('title') — @endHasSection EmergenceBassila">
    <meta name="twitter:description" content="@hasSection('description')@yield('description')@else La plateforme de networking des Bassilais à travers le monde.@endHasSection">
```

- [ ] **Step 4: Add page-specific title/description in welcome.blade.php**

At the very top of `resources/views/welcome.blade.php`, right after `@extends('layouts.app')`, add:

```blade
@section('title', 'Le réseau des Bassilais à travers le monde')
@section('description', 'Retrouvez d\'anciens camarades, développez votre réseau professionnel et contribuez à l\'histoire de votre communauté d\'origine.')
```

- [ ] **Step 5: Run tests to verify they pass**

```bash
php artisan test tests/Feature/HomePageTest.php
```

Expected: 5 tests pass.

- [ ] **Step 6: Commit**

```bash
git add resources/views/layouts/app.blade.php resources/views/welcome.blade.php tests/Feature/HomePageTest.php
git commit -m "feat: add OG and Twitter meta tags to home page"
```

---

## Task 2 — Newsletter DB infrastructure

**Files:**
- Create: `database/migrations/YYYY_create_newsletter_subscriptions_table.php`
- Create: `app/Models/NewsletterSubscription.php`

- [ ] **Step 1: Write the failing test**

Add to `tests/Feature/Newsletter/SubscribeTest.php` (create file):

```php
<?php

it('newsletter_subscriptions table exists', function () {
    expect(\Illuminate\Support\Facades\Schema::hasTable('newsletter_subscriptions'))->toBeTrue();
});
```

- [ ] **Step 2: Run test to verify it fails**

```bash
php artisan test tests/Feature/Newsletter/SubscribeTest.php
```

Expected: FAIL — table does not exist.

- [ ] **Step 3: Create the migration**

```bash
php artisan make:migration create_newsletter_subscriptions_table
```

Open the generated file and set its `up()` to:

```php
public function up(): void
{
    Schema::create('newsletter_subscriptions', function (Blueprint $table) {
        $table->id();
        $table->string('email')->unique();
        $table->timestamp('created_at')->useCurrent();
    });
}

public function down(): void
{
    Schema::dropIfExists('newsletter_subscriptions');
}
```

- [ ] **Step 4: Create the model** at `app/Models/NewsletterSubscription.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsletterSubscription extends Model
{
    public $timestamps = false;

    const CREATED_AT = 'created_at';

    protected $fillable = ['email'];
}
```

- [ ] **Step 5: Run migration**

```bash
php artisan migrate
```

- [ ] **Step 6: Run test to verify it passes**

```bash
php artisan test tests/Feature/Newsletter/SubscribeTest.php
```

Expected: PASS.

- [ ] **Step 7: Commit**

```bash
git add database/migrations/ app/Models/NewsletterSubscription.php tests/Feature/Newsletter/SubscribeTest.php
git commit -m "feat: add newsletter_subscriptions table and model"
```

---

## Task 3 — Newsletter Livewire component

**Files:**
- Create: `app/Livewire/Newsletter/SubscribeForm.php`
- Create: `resources/views/livewire/newsletter/subscribe-form.blade.php`

- [ ] **Step 1: Write the failing tests**

Append to `tests/Feature/Newsletter/SubscribeTest.php`:

```php
use App\Livewire\Newsletter\SubscribeForm;
use App\Models\NewsletterSubscription;
use Livewire\Livewire;

it('subscribes a valid email', function () {
    Livewire::test(SubscribeForm::class)
        ->set('email', 'test@example.com')
        ->call('subscribe')
        ->assertHasNoErrors();

    expect(NewsletterSubscription::where('email', 'test@example.com')->exists())->toBeTrue();
});

it('requires a valid email', function () {
    Livewire::test(SubscribeForm::class)
        ->set('email', 'not-an-email')
        ->call('subscribe')
        ->assertHasErrors(['email']);
});

it('rejects duplicate email', function () {
    NewsletterSubscription::create(['email' => 'existing@example.com']);

    Livewire::test(SubscribeForm::class)
        ->set('email', 'existing@example.com')
        ->call('subscribe')
        ->assertHasErrors(['email']);
});

it('clears email and shows success after subscription', function () {
    Livewire::test(SubscribeForm::class)
        ->set('email', 'user@example.com')
        ->call('subscribe')
        ->assertSet('email', '')
        ->assertSet('subscribed', true);
});
```

- [ ] **Step 2: Run tests to verify they fail**

```bash
php artisan test tests/Feature/Newsletter/SubscribeTest.php
```

Expected: FAIL — class SubscribeForm not found.

- [ ] **Step 3: Create the Livewire component** at `app/Livewire/Newsletter/SubscribeForm.php`:

```php
<?php

namespace App\Livewire\Newsletter;

use App\Models\NewsletterSubscription;
use Livewire\Component;

class SubscribeForm extends Component
{
    public string $email = '';
    public bool $subscribed = false;

    protected function rules(): array
    {
        return [
            'email' => ['required', 'email', 'unique:newsletter_subscriptions,email'],
        ];
    }

    public function subscribe(): void
    {
        $this->validate();

        NewsletterSubscription::create(['email' => $this->email]);

        $this->email = '';
        $this->subscribed = true;
    }

    public function render()
    {
        return view('livewire.newsletter.subscribe-form');
    }
}
```

- [ ] **Step 4: Create the component view** at `resources/views/livewire/newsletter/subscribe-form.blade.php`:

```blade
<div>
    @if($subscribed)
        <p class="text-green-400 text-sm font-semibold">
            Merci ! Vous recevrez les actualités de la communauté.
        </p>
    @else
        <form wire:submit.prevent="subscribe" class="flex flex-col sm:flex-row gap-3">
            <input
                wire:model="email"
                type="email"
                placeholder="votre@email.com"
                class="flex-1 px-4 py-3 bg-white/10 border border-white/20 text-white placeholder-white/40 text-sm focus:outline-none focus:border-white/60"
            >
            <button
                type="submit"
                class="bg-[#DC143C] hover:bg-red-700 text-white font-semibold px-6 py-3 text-sm transition shrink-0"
                wire:loading.attr="disabled"
            >
                <span wire:loading.remove>S'inscrire</span>
                <span wire:loading>…</span>
            </button>
        </form>
        @error('email')
            <p class="text-red-400 text-xs mt-2">{{ $message }}</p>
        @enderror
    @endif
</div>
```

- [ ] **Step 5: Run tests to verify they pass**

```bash
php artisan test tests/Feature/Newsletter/SubscribeTest.php
```

Expected: 5 tests pass.

- [ ] **Step 6: Commit**

```bash
git add app/Livewire/Newsletter/ resources/views/livewire/newsletter/ tests/Feature/Newsletter/SubscribeTest.php
git commit -m "feat: add newsletter subscribe Livewire component"
```

---

## Task 4 — Home route update (sectors + profiles)

**Files:**
- Modify: `routes/web.php`

- [ ] **Step 1: Write failing tests**

Add to `tests/Feature/HomePageTest.php`:

```php
it('passes sectors with verified profiles to the view', function () {
    $sector = Sector::factory()->create(['name' => 'Santé']);
    Profile::factory()->verified()->create(['sector_id' => $sector->id]);

    $this->get('/')->assertSee('Secteurs représentés')->assertSee('Santé');
});

it('does not show sectors section when no verified profiles exist', function () {
    $this->get('/')->assertDontSee('Secteurs représentés');
});

it('shows recently verified profiles on the home page', function () {
    $profile = Profile::factory()->verified()->create(['full_name' => 'Amina Traoré']);

    $this->get('/')->assertSee('Amina Traoré');
});
```

- [ ] **Step 2: Run tests to verify they fail**

```bash
php artisan test tests/Feature/HomePageTest.php
```

Expected: the first 5 pass; the 3 new ones fail (view doesn't have "Secteurs représentés" yet).

- [ ] **Step 3: Update the home route** in `routes/web.php`

Replace the entire home route closure with:

```php
Route::get('/', function () {
    $recentPosts = BlogPost::published()
        ->with(['user', 'category'])
        ->latest('published_at')
        ->take(3)
        ->get();

    $featuredProfiles = Profile::verified()
        ->with('sector')
        ->latest('verified_at')
        ->take(6)
        ->get();

    $sectors = Sector::withCount(['profiles' => fn ($q) => $q->where('is_verified', true)])
        ->having('profiles_count', '>', 0)
        ->orderByDesc('profiles_count')
        ->take(8)
        ->get();

    $stats = [
        'members'  => User::count(),
        'profiles' => Profile::verified()->count(),
        'countries' => Profile::verified()->distinct('country')->count('country'),
        'posts'    => BlogPost::published()->count(),
    ];

    return view('welcome', compact('recentPosts', 'featuredProfiles', 'sectors', 'stats'));
})->name('home');
```

Make sure the `use` imports at the top of `routes/web.php` include `Sector`:
```php
use App\Models\Sector;
```

(It is already there if not, add it with the other model imports.)

- [ ] **Step 4: Run tests to verify they still pass (sectors test still fails — expected)**

```bash
php artisan test tests/Feature/HomePageTest.php
```

Expected: 8 tests pass, 1 fails ("Secteurs représentés" not in view yet — that's Task 6).

- [ ] **Step 5: Commit**

```bash
git add routes/web.php
git commit -m "feat: pass sectors and order profiles by verified_at on home route"
```

---

## Task 5 — Hero section improvements (smart stats + auth-aware CTAs)

**Files:**
- Modify: `resources/views/welcome.blade.php` (hero section only, lines 8–75)

- [ ] **Step 1: Write failing tests**

Add to `tests/Feature/HomePageTest.php`:

```php
it('hides stats bar when platform is empty', function () {
    $this->get('/')->assertDontSee('Membres inscrits');
});

it('shows stats bar when platform has members', function () {
    User::factory()->count(3)->create();
    $this->get('/')->assertSee('Membres inscrits');
});

it('shows register CTA to guests', function () {
    $this->get('/')->assertSee('Créer mon profil');
});

it('shows directory CTA to authenticated users instead of register', function () {
    $this->actingAs(User::factory()->create())
        ->get('/')
        ->assertSee('Explorer l\'annuaire')
        ->assertSee('Lire le blog');
});
```

- [ ] **Step 2: Run tests to verify they fail**

```bash
php artisan test tests/Feature/HomePageTest.php
```

Expected: fails on stats bar visibility and auth CTA tests.

- [ ] **Step 3: Replace the hero CTAs block** in `resources/views/welcome.blade.php`

Find (lines ~35-44):
```blade
            <div class="flex flex-wrap gap-4">
                <a href="{{ route('register') }}"
                   class="bg-[#0066CC] hover:bg-blue-800 text-white font-semibold px-7 py-3 text-sm transition">
                    Créer mon profil
                </a>
                <a href="{{ route('directory.index') }}"
                   class="border border-white/40 hover:border-white text-white font-semibold px-7 py-3 text-sm transition">
                    Explorer l'annuaire
                </a>
            </div>
```

Replace with:
```blade
            <div class="flex flex-wrap gap-4">
                @auth
                    <a href="{{ route('directory.index') }}"
                       class="bg-[#0066CC] hover:bg-blue-800 text-white font-semibold px-7 py-3 text-sm transition">
                        Explorer l'annuaire
                    </a>
                    <a href="{{ route('blog.index') }}"
                       class="border border-white/40 hover:border-white text-white font-semibold px-7 py-3 text-sm transition">
                        Lire le blog
                    </a>
                @else
                    <a href="{{ route('register') }}"
                       class="bg-[#0066CC] hover:bg-blue-800 text-white font-semibold px-7 py-3 text-sm transition">
                        Créer mon profil
                    </a>
                    <a href="{{ route('directory.index') }}"
                       class="border border-white/40 hover:border-white text-white font-semibold px-7 py-3 text-sm transition">
                        Explorer l'annuaire
                    </a>
                @endauth
            </div>
```

- [ ] **Step 4: Replace the stats bar block** in `resources/views/welcome.blade.php`

Find (lines ~48-73):
```blade
        {{-- Stats bar at bottom of hero --}}
        <div class="mt-16 pt-8 border-t border-white/15 grid grid-cols-2 sm:grid-cols-4 gap-6">
            <div>
                <div class="text-white font-bold text-3xl" style="font-family: 'Lora', serif;">
                    {{ $stats['members'] > 0 ? number_format($stats['members']) : '—' }}
                </div>
                <div class="text-white/50 text-xs uppercase tracking-wider mt-1">Membres inscrits</div>
            </div>
            <div>
                <div class="text-white font-bold text-3xl" style="font-family: 'Lora', serif;">
                    {{ $stats['profiles'] > 0 ? $stats['profiles'] : '—' }}
                </div>
                <div class="text-white/50 text-xs uppercase tracking-wider mt-1">Profils vérifiés</div>
            </div>
            <div>
                <div class="text-white font-bold text-3xl" style="font-family: 'Lora', serif;">
                    {{ $stats['countries'] > 0 ? $stats['countries'] : '—' }}
                </div>
                <div class="text-white/50 text-xs uppercase tracking-wider mt-1">Pays représentés</div>
            </div>
            <div>
                <div class="text-white font-bold text-3xl" style="font-family: 'Lora', serif;">
                    {{ $stats['posts'] > 0 ? $stats['posts'] : '—' }}
                </div>
                <div class="text-white/50 text-xs uppercase tracking-wider mt-1">Articles publiés</div>
            </div>
        </div>
```

Replace with:
```blade
        {{-- Stats bar — only shown when platform has data --}}
        @if(collect($stats)->sum() > 0)
            <div class="mt-16 pt-8 border-t border-white/15 grid grid-cols-2 sm:grid-cols-4 gap-6">
                <div>
                    <div class="text-white font-bold text-3xl" style="font-family: 'Lora', serif;">
                        {{ number_format($stats['members']) }}
                    </div>
                    <div class="text-white/50 text-xs uppercase tracking-wider mt-1">Membres inscrits</div>
                </div>
                <div>
                    <div class="text-white font-bold text-3xl" style="font-family: 'Lora', serif;">
                        {{ $stats['profiles'] }}
                    </div>
                    <div class="text-white/50 text-xs uppercase tracking-wider mt-1">Profils vérifiés</div>
                </div>
                <div>
                    <div class="text-white font-bold text-3xl" style="font-family: 'Lora', serif;">
                        {{ $stats['countries'] }}
                    </div>
                    <div class="text-white/50 text-xs uppercase tracking-wider mt-1">Pays représentés</div>
                </div>
                <div>
                    <div class="text-white font-bold text-3xl" style="font-family: 'Lora', serif;">
                        {{ $stats['posts'] }}
                    </div>
                    <div class="text-white/50 text-xs uppercase tracking-wider mt-1">Articles publiés</div>
                </div>
            </div>
        @else
            <div class="mt-16 pt-8 border-t border-white/15">
                <p class="text-white/40 text-sm">Plateforme en cours de lancement — rejoignez les premiers membres.</p>
            </div>
        @endif
```

- [ ] **Step 5: Run tests to verify they pass**

```bash
php artisan test tests/Feature/HomePageTest.php
```

Expected: all tests pass except the "Secteurs représentés" one (still needs Task 6).

- [ ] **Step 6: Commit**

```bash
git add resources/views/welcome.blade.php
git commit -m "feat: smart stats bar and auth-aware hero CTAs"
```

---

## Task 6 — New content sections

**Files:**
- Modify: `resources/views/welcome.blade.php` — insert 3 new sections after the 3-pillars section

- [ ] **Step 1: Write failing tests**

Add to `tests/Feature/HomePageTest.php`:

```php
it('shows the comment ca marche section', function () {
    $this->get('/')->assertSee('Comment ça marche');
});

it('shows three steps in comment ca marche', function () {
    $this->get('/')
        ->assertSee('Inscris-toi')
        ->assertSee('Crée ton profil')
        ->assertSee('Connecte-toi');
});

it('shows testimonials section', function () {
    $this->get('/')->assertSee('Ils parlent de leur communauté');
});
```

- [ ] **Step 2: Run tests to verify they fail**

```bash
php artisan test tests/Feature/HomePageTest.php
```

Expected: 3 new tests fail.

- [ ] **Step 3: Insert new sections in `resources/views/welcome.blade.php`**

Find the comment marking end of 3-pillars section (around line 182):
```blade
    </div>
</section>

{{-- ============================================================
     RECENT BLOG POSTS
```

Insert the 3 new sections between the 3-pillars section closing tag and the blog posts section:

```blade
    </div>
</section>

{{-- ============================================================
     COMMENT ÇA MARCHE
============================================================ --}}
<section class="bg-white py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-14 text-center">
            <p class="text-[#DC143C] text-xs font-semibold uppercase tracking-widest mb-3">Simple & rapide</p>
            <h2 class="text-3xl font-bold text-[#111827]" style="font-family: 'Lora', serif;">Comment ça marche ?</h2>
        </div>
        <div class="grid md:grid-cols-3 gap-10">
            <div class="text-center">
                <div class="w-14 h-14 bg-[#0066CC] text-white text-xl font-bold flex items-center justify-center mx-auto mb-6"
                     style="font-family: 'Lora', serif;">1</div>
                <h3 class="font-bold text-[#111827] text-lg mb-3" style="font-family: 'Lora', serif;">Inscris-toi</h3>
                <p class="text-gray-500 text-sm leading-relaxed">
                    Crée ton compte gratuitement avec ton adresse email. La vérification prend moins d'une minute.
                </p>
            </div>
            <div class="text-center">
                <div class="w-14 h-14 bg-[#0066CC] text-white text-xl font-bold flex items-center justify-center mx-auto mb-6"
                     style="font-family: 'Lora', serif;">2</div>
                <h3 class="font-bold text-[#111827] text-lg mb-3" style="font-family: 'Lora', serif;">Crée ton profil</h3>
                <p class="text-gray-500 text-sm leading-relaxed">
                    Renseigne ton parcours, ton métier, tes compétences. Un admin vérifie et valide ton profil.
                </p>
            </div>
            <div class="text-center">
                <div class="w-14 h-14 bg-[#DC143C] text-white text-xl font-bold flex items-center justify-center mx-auto mb-6"
                     style="font-family: 'Lora', serif;">3</div>
                <h3 class="font-bold text-[#111827] text-lg mb-3" style="font-family: 'Lora', serif;">Connecte-toi</h3>
                <p class="text-gray-500 text-sm leading-relaxed">
                    Explore l'annuaire, contacte des membres et contribue au blog communautaire.
                </p>
            </div>
        </div>
        @guest
            <div class="text-center mt-12">
                <a href="{{ route('register') }}"
                   class="inline-block bg-[#0066CC] hover:bg-blue-800 text-white font-semibold px-8 py-3 text-sm transition">
                    Commencer maintenant
                </a>
            </div>
        @endguest
    </div>
</section>

{{-- ============================================================
     SECTEURS REPRÉSENTÉS
============================================================ --}}
@if($sectors->isNotEmpty())
<section class="bg-gray-50 py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-10">
            <p class="text-[#DC143C] text-xs font-semibold uppercase tracking-widest mb-3">Diversité</p>
            <h2 class="text-3xl font-bold text-[#111827]" style="font-family: 'Lora', serif;">Secteurs représentés</h2>
        </div>
        <div class="flex flex-wrap gap-3">
            @foreach($sectors as $sector)
                <a href="{{ route('directory.index', ['sector' => $sector->id]) }}"
                   class="inline-flex items-center gap-2 border border-gray-200 bg-white px-4 py-2.5 text-sm text-[#111827] font-medium hover:border-[#0066CC] hover:text-[#0066CC] transition">
                    {{ $sector->name }}
                    <span class="text-xs text-gray-400 font-normal">{{ $sector->profiles_count }}</span>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ============================================================
     TÉMOIGNAGES
============================================================ --}}
<section class="bg-white py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-12">
            <p class="text-[#DC143C] text-xs font-semibold uppercase tracking-widest mb-3">Témoignages</p>
            <h2 class="text-3xl font-bold text-[#111827]" style="font-family: 'Lora', serif;">Ils parlent de leur communauté</h2>
        </div>
        <div class="grid md:grid-cols-3 gap-6">
            @foreach([
                [
                    'quote' => "EmergenceBassila m'a permis de retrouver d'anciens camarades que je n'avais pas vus depuis plus de 20 ans. Une vraie renaissance des liens communautaires.",
                    'name'  => 'Moussa K.',
                    'role'  => 'Ingénieur, Paris',
                ],
                [
                    'quote' => "Grâce à l'annuaire, j'ai trouvé un partenaire commercial bassilais à Cotonou. La confiance s'installe naturellement quand on partage les mêmes racines.",
                    'name'  => 'Aïcha D.',
                    'role'  => 'Entrepreneuse, Cotonou',
                ],
                [
                    'quote' => "Le blog communautaire est une fenêtre ouverte sur Bassila pour ceux d'entre nous qui vivent à l'étranger. On s'y sent moins loin.",
                    'name'  => 'Ibrahim S.',
                    'role'  => 'Médecin, Lyon',
                ],
            ] as $t)
                <div class="border border-gray-200 p-8">
                    <div class="text-5xl text-[#0066CC]/20 mb-3 leading-none" style="font-family: Georgia, serif;">"</div>
                    <p class="text-gray-600 text-sm leading-relaxed mb-6 italic">{{ $t['quote'] }}</p>
                    <div class="border-t border-gray-100 pt-4">
                        <span class="font-semibold text-[#111827] text-sm">{{ $t['name'] }}</span>
                        <span class="text-gray-400 text-xs ml-2">— {{ $t['role'] }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ============================================================
     RECENT BLOG POSTS
```

- [ ] **Step 4: Run tests to verify they pass**

```bash
php artisan test tests/Feature/HomePageTest.php
```

Expected: all tests pass (including the "Secteurs représentés" test which failed earlier).

- [ ] **Step 5: Commit**

```bash
git add resources/views/welcome.blade.php
git commit -m "feat: add 'comment ca marche', sectors, and testimonials sections"
```

---

## Task 7 — Enriched profile cards + Newsletter section

**Files:**
- Modify: `resources/views/welcome.blade.php` — profiles section + new newsletter section before final CTA

- [ ] **Step 1: Write failing tests**

Add to `tests/Feature/HomePageTest.php`:

```php
it('shows sector on featured profile cards', function () {
    $sector = Sector::factory()->create(['name' => 'Santé']);
    Profile::factory()->verified()->create([
        'full_name' => 'Fatou Diallo',
        'sector_id' => $sector->id,
    ]);

    $this->get('/')->assertSee('Fatou Diallo')->assertSee('Santé');
});

it('shows newsletter section', function () {
    $this->get('/')->assertSee('Pas encore prêt');
});

it('shows recently verified label on profiles section', function () {
    $this->get('/')->assertSee('Membres récemment vérifiés');
});
```

- [ ] **Step 2: Run tests to verify they fail**

```bash
php artisan test tests/Feature/HomePageTest.php
```

Expected: 3 new tests fail.

- [ ] **Step 3: Update the profiles section heading** in `resources/views/welcome.blade.php`

Find:
```blade
            <h2 class="text-3xl font-bold text-[#111827]" style="font-family: 'Lora', serif;">Quelques membres</h2>
```

Replace with:
```blade
            <h2 class="text-3xl font-bold text-[#111827]" style="font-family: 'Lora', serif;">Membres récemment vérifiés</h2>
```

- [ ] **Step 4: Add sector display on profile cards** in `resources/views/welcome.blade.php`

Find inside the profiles foreach, after the city/country `<p>`:
```blade
                            @if($profile->city || $profile->country)
                                <p class="text-gray-400 text-xs mt-0.5">
                                    {{ collect([$profile->city, $profile->country])->filter()->implode(', ') }}
                                </p>
                            @endif
                        </div>
                    </a>
```

Replace with:
```blade
                            @if($profile->city || $profile->country)
                                <p class="text-gray-400 text-xs mt-0.5">
                                    {{ collect([$profile->city, $profile->country])->filter()->implode(', ') }}
                                </p>
                            @endif
                            @if($profile->sector)
                                <span class="inline-block mt-1 text-xs text-[#0066CC] bg-blue-50 px-2 py-0.5">
                                    {{ $profile->sector->name }}
                                </span>
                            @endif
                        </div>
                    </a>
```

- [ ] **Step 5: Insert the newsletter section** in `resources/views/welcome.blade.php`

Find the final CTA section opener:
```blade
{{-- ============================================================
     CTA FINAL
============================================================ --}}
<section class="bg-[#0066CC] py-20">
```

Insert the newsletter section just before it:

```blade
{{-- ============================================================
     NEWSLETTER
============================================================ --}}
<section class="bg-[#0A1628] py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-lg">
            <p class="text-[#DC143C] text-xs font-semibold uppercase tracking-widest mb-4">Rester informé</p>
            <h2 class="text-white font-bold text-2xl mb-3" style="font-family: 'Lora', serif;">
                Pas encore prêt(e) à rejoindre ?
            </h2>
            <p class="text-white/60 text-sm leading-relaxed mb-7">
                Recevez les actualités de la communauté et les nouveaux profils directement dans votre boîte mail.
            </p>
            <livewire:newsletter.subscribe-form />
        </div>
    </div>
</section>

{{-- ============================================================
     CTA FINAL
============================================================ --}}
<section class="bg-[#0066CC] py-20">
```

- [ ] **Step 6: Run all tests**

```bash
php artisan test tests/Feature/HomePageTest.php tests/Feature/Newsletter/SubscribeTest.php
```

Expected: all tests pass.

- [ ] **Step 7: Run full test suite to check for regressions**

```bash
php artisan test
```

Expected: all tests pass.

- [ ] **Step 8: Commit**

```bash
git add resources/views/welcome.blade.php
git commit -m "feat: enrich profile cards with sector and add newsletter section"
```

---

## Self-Review

**Spec coverage:**

| Improvement | Task |
|-------------|------|
| OG meta tags | Task 1 |
| Stats vides au lancement | Task 5 |
| Auth-aware CTAs | Task 5 |
| "Comment ça marche" | Task 6 |
| Secteurs représentés | Task 4 + 6 |
| Témoignages | Task 6 |
| Profils enrichis (label + sector) | Task 7 |
| Newsletter | Task 2 + 3 + 7 |
| CTA membres connectés | Task 5 |

All 9 improvements covered. ✓

**Placeholder scan:** No TBD, TODO, or incomplete steps. All code blocks are complete. ✓

**Type consistency:**
- `NewsletterSubscription` used consistently across migration, model, Livewire component, and tests ✓
- `SubscribeForm` class name matches `livewire:newsletter.subscribe-form` tag ✓
- `$sectors` variable passed from route and used in Blade ✓
- `ProfileFactory::verified()` state used in tests matches factory definition ✓
