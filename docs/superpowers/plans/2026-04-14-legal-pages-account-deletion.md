# Legal Pages & Account Deletion — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Publish CGU, Privacy Policy and Legal Mentions pages, and ship a self-serve account-deletion flow with a 30-day grace period plus admin oversight, fully aligned with RGPD and Benin's code numérique.

**Architecture:** Legal pages are static Blade views routed through `Route::view()` (git serves as version history). A new `account_deletion_requests` table tracks a state machine (`requested` → `confirmed` → `purged`, with `cancelled` branches). A Livewire `DeleteAccount` component kicks off the flow; a signed email link transitions the request to `confirmed` and disables the account. A daily artisan command `accounts:purge-expired` performs a differentiated purge (profile deleted, articles/comments anonymised, logs retained). An admin Livewire page gives oversight.

**Tech Stack:** Laravel 11, Livewire 3, Tailwind, Pest, spatie/laravel-permission, spatie/laravel-activitylog.

**Spec reference:** `docs/superpowers/specs/2026-04-14-legal-pages-account-deletion-design.md`

---

## File Map

Files created:

- `database/migrations/2026_04_14_090000_create_account_deletion_requests_table.php`
- `database/migrations/2026_04_14_090100_add_author_display_name_to_blog_posts_table.php`
- `database/migrations/2026_04_14_090200_add_author_display_name_to_blog_comments_table.php`
- `app/Models/AccountDeletionRequest.php`
- `app/Policies/AccountDeletionRequestPolicy.php`
- `app/Livewire/Profile/DeleteAccount.php` + `resources/views/livewire/profile/delete-account.blade.php`
- `app/Livewire/Account/ConfirmDeletion.php` + view
- `app/Livewire/Account/CancelDeletion.php` + view
- `app/Livewire/Admin/DeletionRequests.php` + `resources/views/livewire/admin/deletion-requests.blade.php`
- `app/Mail/AccountDeletionRequested.php` + `resources/views/mail/account/deletion-requested.blade.php`
- `app/Mail/AccountDeletionConfirmed.php` + `resources/views/mail/account/deletion-confirmed.blade.php`
- `app/Mail/AccountDeletionCancelled.php` + `resources/views/mail/account/deletion-cancelled.blade.php`
- `app/Mail/AccountDeletionCompleted.php` + `resources/views/mail/account/deletion-completed.blade.php`
- `app/Mail/AdminDeletionPendingWithContent.php` + `resources/views/mail/account/admin-pending-content.blade.php`
- `app/Console/Commands/PurgeExpiredAccounts.php`
- `app/Http/Middleware/RedirectIfDeletionPending.php`
- `resources/views/pages/cgu.blade.php`
- `resources/views/pages/politique-confidentialite.blade.php`
- `resources/views/pages/mentions-legales.blade.php`
- `database/factories/AccountDeletionRequestFactory.php`
- `tests/Unit/AccountDeletionRequestTest.php`
- `tests/Feature/LegalPagesTest.php`
- `tests/Feature/Auth/RegistrationAcceptsTermsTest.php`
- `tests/Feature/Account/DeleteAccountFlowTest.php`
- `tests/Feature/Account/PurgeExpiredAccountsCommandTest.php`
- `tests/Feature/Admin/DeletionRequestsAdminTest.php`

Files modified:

- `app/Models/User.php` — add relation `deletionRequest()`, method `hasPendingDeletion()`
- `app/Models/BlogPost.php` — accessor `displayAuthorName`
- `app/Models/BlogComment.php` — accessor `displayAuthorName`
- `app/Livewire/Auth/Register.php` — add `accepts_terms` validated checkbox
- `resources/views/livewire/auth/register.blade.php` — checkbox UI
- `resources/views/livewire/profile/edit-profile.blade.php` — include DeleteAccount component
- `resources/views/partials/footer.blade.php` — replace dead `#` links with real routes
- `resources/views/livewire/blog/...` (views displaying post author) — use `displayAuthorName`
- `resources/views/livewire/admin/*` (sidebar) — add «Suppressions» nav item
- `routes/web.php` — add legal routes, deletion confirm/cancel routes, admin deletion route
- `routes/console.php` — schedule `accounts:purge-expired` daily at 03:00
- `bootstrap/app.php` — register `RedirectIfDeletionPending` middleware alias and append to `web` group for authenticated users
- `app/Providers/AuthServiceProvider.php` (create if missing) — register policy

---

## Task 1: Migrations & `AccountDeletionRequest` model (with transition unit tests)

**Files:**
- Create: `database/migrations/2026_04_14_090000_create_account_deletion_requests_table.php`
- Create: `database/migrations/2026_04_14_090100_add_author_display_name_to_blog_posts_table.php`
- Create: `database/migrations/2026_04_14_090200_add_author_display_name_to_blog_comments_table.php`
- Create: `app/Models/AccountDeletionRequest.php`
- Create: `database/factories/AccountDeletionRequestFactory.php`
- Create: `tests/Unit/AccountDeletionRequestTest.php`
- Modify: `app/Models/User.php`

- [ ] **Step 1.1 — Write the deletion-requests migration**

Create `database/migrations/2026_04_14_090000_create_account_deletion_requests_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('account_deletion_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->unique()
                ->constrained()
                ->cascadeOnDelete();
            $table->enum('status', ['requested', 'confirmed', 'cancelled', 'purged'])
                ->default('requested');
            $table->string('confirmation_token', 64)->nullable()->index();
            $table->timestamp('requested_at')->useCurrent();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('scheduled_purge_at')->nullable()->index();
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('cancelled_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->text('cancel_reason')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamp('purged_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_deletion_requests');
    }
};
```

- [ ] **Step 1.2 — Write the `author_display_name` migrations**

Create `database/migrations/2026_04_14_090100_add_author_display_name_to_blog_posts_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->string('author_display_name', 120)->nullable()->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropColumn('author_display_name');
        });
    }
};
```

Create `database/migrations/2026_04_14_090200_add_author_display_name_to_blog_comments_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('blog_comments', function (Blueprint $table) {
            $table->string('author_display_name', 120)->nullable()->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('blog_comments', function (Blueprint $table) {
            $table->dropColumn('author_display_name');
        });
    }
};
```

Before running, double-check the `blog_comments` schema; if the column already exists, delete this migration.

- [ ] **Step 1.3 — Write the failing unit tests**

Create `tests/Unit/AccountDeletionRequestTest.php`:

```php
<?php

use App\Models\AccountDeletionRequest;
use App\Models\User;

it('is created in requested state with a token', function () {
    $user = User::factory()->create();
    $req = AccountDeletionRequest::startFor($user);

    expect($req->status)->toBe('requested')
        ->and($req->confirmation_token)->toHaveLength(64)
        ->and($req->requested_at)->not->toBeNull();
});

it('transitions from requested to confirmed, clears token, schedules purge at now+30d', function () {
    $user = User::factory()->create();
    $req = AccountDeletionRequest::startFor($user);

    $req->confirm();

    expect($req->status)->toBe('confirmed')
        ->and($req->confirmed_at)->not->toBeNull()
        ->and($req->confirmation_token)->toBeNull()
        ->and($req->scheduled_purge_at->isSameDay(now()->addDays(30)))->toBeTrue();
});

it('cannot confirm a cancelled request', function () {
    $user = User::factory()->create();
    $req = AccountDeletionRequest::startFor($user);
    $req->cancel($user, 'changed mind');

    expect(fn () => $req->confirm())
        ->toThrow(LogicException::class);
});

it('cannot confirm a purged request', function () {
    $user = User::factory()->create();
    $req = AccountDeletionRequest::factory()->for($user)->purged()->create();

    expect(fn () => $req->confirm())
        ->toThrow(LogicException::class);
});

it('can cancel from requested state', function () {
    $user = User::factory()->create();
    $req = AccountDeletionRequest::startFor($user);

    $req->cancel($user, 'changed mind');

    expect($req->status)->toBe('cancelled')
        ->and($req->cancelled_at)->not->toBeNull()
        ->and($req->cancelled_by)->toBe($user->id)
        ->and($req->cancel_reason)->toBe('changed mind');
});

it('can cancel from confirmed state', function () {
    $user = User::factory()->create();
    $req = AccountDeletionRequest::startFor($user);
    $req->confirm();

    $req->cancel($user);

    expect($req->status)->toBe('cancelled');
});

it('cannot cancel an already cancelled request', function () {
    $user = User::factory()->create();
    $req = AccountDeletionRequest::startFor($user);
    $req->cancel($user);

    expect(fn () => $req->cancel($user))->toThrow(LogicException::class);
});

it('dueForPurge scope returns only confirmed requests whose purge date has passed', function () {
    $u1 = User::factory()->create();
    $u2 = User::factory()->create();
    $u3 = User::factory()->create();

    $due = AccountDeletionRequest::factory()->for($u1)->confirmed()->create([
        'scheduled_purge_at' => now()->subDay(),
    ]);
    AccountDeletionRequest::factory()->for($u2)->confirmed()->create([
        'scheduled_purge_at' => now()->addDays(10),
    ]);
    AccountDeletionRequest::factory()->for($u3)->create(['status' => 'requested']);

    $ids = AccountDeletionRequest::dueForPurge()->pluck('id');

    expect($ids)->toHaveCount(1)->and($ids->first())->toBe($due->id);
});
```

- [ ] **Step 1.4 — Run the tests and confirm they fail**

Run: `./vendor/bin/pest tests/Unit/AccountDeletionRequestTest.php`
Expected: failures (class missing / methods missing).

- [ ] **Step 1.5 — Write the model**

Create `app/Models/AccountDeletionRequest.php`:

```php
<?php

namespace App\Models;

use Database\Factories\AccountDeletionRequestFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use LogicException;

class AccountDeletionRequest extends Model
{
    /** @use HasFactory<AccountDeletionRequestFactory> */
    use HasFactory;

    public const GRACE_DAYS = 30;
    public const TOKEN_TTL_HOURS = 24;

    protected $fillable = [
        'user_id',
        'status',
        'confirmation_token',
        'requested_at',
        'confirmed_at',
        'scheduled_purge_at',
        'cancelled_at',
        'cancelled_by',
        'cancel_reason',
        'admin_notes',
        'purged_at',
    ];

    protected $casts = [
        'requested_at'       => 'datetime',
        'confirmed_at'       => 'datetime',
        'scheduled_purge_at' => 'datetime',
        'cancelled_at'       => 'datetime',
        'purged_at'          => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function canceller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public static function startFor(User $user): self
    {
        return self::create([
            'user_id'            => $user->id,
            'status'             => 'requested',
            'confirmation_token' => Str::random(64),
            'requested_at'       => now(),
        ]);
    }

    public function confirm(): void
    {
        if ($this->status !== 'requested') {
            throw new LogicException("Cannot confirm from status {$this->status}");
        }

        $this->update([
            'status'             => 'confirmed',
            'confirmed_at'       => now(),
            'confirmation_token' => null,
            'scheduled_purge_at' => now()->addDays(self::GRACE_DAYS),
        ]);
    }

    public function cancel(User $by, ?string $reason = null): void
    {
        if (! in_array($this->status, ['requested', 'confirmed'], true)) {
            throw new LogicException("Cannot cancel from status {$this->status}");
        }

        $this->update([
            'status'        => 'cancelled',
            'cancelled_at'  => now(),
            'cancelled_by'  => $by->id,
            'cancel_reason' => $reason,
        ]);
    }

    public function markPurged(): void
    {
        $this->update([
            'status'             => 'purged',
            'purged_at'          => now(),
            'confirmation_token' => null,
        ]);
    }

    public function isTokenExpired(): bool
    {
        return $this->requested_at->diffInHours(now()) >= self::TOKEN_TTL_HOURS;
    }

    public function scopeRequested(Builder $q): Builder
    {
        return $q->where('status', 'requested');
    }

    public function scopeConfirmed(Builder $q): Builder
    {
        return $q->where('status', 'confirmed');
    }

    public function scopeDueForPurge(Builder $q): Builder
    {
        return $q->where('status', 'confirmed')
            ->whereNotNull('scheduled_purge_at')
            ->where('scheduled_purge_at', '<=', now())
            ->whereNull('cancelled_at');
    }
}
```

- [ ] **Step 1.6 — Write the factory**

Create `database/factories/AccountDeletionRequestFactory.php`:

```php
<?php

namespace Database\Factories;

use App\Models\AccountDeletionRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<AccountDeletionRequest>
 */
class AccountDeletionRequestFactory extends Factory
{
    protected $model = AccountDeletionRequest::class;

    public function definition(): array
    {
        return [
            'user_id'            => User::factory(),
            'status'             => 'requested',
            'confirmation_token' => Str::random(64),
            'requested_at'       => now(),
        ];
    }

    public function confirmed(): self
    {
        return $this->state(fn () => [
            'status'             => 'confirmed',
            'confirmation_token' => null,
            'confirmed_at'       => now(),
            'scheduled_purge_at' => now()->addDays(AccountDeletionRequest::GRACE_DAYS),
        ]);
    }

    public function cancelled(): self
    {
        return $this->state(fn () => [
            'status'        => 'cancelled',
            'cancelled_at'  => now(),
        ]);
    }

    public function purged(): self
    {
        return $this->state(fn () => [
            'status'    => 'purged',
            'purged_at' => now(),
        ]);
    }
}
```

- [ ] **Step 1.7 — Add the `deletionRequest` relation to `User`**

In `app/Models/User.php`, inside the class, add imports and methods:

```php
use Illuminate\Database\Eloquent\Relations\HasOne;

// ... existing methods ...

public function deletionRequest(): HasOne
{
    return $this->hasOne(AccountDeletionRequest::class);
}

public function hasPendingDeletion(): bool
{
    return $this->deletionRequest()
        ->whereIn('status', ['requested', 'confirmed'])
        ->exists();
}
```

- [ ] **Step 1.8 — Run migrations and tests**

Run: `php artisan migrate`
Run: `./vendor/bin/pest tests/Unit/AccountDeletionRequestTest.php`
Expected: 8 passed.

- [ ] **Step 1.9 — Commit**

```bash
git add database/migrations/2026_04_14_0901*.php \
        database/migrations/2026_04_14_0902*.php \
        database/migrations/2026_04_14_0900*.php \
        app/Models/AccountDeletionRequest.php \
        app/Models/User.php \
        database/factories/AccountDeletionRequestFactory.php \
        tests/Unit/AccountDeletionRequestTest.php
git commit -m "feat(account-deletion): add AccountDeletionRequest model and migrations"
```

---

## Task 2: Legal pages (CGU, Privacy, Mentions) + footer links

**Files:**
- Create: `resources/views/pages/cgu.blade.php`
- Create: `resources/views/pages/politique-confidentialite.blade.php`
- Create: `resources/views/pages/mentions-legales.blade.php`
- Modify: `routes/web.php`
- Modify: `resources/views/partials/footer.blade.php`
- Create: `tests/Feature/LegalPagesTest.php`

- [ ] **Step 2.1 — Write the feature test**

Create `tests/Feature/LegalPagesTest.php`:

```php
<?php

it('serves the CGU page publicly', function () {
    $this->get('/cgu')
        ->assertOk()
        ->assertSee('Conditions Générales d\'Utilisation', false)
        ->assertSee('Bassila Emergence')
        ->assertSee('Dernière mise à jour');
});

it('serves the privacy policy publicly', function () {
    $this->get('/politique-de-confidentialite')
        ->assertOk()
        ->assertSee('Politique de confidentialité')
        ->assertSee('contact@bassila-emergence.org')
        ->assertSee('APDP')
        ->assertSee('RGPD');
});

it('serves the legal mentions publicly', function () {
    $this->get('/mentions-legales')
        ->assertOk()
        ->assertSee('Mentions légales')
        ->assertSee('Bassila Emergence');
});

it('links the legal pages from the footer', function () {
    $html = $this->get('/')->getContent();

    expect($html)->toContain(route('pages.cgu'))
        ->and($html)->toContain(route('pages.privacy'))
        ->and($html)->toContain(route('pages.legal'));
});
```

- [ ] **Step 2.2 — Run the test to verify failure**

Run: `./vendor/bin/pest tests/Feature/LegalPagesTest.php`
Expected: FAIL (404s and `route()` errors).

- [ ] **Step 2.3 — Add the routes**

In `routes/web.php`, after the existing `/a-propos-de-bassila` line:

```php
Route::view('/cgu', 'pages.cgu')->name('pages.cgu');
Route::view('/politique-de-confidentialite', 'pages.politique-confidentialite')->name('pages.privacy');
Route::view('/mentions-legales', 'pages.mentions-legales')->name('pages.legal');
```

- [ ] **Step 2.4 — Create the CGU view**

Create `resources/views/pages/cgu.blade.php`. Follow the structure of `pages/qui-sommes-nous.blade.php` (SeoData header, breadcrumbs, `<h1>`, prose sections). The body MUST contain all 12 sections from the spec. Use this skeleton (fill each `<section>` with complete French text; keep prose concise but legally explicit):

```blade
@php
    use App\Support\Seo\SeoData;

    $seo = SeoData::default()
        ->withTitle('Conditions Générales d\'Utilisation')
        ->withDescription('Conditions Générales d\'Utilisation de la plateforme Bassila Emergence.')
        ->withOgType('website');

    $version = '1.0';
    $updatedAt = '14 avril 2026';
@endphp
@extends('layouts.app')
@section('title', 'Conditions Générales d\'Utilisation')
@section('description', 'CGU de Bassila Emergence.')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <x-breadcrumbs :items="[
        ['name' => 'Accueil', 'url' => route('home')],
        ['name' => 'CGU', 'url' => null],
    ]" :with-json-ld="true" />

    <header class="mb-10">
        <p class="text-[#DC143C] text-xs font-semibold uppercase tracking-widest mb-3">Informations légales</p>
        <h1 class="text-4xl font-bold text-[#111827] leading-tight" style="font-family: 'Lora', serif;">
            Conditions Générales d'Utilisation
        </h1>
        <p class="text-sm text-gray-500 mt-3">Version {{ $version }} — Dernière mise à jour : {{ $updatedAt }}</p>
    </header>

    <nav aria-label="Sommaire" class="mb-10 p-6 bg-gray-50 rounded-sm">
        <p class="text-xs font-semibold uppercase tracking-widest text-gray-500 mb-3">Sommaire</p>
        <ol class="list-decimal list-inside text-sm space-y-1">
            <li><a href="#objet" class="hover:underline">Objet et éditeur</a></li>
            <li><a href="#acces" class="hover:underline">Accès au service</a></li>
            <li><a href="#compte" class="hover:underline">Inscription et compte</a></li>
            <li><a href="#contenus" class="hover:underline">Contenus publiés</a></li>
            <li><a href="#conduite" class="hover:underline">Règles de conduite</a></li>
            <li><a href="#moderation" class="hover:underline">Modération</a></li>
            <li><a href="#pi" class="hover:underline">Propriété intellectuelle du site</a></li>
            <li><a href="#responsabilite" class="hover:underline">Responsabilité</a></li>
            <li><a href="#resiliation" class="hover:underline">Durée, résiliation, suppression de compte</a></li>
            <li><a href="#modifications" class="hover:underline">Modifications des CGU</a></li>
            <li><a href="#droit" class="hover:underline">Droit applicable et juridiction</a></li>
            <li><a href="#contact" class="hover:underline">Contact</a></li>
        </ol>
    </nav>

    <div class="prose prose-lg max-w-none">
        <section id="objet">
            <h2>1. Objet et éditeur</h2>
            <p>Les présentes Conditions Générales d'Utilisation (ci-après « CGU ») régissent l'accès à la plateforme Bassila Emergence (ci-après « la plateforme »), éditée par le collectif communautaire Bassila Emergence, basé à Bassila, Département de la Donga, République du Bénin. Contact : contact@bassila-emergence.org.</p>
            <p>Toute utilisation de la plateforme implique l'acceptation pleine et entière des présentes CGU.</p>
        </section>

        <section id="acces">
            <h2>2. Accès au service</h2>
            <p>L'accès à la consultation de la plateforme (annuaire public, blog) est gratuit et libre. La publication d'articles est soumise à invitation préalable par un administrateur. L'inscription à l'annuaire est libre pour toute personne justifiant d'un lien avec la commune de Bassila.</p>
        </section>

        <section id="compte">
            <h2>3. Inscription et compte</h2>
            <p>L'inscription est réservée aux personnes âgées de seize (16) ans au moins. L'utilisateur s'engage à fournir des informations exactes et à jour. Un seul compte par personne physique est autorisé. L'usurpation d'identité entraîne la suspension immédiate du compte.</p>
        </section>

        <section id="contenus">
            <h2>4. Contenus publiés par les membres</h2>
            <p>L'utilisateur reste propriétaire des contenus qu'il publie (profil, articles, commentaires). En les publiant, il accorde à Bassila Emergence une licence non-exclusive, gratuite et mondiale, limitée à la diffusion de ces contenus sur la plateforme et aux canaux de communication qui en dépendent (newsletter, réseaux sociaux officiels). Cette licence s'éteint à la suppression du contenu ou du compte, sous réserve des dispositions de l'article 9.</p>
        </section>

        <section id="conduite">
            <h2>5. Règles de conduite</h2>
            <p>Sont strictement interdits : les contenus à caractère haineux, discriminatoire, diffamatoire, injurieux, pornographique ou illicite ; le spam ; l'usurpation d'identité ; la publication de données personnelles d'autrui sans consentement ; toute tentative d'atteinte à la sécurité de la plateforme.</p>
        </section>

        <section id="moderation">
            <h2>6. Modération</h2>
            <p>L'équipe Bassila Emergence se réserve le droit de retirer tout contenu contraire aux CGU et de suspendre ou supprimer tout compte en cas de manquement grave ou répété. Toute décision peut être contestée par email à contact@bassila-emergence.org ; une réponse est apportée dans un délai raisonnable, au plus tard sous trente (30) jours.</p>
        </section>

        <section id="pi">
            <h2>7. Propriété intellectuelle du site</h2>
            <p>Le logo, le design, le code source, la base de données et les éléments graphiques originaux de la plateforme sont la propriété exclusive de Bassila Emergence. Toute reproduction non autorisée est interdite.</p>
        </section>

        <section id="responsabilite">
            <h2>8. Responsabilité</h2>
            <p>La plateforme est fournie « en l'état », sans garantie de disponibilité continue. Bassila Emergence ne saurait être tenue responsable des contenus publiés par ses membres, sous réserve des obligations de retrait dès notification d'un contenu manifestement illicite. La responsabilité de Bassila Emergence est limitée dans les conditions prévues par le droit béninois applicable.</p>
        </section>

        <section id="resiliation">
            <h2>9. Durée, résiliation, suppression de compte</h2>
            <p>Tout membre peut demander la suppression de son compte à tout moment depuis son espace personnel. La procédure est détaillée dans la <a href="{{ route('pages.privacy') }}#suppression">Politique de confidentialité</a> : un délai de grâce de trente (30) jours précède la purge définitive, pendant lequel la demande peut être annulée. À l'issue de ce délai, le profil est supprimé ; les articles publiés et commentaires sont anonymisés afin de préserver l'intégrité éditoriale de la plateforme, dans le cadre de l'intérêt légitime d'information prévu au RGPD article 17.3.a.</p>
        </section>

        <section id="modifications">
            <h2>10. Modifications des CGU</h2>
            <p>Bassila Emergence se réserve le droit de modifier les présentes CGU. En cas de changement substantiel, les membres seront informés par email au moins trente (30) jours avant l'entrée en vigueur des nouvelles conditions. La poursuite de l'utilisation du service après cette date vaut acceptation.</p>
        </section>

        <section id="droit">
            <h2>11. Droit applicable et juridiction</h2>
            <p>Les présentes CGU sont soumises au droit béninois. Tout litige fera l'objet d'une tentative de règlement amiable préalable par échange écrit. À défaut d'accord dans un délai de trente (30) jours, les tribunaux de Cotonou seront seuls compétents.</p>
        </section>

        <section id="contact">
            <h2>12. Contact</h2>
            <p>Pour toute question relative aux présentes CGU : contact@bassila-emergence.org.</p>
        </section>
    </div>
</div>
@endsection
```

- [ ] **Step 2.5 — Create the Politique de confidentialité view**

Create `resources/views/pages/politique-confidentialite.blade.php`. Same SEO/header/breadcrumb skeleton as CGU. Body MUST contain all 13 sections listed in the spec, including a data table (HTML `<table>` inside prose) for section 2 and explicit mentions of «RGPD», «code numérique du Bénin», «APDP», «CNIL», «CEDEAO», «contact@bassila-emergence.org», «session Laravel», «XSRF-TOKEN», «remember_web», «pixel newsletter». Add an anchor `<section id="suppression">` for section 8.

Use the same visual scaffold (breadcrumbs, `<h1>`, version/date header, sommaire nav). Header title: `Politique de confidentialité`. Include this specific block for the right-to-deletion procedure (section 8) so the test anchors match:

```blade
<section id="suppression">
    <h2>8. Suppression de compte</h2>
    <p>Tout membre peut demander la suppression de son compte depuis la page de modification de son profil. La procédure est la suivante :</p>
    <ol>
        <li>Clic sur «&nbsp;Supprimer mon compte&nbsp;» dans la section «&nbsp;Zone dangereuse&nbsp;» du profil.</li>
        <li>Saisie du mot de passe et confirmation dans la fenêtre modale.</li>
        <li>Envoi d'un email de confirmation, valable 24 heures.</li>
        <li>Clic sur le lien de confirmation&nbsp;: le compte est désactivé et la purge est planifiée à trente (30) jours.</li>
        <li>Pendant ce délai, toute reconnexion propose l'annulation de la demande.</li>
        <li>À l'échéance, la purge est exécutée&nbsp;: le profil est supprimé, les articles et commentaires anonymisés, les données de compte (email, mot de passe) effacées. Les journaux d'audit sont conservés un (1) an pour des raisons de sécurité.</li>
    </ol>
</section>
```

- [ ] **Step 2.6 — Create the Mentions légales view**

Create `resources/views/pages/mentions-legales.blade.php`. Sections from spec. Use the same visual scaffold. For hébergeur and directeur de la publication, write the field with a visible `À renseigner au déploiement` placeholder that the operator will fill in production — this is intentional and documented in the spec.

- [ ] **Step 2.7 — Update the footer**

In `resources/views/partials/footer.blade.php`, replace the bottom-right links block (lines 59-62 of current file):

```blade
<div class="flex gap-5">
    <a href="{{ route('pages.privacy') }}" class="hover:text-white transition">Confidentialité</a>
    <a href="{{ route('pages.cgu') }}" class="hover:text-white transition">CGU</a>
    <a href="{{ route('pages.legal') }}" class="hover:text-white transition">Mentions légales</a>
</div>
```

- [ ] **Step 2.8 — Run the tests**

Run: `./vendor/bin/pest tests/Feature/LegalPagesTest.php`
Expected: 4 passed.

- [ ] **Step 2.9 — Commit**

```bash
git add resources/views/pages/cgu.blade.php \
        resources/views/pages/politique-confidentialite.blade.php \
        resources/views/pages/mentions-legales.blade.php \
        routes/web.php \
        resources/views/partials/footer.blade.php \
        tests/Feature/LegalPagesTest.php
git commit -m "feat(legal): add CGU, privacy policy and legal mentions pages"
```

---

## Task 3: Mandatory CGU checkbox on registration

**Files:**
- Modify: `app/Livewire/Auth/Register.php`
- Modify: `resources/views/livewire/auth/register.blade.php`
- Create: `tests/Feature/Auth/RegistrationAcceptsTermsTest.php`

- [ ] **Step 3.1 — Write the failing test**

Create `tests/Feature/Auth/RegistrationAcceptsTermsTest.php`:

```php
<?php

use App\Livewire\Auth\Register;
use Livewire\Livewire;

it('rejects registration when CGU checkbox is not checked', function () {
    Livewire::test(Register::class)
        ->set('first_name', 'Test')
        ->set('last_name', 'User')
        ->set('email', 'test@example.com')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->set('accepts_terms', false)
        ->call('register')
        ->assertHasErrors(['accepts_terms']);
});

it('accepts registration when CGU checkbox is checked', function () {
    Livewire::test(Register::class)
        ->set('first_name', 'Test')
        ->set('last_name', 'User')
        ->set('email', 'test@example.com')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->set('accepts_terms', true)
        ->call('register')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
});
```

- [ ] **Step 3.2 — Run tests to confirm failure**

Run: `./vendor/bin/pest tests/Feature/Auth/RegistrationAcceptsTermsTest.php`
Expected: FAIL (property missing).

- [ ] **Step 3.3 — Add `accepts_terms` to the Livewire component**

Modify `app/Livewire/Auth/Register.php`:

```php
public bool $accepts_terms = false;

protected function rules(): array
{
    return [
        'first_name'    => ['required', 'string', 'max:100'],
        'last_name'     => ['required', 'string', 'max:100'],
        'email'         => ['required', 'email', 'unique:users,email'],
        'password'      => ['required', 'min:8', 'confirmed'],
        'accepts_terms' => ['accepted'],
    ];
}

protected function messages(): array
{
    return [
        'accepts_terms.accepted' => 'Vous devez accepter les CGU et la Politique de confidentialité pour créer un compte.',
    ];
}
```

- [ ] **Step 3.4 — Add the checkbox in the view**

In `resources/views/livewire/auth/register.blade.php`, immediately before the submit button (after the `password_confirmation` block):

```blade
<div class="flex items-start gap-2">
    <input wire:model="accepts_terms" id="accepts_terms" type="checkbox"
           class="mt-1 h-4 w-4 border-gray-300 text-[#0066CC] focus:ring-[#0066CC]">
    <label for="accepts_terms" class="text-sm text-gray-600 leading-snug">
        J'accepte les
        <a href="{{ route('pages.cgu') }}" target="_blank" class="text-[#0066CC] hover:underline">CGU</a>
        et la
        <a href="{{ route('pages.privacy') }}" target="_blank" class="text-[#0066CC] hover:underline">Politique de confidentialité</a>.
    </label>
</div>
@error('accepts_terms') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
```

- [ ] **Step 3.5 — Make sure old passing registration tests still pass**

Update `tests/Feature/Auth/RegisterTest.php` — in the existing `it('can register a new user', ...)` test, add `->set('accepts_terms', true)` before `->call('register')`. Same for `it('rejects duplicate email on registration', ...)` and `it('rejects mismatched passwords', ...)`.

- [ ] **Step 3.6 — Run all auth tests**

Run: `./vendor/bin/pest tests/Feature/Auth/`
Expected: all green.

- [ ] **Step 3.7 — Commit**

```bash
git add app/Livewire/Auth/Register.php \
        resources/views/livewire/auth/register.blade.php \
        tests/Feature/Auth/RegistrationAcceptsTermsTest.php \
        tests/Feature/Auth/RegisterTest.php
git commit -m "feat(auth): require CGU acceptance on registration"
```

---

## Task 4: `DeleteAccount` Livewire component — request creation + email

**Files:**
- Create: `app/Livewire/Profile/DeleteAccount.php`
- Create: `resources/views/livewire/profile/delete-account.blade.php`
- Create: `app/Mail/AccountDeletionRequested.php` + view
- Create: `app/Policies/AccountDeletionRequestPolicy.php`
- Modify: `resources/views/livewire/profile/edit-profile.blade.php`
- Modify: `routes/web.php` (signed confirm/cancel routes, wired in Task 5)
- Create: `tests/Feature/Account/DeleteAccountFlowTest.php`

- [ ] **Step 4.1 — Write the first three failing tests**

Create `tests/Feature/Account/DeleteAccountFlowTest.php`:

```php
<?php

use App\Livewire\Profile\DeleteAccount;
use App\Mail\AccountDeletionRequested;
use App\Models\AccountDeletionRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

beforeEach(function () {
    // Roles seeded for all tests that instantiate a User.
    app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    \Spatie\Permission\Models\Role::findOrCreate('admin');
    \Spatie\Permission\Models\Role::findOrCreate('member');
});

it('creates a deletion request and sends confirmation email', function () {
    Mail::fake();

    $user = User::factory()->create(['password' => Hash::make('secret1234')]);
    $user->assignRole('member');
    $this->actingAs($user);

    Livewire::test(DeleteAccount::class)
        ->set('password', 'secret1234')
        ->set('understood', true)
        ->call('submit')
        ->assertHasNoErrors();

    expect(AccountDeletionRequest::where('user_id', $user->id)->requested()->exists())->toBeTrue();
    Mail::assertQueued(AccountDeletionRequested::class, fn ($m) => $m->hasTo($user->email));
});

it('rejects when password is wrong', function () {
    $user = User::factory()->create(['password' => Hash::make('secret1234')]);
    $user->assignRole('member');
    $this->actingAs($user);

    Livewire::test(DeleteAccount::class)
        ->set('password', 'wrong')
        ->set('understood', true)
        ->call('submit')
        ->assertHasErrors(['password']);

    expect(AccountDeletionRequest::where('user_id', $user->id)->exists())->toBeFalse();
});

it('rejects when understood is not checked', function () {
    $user = User::factory()->create(['password' => Hash::make('secret1234')]);
    $user->assignRole('member');
    $this->actingAs($user);

    Livewire::test(DeleteAccount::class)
        ->set('password', 'secret1234')
        ->set('understood', false)
        ->call('submit')
        ->assertHasErrors(['understood']);
});

it('rejects when the user is the last active admin', function () {
    $admin = User::factory()->create(['password' => Hash::make('secret1234')]);
    $admin->assignRole('admin');
    $this->actingAs($admin);

    Livewire::test(DeleteAccount::class)
        ->set('password', 'secret1234')
        ->set('understood', true)
        ->call('submit')
        ->assertHasErrors(['lockout']);

    expect(AccountDeletionRequest::count())->toBe(0);
});
```

- [ ] **Step 4.2 — Run tests to verify failure**

Run: `./vendor/bin/pest tests/Feature/Account/DeleteAccountFlowTest.php`
Expected: FAIL (component missing).

- [ ] **Step 4.3 — Create the Mailable**

Create `app/Mail/AccountDeletionRequested.php`:

```php
<?php

namespace App\Mail;

use App\Models\AccountDeletionRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountDeletionRequested extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public AccountDeletionRequest $request) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Confirmation de la suppression de votre compte');
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.account.deletion-requested',
            with: [
                'user'       => $this->request->user,
                'confirmUrl' => route('account.deletion.confirm', ['token' => $this->request->confirmation_token]),
                'ttlHours'   => AccountDeletionRequest::TOKEN_TTL_HOURS,
            ],
        );
    }
}
```

Create `resources/views/mail/account/deletion-requested.blade.php`:

```blade
<p>Bonjour {{ $user->first_name }},</p>

<p>Nous avons reçu une demande de suppression de votre compte Bassila Emergence.</p>

<p>Pour confirmer, cliquez sur le lien ci-dessous dans les {{ $ttlHours }} heures&nbsp;:</p>

<p><a href="{{ $confirmUrl }}">Confirmer la suppression de mon compte</a></p>

<p>Une fois la demande confirmée, votre compte sera désactivé et définitivement supprimé sous 30 jours. Pendant ce délai, vous pourrez annuler la demande à tout moment en vous reconnectant.</p>

<p>Si vous n'êtes pas à l'origine de cette demande, ignorez simplement cet email&nbsp;: aucune suppression n'aura lieu tant que vous n'aurez pas cliqué sur le lien.</p>

<p>L'équipe Bassila Emergence</p>
```

- [ ] **Step 4.4 — Create the Livewire component**

Create `app/Livewire/Profile/DeleteAccount.php`:

```php
<?php

namespace App\Livewire\Profile;

use App\Mail\AccountDeletionRequested;
use App\Models\AccountDeletionRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Component;

class DeleteAccount extends Component
{
    #[Validate('required|string')]
    public string $password = '';

    #[Validate('accepted')]
    public bool $understood = false;

    public bool $submitted = false;

    public function submit(): void
    {
        $this->validate([
            'password'   => ['required', 'string'],
            'understood' => ['accepted'],
        ]);

        $user = auth()->user();

        if (! Hash::check($this->password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => 'Mot de passe incorrect.',
            ]);
        }

        if (User::isLastActiveAdmin($user)) {
            throw ValidationException::withMessages([
                'lockout' => 'Vous êtes le dernier administrateur actif. Retirez d\'abord ce rôle via un autre administrateur.',
            ]);
        }

        if ($user->hasPendingDeletion()) {
            throw ValidationException::withMessages([
                'password' => 'Une demande de suppression est déjà en cours.',
            ]);
        }

        $req = AccountDeletionRequest::startFor($user);

        Mail::to($user->email)->queue(new AccountDeletionRequested($req));

        $this->reset(['password', 'understood']);
        $this->submitted = true;
    }

    public function render()
    {
        return view('livewire.profile.delete-account');
    }
}
```

- [ ] **Step 4.5 — Create the component view**

Create `resources/views/livewire/profile/delete-account.blade.php`:

```blade
<section class="mt-12 border border-red-200 p-6 bg-red-50" x-data="{ open: false }">
    <h2 class="text-lg font-bold text-red-800 mb-2">Zone dangereuse</h2>
    <p class="text-sm text-red-900 mb-4">
        La suppression de votre compte entraîne la disparition définitive de votre profil. Vos articles et commentaires seront conservés de manière anonyme pour préserver la continuité éditoriale. Cette action peut être annulée pendant 30 jours.
    </p>

    @if ($submitted)
        <p class="text-sm text-green-800 bg-green-50 border border-green-200 p-3">
            Demande enregistrée. Vérifiez votre boîte email pour confirmer la suppression.
        </p>
    @else
        <button type="button" @click="open = true"
                class="bg-red-700 hover:bg-red-800 text-white text-sm font-semibold px-4 py-2">
            Supprimer mon compte
        </button>

        <div x-show="open" x-cloak
             class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div @click.outside="open = false"
                 class="bg-white max-w-md w-full p-6">
                <h3 class="text-base font-bold text-gray-900 mb-3">Confirmer la suppression</h3>
                <form wire:submit.prevent="submit" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1.5">Mot de passe</label>
                        <input wire:model="password" type="password" autocomplete="current-password"
                               class="w-full border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:border-red-500">
                        @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        @error('lockout')  <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <label class="flex gap-2 text-sm text-gray-700">
                        <input wire:model="understood" type="checkbox" class="mt-1">
                        <span>Je comprends que mon profil sera supprimé et que mes articles seront anonymisés.</span>
                    </label>
                    @error('understood') <p class="text-xs text-red-600">{{ $message }}</p> @enderror

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="open = false"
                                class="text-sm text-gray-600 hover:text-gray-900">Annuler</button>
                        <button type="submit"
                                class="bg-red-700 hover:bg-red-800 text-white text-sm font-semibold px-4 py-2">
                            Confirmer la demande
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</section>
```

- [ ] **Step 4.6 — Embed the component in the edit profile view**

In `resources/views/livewire/profile/edit-profile.blade.php`, add at the very bottom of the outer container (after the last form block but before the closing `</div>`):

```blade
@livewire('profile.delete-account')
```

- [ ] **Step 4.7 — Run the tests**

Run: `./vendor/bin/pest tests/Feature/Account/DeleteAccountFlowTest.php`
Expected: 4 passed.

- [ ] **Step 4.8 — Commit**

```bash
git add app/Livewire/Profile/DeleteAccount.php \
        resources/views/livewire/profile/delete-account.blade.php \
        app/Mail/AccountDeletionRequested.php \
        resources/views/mail/account/deletion-requested.blade.php \
        resources/views/livewire/profile/edit-profile.blade.php \
        tests/Feature/Account/DeleteAccountFlowTest.php
git commit -m "feat(account-deletion): add self-serve deletion request component"
```

---

## Task 5: Email confirmation flow (signed link → `confirmed`)

**Files:**
- Create: `app/Livewire/Account/ConfirmDeletion.php` + view
- Create: `app/Mail/AccountDeletionConfirmed.php` + view
- Modify: `routes/web.php`
- Modify: `tests/Feature/Account/DeleteAccountFlowTest.php` — append new tests

- [ ] **Step 5.1 — Append tests for the confirm flow**

Add to `tests/Feature/Account/DeleteAccountFlowTest.php`:

```php
it('confirms a deletion when clicking a valid token URL, disables the account, and schedules the purge', function () {
    Mail::fake();

    $user = User::factory()->create(['is_active' => true]);
    $user->assignRole('member');
    $req = AccountDeletionRequest::startFor($user);
    $token = $req->confirmation_token;

    $this->get(route('account.deletion.confirm', ['token' => $token]))
        ->assertOk()
        ->assertSee('confirmée');

    $user->refresh();
    $req->refresh();

    expect($req->status)->toBe('confirmed')
        ->and($req->scheduled_purge_at)->not->toBeNull()
        ->and($user->is_active)->toBeFalse();

    Mail::assertQueued(\App\Mail\AccountDeletionConfirmed::class);
});

it('rejects an expired confirmation token (>24h)', function () {
    $user = User::factory()->create();
    $user->assignRole('member');
    $req = AccountDeletionRequest::startFor($user);
    $req->update(['requested_at' => now()->subHours(25)]);
    $token = $req->confirmation_token;

    $this->get(route('account.deletion.confirm', ['token' => $token]))
        ->assertOk()
        ->assertSee('expiré');

    $req->refresh();
    expect($req->status)->toBe('cancelled');
});

it('rejects an unknown token with 404', function () {
    $this->get(route('account.deletion.confirm', ['token' => str_repeat('x', 64)]))
        ->assertNotFound();
});
```

- [ ] **Step 5.2 — Run to verify failure**

Run: `./vendor/bin/pest tests/Feature/Account/DeleteAccountFlowTest.php`
Expected: the three new tests fail; earlier tests still pass.

- [ ] **Step 5.3 — Build the confirm mailable**

Create `app/Mail/AccountDeletionConfirmed.php`:

```php
<?php

namespace App\Mail;

use App\Models\AccountDeletionRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountDeletionConfirmed extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public AccountDeletionRequest $request) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Suppression de votre compte confirmée');
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.account.deletion-confirmed',
            with: [
                'user'     => $this->request->user,
                'purgeAt'  => $this->request->scheduled_purge_at,
            ],
        );
    }
}
```

Create `resources/views/mail/account/deletion-confirmed.blade.php`:

```blade
<p>Bonjour {{ $user->first_name }},</p>

<p>Votre demande de suppression a bien été confirmée. Votre compte est désormais désactivé.</p>

<p>La purge définitive aura lieu le <strong>{{ $purgeAt->isoFormat('D MMMM YYYY') }}</strong>. Jusqu'à cette date, vous pouvez annuler la demande en vous reconnectant à l'adresse habituelle du site.</p>

<p>L'équipe Bassila Emergence</p>
```

- [ ] **Step 5.4 — Build the ConfirmDeletion Livewire component**

Create `app/Livewire/Account/ConfirmDeletion.php`:

```php
<?php

namespace App\Livewire\Account;

use App\Mail\AccountDeletionConfirmed;
use App\Models\AccountDeletionRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Layout;
use Livewire\Component;

class ConfirmDeletion extends Component
{
    public AccountDeletionRequest $request;
    public string $state;

    public function mount(string $token): void
    {
        $req = AccountDeletionRequest::where('confirmation_token', $token)->first();

        abort_unless($req, 404);

        if ($req->status !== 'requested') {
            $this->request = $req;
            $this->state = 'already-handled';
            return;
        }

        if ($req->isTokenExpired()) {
            $req->cancel($req->user, 'token-expired');
            $this->request = $req;
            $this->state = 'expired';
            return;
        }

        $req->confirm();
        $req->user->update(['is_active' => false]);

        if (Auth::check() && Auth::id() === $req->user_id) {
            Auth::logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
        }

        Mail::to($req->user->email)->queue(new AccountDeletionConfirmed($req));

        $this->request = $req;
        $this->state = 'confirmed';
    }

    #[Layout('layouts.guest')]
    public function render()
    {
        return view('livewire.account.confirm-deletion');
    }
}
```

Create `resources/views/livewire/account/confirm-deletion.blade.php`:

```blade
<div class="max-w-md mx-auto text-center py-12">
    @if ($state === 'confirmed')
        <h1 class="text-2xl font-bold text-gray-900 mb-4">Suppression confirmée</h1>
        <p class="text-sm text-gray-600">
            Votre compte est désormais désactivé. La suppression définitive interviendra le
            <strong>{{ $request->scheduled_purge_at->isoFormat('D MMMM YYYY') }}</strong>.
            Vous pouvez annuler à tout moment en vous reconnectant.
        </p>
    @elseif ($state === 'expired')
        <h1 class="text-2xl font-bold text-gray-900 mb-4">Lien expiré</h1>
        <p class="text-sm text-gray-600">
            Ce lien de confirmation a expiré (validité 24 heures). Relancez une demande depuis votre profil si vous souhaitez toujours supprimer votre compte.
        </p>
    @else
        <h1 class="text-2xl font-bold text-gray-900 mb-4">Demande déjà traitée</h1>
        <p class="text-sm text-gray-600">Cette demande a déjà été confirmée, annulée ou traitée.</p>
    @endif
</div>
```

- [ ] **Step 5.5 — Wire the route**

In `routes/web.php`, near the deletion-related routes (create them now):

```php
use App\Livewire\Account\ConfirmDeletion;

Route::get('/compte/suppression/confirmer/{token}', ConfirmDeletion::class)
    ->name('account.deletion.confirm');
```

- [ ] **Step 5.6 — Run tests**

Run: `./vendor/bin/pest tests/Feature/Account/DeleteAccountFlowTest.php`
Expected: all tests pass.

- [ ] **Step 5.7 — Commit**

```bash
git add app/Livewire/Account/ConfirmDeletion.php \
        resources/views/livewire/account/confirm-deletion.blade.php \
        app/Mail/AccountDeletionConfirmed.php \
        resources/views/mail/account/deletion-confirmed.blade.php \
        routes/web.php \
        tests/Feature/Account/DeleteAccountFlowTest.php
git commit -m "feat(account-deletion): confirm deletion via signed email link"
```

---

## Task 6: Cancel during grace period + login redirect middleware

**Files:**
- Create: `app/Http/Middleware/RedirectIfDeletionPending.php`
- Create: `app/Livewire/Account/CancelDeletion.php` + view
- Create: `app/Mail/AccountDeletionCancelled.php` + view
- Modify: `routes/web.php`
- Modify: `bootstrap/app.php`
- Modify: `app/Livewire/Auth/Login.php` (to allow login even when `is_active=false` if deletion is pending)
- Modify: `tests/Feature/Account/DeleteAccountFlowTest.php`

- [ ] **Step 6.1 — Append tests for the cancel flow**

Add to `tests/Feature/Account/DeleteAccountFlowTest.php`:

```php
it('lets a user in grace period cancel their pending deletion', function () {
    Mail::fake();

    $user = User::factory()->create(['is_active' => false, 'password' => Hash::make('secret1234')]);
    $user->assignRole('member');
    $req = AccountDeletionRequest::factory()->for($user)->confirmed()->create();

    // Re-login: normally blocked by is_active=false, but allowed because deletion pending.
    $this->post(route('login'), [
        'email'    => $user->email,
        'password' => 'secret1234',
    ]);

    $this->actingAs($user->fresh());
    $this->get('/')->assertRedirect(route('account.deletion.cancel'));

    Livewire::test(\App\Livewire\Account\CancelDeletion::class)
        ->call('cancel')
        ->assertRedirect('/');

    $req->refresh();
    $user->refresh();

    expect($req->status)->toBe('cancelled')
        ->and($user->is_active)->toBeTrue();

    Mail::assertQueued(\App\Mail\AccountDeletionCancelled::class);
});
```

- [ ] **Step 6.2 — Run to verify failure**

Run: `./vendor/bin/pest tests/Feature/Account/DeleteAccountFlowTest.php --filter="grace period"`
Expected: failures (middleware / route / component missing).

- [ ] **Step 6.3 — Create the cancel mailable**

Create `app/Mail/AccountDeletionCancelled.php`:

```php
<?php

namespace App\Mail;

use App\Models\AccountDeletionRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountDeletionCancelled extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public AccountDeletionRequest $request) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Votre demande de suppression a été annulée');
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.account.deletion-cancelled',
            with: ['user' => $this->request->user],
        );
    }
}
```

Create `resources/views/mail/account/deletion-cancelled.blade.php`:

```blade
<p>Bonjour {{ $user->first_name }},</p>

<p>Votre demande de suppression de compte a bien été annulée. Votre compte est de nouveau actif et vous pouvez continuer à utiliser la plateforme normalement.</p>

<p>L'équipe Bassila Emergence</p>
```

- [ ] **Step 6.4 — Create the cancel component**

Create `app/Livewire/Account/CancelDeletion.php`:

```php
<?php

namespace App\Livewire\Account;

use App\Mail\AccountDeletionCancelled;
use App\Models\AccountDeletionRequest;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Layout;
use Livewire\Component;

class CancelDeletion extends Component
{
    public ?AccountDeletionRequest $request = null;

    public function mount(): void
    {
        $user = auth()->user();
        abort_unless($user, 403);

        $this->request = $user->deletionRequest()
            ->whereIn('status', ['requested', 'confirmed'])
            ->first();

        abort_unless($this->request, 404);
    }

    public function cancel()
    {
        $user = auth()->user();
        $this->request->cancel($user);
        $user->update(['is_active' => true]);

        Mail::to($user->email)->queue(new AccountDeletionCancelled($this->request));

        session()->flash('status', 'Suppression annulée. Bienvenue à nouveau.');

        return redirect('/');
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.account.cancel-deletion');
    }
}
```

Create `resources/views/livewire/account/cancel-deletion.blade.php`:

```blade
<div class="max-w-xl mx-auto py-12 px-4">
    <h1 class="text-2xl font-bold text-gray-900 mb-4">Ton compte est en cours de suppression</h1>
    <p class="text-sm text-gray-700 mb-6">
        Ta demande est planifiée pour le
        <strong>{{ $request->scheduled_purge_at?->isoFormat('D MMMM YYYY') ?? '—' }}</strong>.
        Tu peux encore l'annuler&nbsp;: ton compte redeviendra actif immédiatement.
    </p>
    <button wire:click="cancel"
            class="bg-[#0066CC] hover:bg-blue-800 text-white font-semibold px-5 py-3 text-sm">
        Annuler la suppression et réactiver mon compte
    </button>
</div>
```

- [ ] **Step 6.5 — Create the middleware**

Create `app/Http/Middleware/RedirectIfDeletionPending.php`:

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfDeletionPending
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->hasPendingDeletion()) {
            return $next($request);
        }

        $allowed = [
            'account.deletion.cancel',
            'account.deletion.confirm',
            'logout',
        ];

        if (in_array($request->route()?->getName(), $allowed, true)) {
            return $next($request);
        }

        return redirect()->route('account.deletion.cancel');
    }
}
```

- [ ] **Step 6.6 — Register the route and middleware**

In `routes/web.php`, add:

```php
use App\Livewire\Account\CancelDeletion;

Route::get('/compte/suppression/annuler', CancelDeletion::class)
    ->middleware('auth')
    ->name('account.deletion.cancel');
```

In `bootstrap/app.php`, extend `$middleware->alias([...])`:

```php
$middleware->alias([
    'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
    'registration.check' => \App\Http\Middleware\CheckRegistrationOpen::class,
    'deletion.redirect' => \App\Http\Middleware\RedirectIfDeletionPending::class,
]);

$middleware->appendToGroup('web', [
    \App\Http\Middleware\MaintenanceModeCheck::class,
    \App\Http\Middleware\RedirectIfDeletionPending::class,
]);
```

- [ ] **Step 6.7 — Allow login while deletion is pending**

In `app/Livewire/Auth/Login.php`, after successful authentication but before the `is_active` guard (locate the `authenticate()` method), make the `is_active=false` check an exception when a pending deletion exists:

```php
if (! $user->is_active && ! $user->hasPendingDeletion()) {
    Auth::logout();
    throw ValidationException::withMessages([
        'email' => 'Ce compte est désactivé.',
    ]);
}
```

(If the current Login component has a different structure, apply the equivalent bypass: users with a pending deletion MUST be able to authenticate in order to cancel.)

- [ ] **Step 6.8 — Run tests**

Run: `./vendor/bin/pest tests/Feature/Account/DeleteAccountFlowTest.php`
Expected: all passing.

- [ ] **Step 6.9 — Commit**

```bash
git add app/Http/Middleware/RedirectIfDeletionPending.php \
        app/Livewire/Account/CancelDeletion.php \
        resources/views/livewire/account/cancel-deletion.blade.php \
        app/Mail/AccountDeletionCancelled.php \
        resources/views/mail/account/deletion-cancelled.blade.php \
        bootstrap/app.php \
        routes/web.php \
        app/Livewire/Auth/Login.php \
        tests/Feature/Account/DeleteAccountFlowTest.php
git commit -m "feat(account-deletion): grace-period cancellation with middleware redirect"
```

---

## Task 7: `accounts:purge-expired` command + anonymisation + author accessors

**Files:**
- Create: `app/Console/Commands/PurgeExpiredAccounts.php`
- Create: `app/Mail/AccountDeletionCompleted.php` + view
- Modify: `app/Models/BlogPost.php` — accessor `displayAuthorName`
- Modify: `app/Models/BlogComment.php` — accessor `displayAuthorName`
- Modify: `app/Models/AccountDeletionRequest.php` — `purge()` method
- Modify: `routes/console.php` — schedule
- Modify: Blade views that render post/comment author to use `displayAuthorName`
- Create: `tests/Feature/Account/PurgeExpiredAccountsCommandTest.php`

- [ ] **Step 7.1 — Write failing tests**

Create `tests/Feature/Account/PurgeExpiredAccountsCommandTest.php`:

```php
<?php

use App\Mail\AccountDeletionCompleted;
use App\Models\AccountDeletionRequest;
use App\Models\BlogPost;
use App\Models\BlogComment;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    \Spatie\Permission\Models\Role::findOrCreate('member');
});

it('purges a confirmed request whose scheduled_purge_at has passed', function () {
    Mail::fake();

    $user = User::factory()->create();
    $user->assignRole('member');
    Profile::factory()->for($user)->create();
    $post = BlogPost::factory()->for($user)->create(['status' => 'published']);
    $comment = BlogComment::factory()->for($user)->create();

    $req = AccountDeletionRequest::factory()->for($user)->confirmed()->create([
        'scheduled_purge_at' => now()->subDay(),
    ]);

    $this->artisan('accounts:purge-expired')->assertExitCode(0);

    expect(User::find($user->id))->toBeNull()
        ->and(Profile::where('user_id', $user->id)->exists())->toBeFalse();

    $post->refresh();
    $comment->refresh();

    expect($post->user_id)->toBeNull()
        ->and($post->author_display_name)->toBe('Ancien membre')
        ->and($comment->user_id)->toBeNull()
        ->and($comment->author_display_name)->toBe('Membre supprimé');

    $req->refresh();
    expect($req->status)->toBe('purged')->and($req->purged_at)->not->toBeNull();

    Mail::assertQueued(AccountDeletionCompleted::class);
});

it('does not purge a confirmed request whose scheduled_purge_at is in the future', function () {
    $user = User::factory()->create();
    $user->assignRole('member');
    AccountDeletionRequest::factory()->for($user)->confirmed()->create([
        'scheduled_purge_at' => now()->addDays(5),
    ]);

    $this->artisan('accounts:purge-expired')->assertExitCode(0);

    expect(User::find($user->id))->not->toBeNull();
});

it('does not purge a cancelled request even if scheduled_purge_at has passed', function () {
    $user = User::factory()->create();
    $user->assignRole('member');
    AccountDeletionRequest::factory()->for($user)->confirmed()->create([
        'scheduled_purge_at' => now()->subDay(),
        'cancelled_at'       => now()->subHour(),
        'status'             => 'cancelled',
    ]);

    $this->artisan('accounts:purge-expired')->assertExitCode(0);

    expect(User::find($user->id))->not->toBeNull();
});
```

- [ ] **Step 7.2 — Run to verify failure**

Run: `./vendor/bin/pest tests/Feature/Account/PurgeExpiredAccountsCommandTest.php`
Expected: failures.

- [ ] **Step 7.3 — Add `purge()` to the model**

In `app/Models/AccountDeletionRequest.php`, add:

```php
use App\Mail\AccountDeletionCompleted;
use App\Models\BlogComment;
use App\Models\BlogPost;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

public function purge(): void
{
    if ($this->status !== 'confirmed') {
        throw new LogicException("Cannot purge from status {$this->status}");
    }

    $user = $this->user;
    $email = $user->email;
    $firstName = $user->first_name;

    DB::transaction(function () use ($user) {
        BlogPost::where('user_id', $user->id)->update([
            'user_id'             => null,
            'author_display_name' => 'Ancien membre',
        ]);

        BlogComment::where('user_id', $user->id)->update([
            'user_id'             => null,
            'author_display_name' => 'Membre supprimé',
        ]);

        // Newsletter subscription tied to the email.
        \App\Models\NewsletterSubscriber::where('email', $user->email)->delete();

        $user->profile()?->delete();
        $user->delete();

        $this->markPurged();
    });

    Mail::to($email)->queue(
        (new AccountDeletionCompleted($firstName))->afterCommit()
    );
}
```

Note: `afterCommit()` on the mailable ensures the email is only dispatched once the DB transaction succeeds.

- [ ] **Step 7.4 — Build the `AccountDeletionCompleted` mailable**

Create `app/Mail/AccountDeletionCompleted.php`:

```php
<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountDeletionCompleted extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $firstName) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Votre compte Bassila Emergence a été supprimé');
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.account.deletion-completed',
            with: ['firstName' => $this->firstName],
        );
    }
}
```

Create `resources/views/mail/account/deletion-completed.blade.php`:

```blade
<p>Bonjour {{ $firstName }},</p>

<p>Votre compte Bassila Emergence a été définitivement supprimé. Vos données personnelles (profil, email, contacts) ont été effacées. Vos articles et commentaires ont été anonymisés et restent publiés pour préserver la continuité éditoriale.</p>

<p>Nous vous remercions de la confiance que vous nous avez accordée et vous souhaitons une bonne continuation.</p>

<p>L'équipe Bassila Emergence</p>
```

- [ ] **Step 7.5 — Add display-name accessors on BlogPost and BlogComment**

In `app/Models/BlogPost.php`, add:

```php
public function getDisplayAuthorNameAttribute(): string
{
    if ($this->user) {
        return trim($this->user->first_name.' '.$this->user->last_name) ?: $this->user->email;
    }

    return $this->author_display_name ?? 'Ancien membre';
}
```

Add `author_display_name` to `$fillable`.

In `app/Models/BlogComment.php`, add:

```php
public function getDisplayAuthorNameAttribute(): string
{
    if ($this->user) {
        return trim($this->user->first_name.' '.$this->user->last_name) ?: $this->user->email;
    }

    return $this->author_display_name ?? 'Membre supprimé';
}
```

Add `author_display_name` to `$fillable`.

- [ ] **Step 7.6 — Update blade views that render post/comment author**

Search for occurrences of `$post->user->name` / `$post->user->first_name` / `$comment->user->...` in `resources/views/`:

Run: `grep -rn "post->user" resources/views resources/views/livewire`
Run: `grep -rn "comment->user" resources/views resources/views/livewire`

Replace author-display uses with `{{ $post->display_author_name }}` / `{{ $comment->display_author_name }}`. Keep `->user->id` / avatar-linked uses intact where they refer to the live user, but use `@if ($post->user) ... @endif` guards when the template links to a profile page. Example idiom:

```blade
<span>
    @if ($post->user)
        <a href="{{ route('profile.show', $post->user->profile) }}">{{ $post->display_author_name }}</a>
    @else
        {{ $post->display_author_name }}
    @endif
</span>
```

- [ ] **Step 7.7 — Build the artisan command**

Create `app/Console/Commands/PurgeExpiredAccounts.php`:

```php
<?php

namespace App\Console\Commands;

use App\Models\AccountDeletionRequest;
use Illuminate\Console\Command;

class PurgeExpiredAccounts extends Command
{
    protected $signature = 'accounts:purge-expired';
    protected $description = 'Purge accounts whose deletion grace period has expired';

    public function handle(): int
    {
        $count = 0;

        AccountDeletionRequest::dueForPurge()->with('user')->get()->each(function ($req) use (&$count) {
            if (! $req->user) {
                $req->markPurged();
                return;
            }

            try {
                $req->purge();
                $count++;
            } catch (\Throwable $e) {
                $this->error("Failed to purge request {$req->id}: {$e->getMessage()}");
                report($e);
            }
        });

        $this->info("Purged {$count} account(s).");
        return self::SUCCESS;
    }
}
```

- [ ] **Step 7.8 — Schedule the command**

In `routes/console.php`, replace the existing content with:

```php
<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('accounts:purge-expired')->dailyAt('03:00');
```

- [ ] **Step 7.9 — Run tests**

Run: `./vendor/bin/pest tests/Feature/Account/PurgeExpiredAccountsCommandTest.php`
Expected: 3 passed.

Run the full suite to make sure the view changes didn't break blog tests:
Run: `./vendor/bin/pest`
Expected: green.

- [ ] **Step 7.10 — Commit**

```bash
git add app/Console/Commands/PurgeExpiredAccounts.php \
        app/Mail/AccountDeletionCompleted.php \
        resources/views/mail/account/deletion-completed.blade.php \
        app/Models/AccountDeletionRequest.php \
        app/Models/BlogPost.php \
        app/Models/BlogComment.php \
        resources/views/ \
        routes/console.php \
        tests/Feature/Account/PurgeExpiredAccountsCommandTest.php
git commit -m "feat(account-deletion): daily purge command with content anonymisation"
```

---

## Task 8: Admin page — view, cancel, force-purge

**Files:**
- Create: `app/Livewire/Admin/DeletionRequests.php`
- Create: `resources/views/livewire/admin/deletion-requests.blade.php`
- Create: `app/Mail/AdminDeletionPendingWithContent.php` + view
- Modify: `app/Livewire/Account/ConfirmDeletion.php` — notify admin when user has published content
- Modify: `routes/web.php` — add admin route
- Modify: admin sidebar view (locate via `grep -rn "admin.users" resources/views`)
- Create: `tests/Feature/Admin/DeletionRequestsAdminTest.php`

- [ ] **Step 8.1 — Write failing tests**

Create `tests/Feature/Admin/DeletionRequestsAdminTest.php`:

```php
<?php

use App\Livewire\Admin\DeletionRequests;
use App\Mail\AdminDeletionPendingWithContent;
use App\Models\AccountDeletionRequest;
use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

beforeEach(function () {
    \Spatie\Permission\Models\Role::findOrCreate('admin');
    \Spatie\Permission\Models\Role::findOrCreate('member');
});

it('blocks non-admins', function () {
    $user = User::factory()->create();
    $user->assignRole('member');
    $this->actingAs($user);

    $this->get('/admin/suppressions')->assertForbidden();
});

it('lists all deletion requests for an admin', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $u = User::factory()->create(['first_name' => 'Alice']);
    $u->assignRole('member');
    AccountDeletionRequest::factory()->for($u)->confirmed()->create();

    $this->actingAs($admin);

    Livewire::test(DeletionRequests::class)
        ->assertSee('Alice');
});

it('lets an admin cancel a pending request', function () {
    Mail::fake();

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $u = User::factory()->create();
    $u->assignRole('member');
    $req = AccountDeletionRequest::factory()->for($u)->confirmed()->create();

    $this->actingAs($admin);

    Livewire::test(DeletionRequests::class)
        ->set('cancelReason.'.$req->id, 'abuse report to review first')
        ->call('cancel', $req->id)
        ->assertHasNoErrors();

    $req->refresh();
    expect($req->status)->toBe('cancelled')
        ->and($req->cancelled_by)->toBe($admin->id)
        ->and($req->cancel_reason)->toBe('abuse report to review first');
});

it('lets an admin force-purge a due request', function () {
    Mail::fake();

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $u = User::factory()->create();
    $u->assignRole('member');
    $req = AccountDeletionRequest::factory()->for($u)->confirmed()->create([
        'scheduled_purge_at' => now()->addDays(10),
    ]);

    $this->actingAs($admin);

    Livewire::test(DeletionRequests::class)
        ->call('forcePurge', $req->id)
        ->assertHasNoErrors();

    $req->refresh();
    expect($req->status)->toBe('purged')
        ->and(User::find($u->id))->toBeNull();
});

it('notifies the admin team when a confirmed deletion concerns a user with published posts', function () {
    Mail::fake();

    $user = User::factory()->create();
    $user->assignRole('member');
    BlogPost::factory()->for($user)->create(['status' => 'published']);

    $req = AccountDeletionRequest::startFor($user);

    $this->get(route('account.deletion.confirm', ['token' => $req->confirmation_token]))
        ->assertOk();

    Mail::assertQueued(AdminDeletionPendingWithContent::class, fn ($m) => $m->hasTo('contact@bassila-emergence.org'));
});
```

- [ ] **Step 8.2 — Run failing tests**

Run: `./vendor/bin/pest tests/Feature/Admin/DeletionRequestsAdminTest.php`
Expected: failures.

- [ ] **Step 8.3 — Build the admin mailable**

Create `app/Mail/AdminDeletionPendingWithContent.php`:

```php
<?php

namespace App\Mail;

use App\Models\AccountDeletionRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminDeletionPendingWithContent extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public AccountDeletionRequest $request, public int $publishedPostCount) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: '[Admin] Demande de suppression d\'un membre avec contenus publiés');
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.account.admin-pending-content',
            with: [
                'user'       => $this->request->user,
                'postCount'  => $this->publishedPostCount,
                'purgeAt'    => $this->request->scheduled_purge_at,
                'adminUrl'   => route('admin.deletions'),
            ],
        );
    }
}
```

Create `resources/views/mail/account/admin-pending-content.blade.php`:

```blade
<p>Équipe Bassila Emergence,</p>

<p>Le membre <strong>{{ $user->first_name }} {{ $user->last_name }}</strong> ({{ $user->email }}) a confirmé sa suppression. Il a publié <strong>{{ $postCount }}</strong> article(s) publié(s) sur la plateforme.</p>

<p>Purge planifiée&nbsp;: <strong>{{ $purgeAt->isoFormat('D MMMM YYYY') }}</strong>.</p>

<p>Les articles seront anonymisés automatiquement. Vérifiez en amont si une action éditoriale spécifique (dépublication, retitrage) est nécessaire&nbsp;:</p>
<p><a href="{{ $adminUrl }}">Tableau des suppressions</a></p>
```

- [ ] **Step 8.4 — Dispatch that email from `ConfirmDeletion`**

In `app/Livewire/Account/ConfirmDeletion.php`, after `Mail::to($req->user->email)->queue(new AccountDeletionConfirmed($req))`, add:

```php
$published = $req->user->blogPosts()->where('status', 'published')->count();
if ($published > 0) {
    Mail::to('contact@bassila-emergence.org')
        ->queue(new \App\Mail\AdminDeletionPendingWithContent($req, $published));
}
```

- [ ] **Step 8.5 — Build the admin Livewire page**

Create `app/Livewire/Admin/DeletionRequests.php`:

```php
<?php

namespace App\Livewire\Admin;

use App\Models\AccountDeletionRequest;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class DeletionRequests extends Component
{
    use WithPagination;

    public string $statusFilter = 'all';
    public array $cancelReason = [];

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function cancel(int $id): void
    {
        $req = AccountDeletionRequest::findOrFail($id);
        $reason = $this->cancelReason[$id] ?? null;
        $req->cancel(auth()->user(), $reason);
        $req->user?->update(['is_active' => true]);

        session()->flash('status', 'Demande annulée.');
    }

    public function forcePurge(int $id): void
    {
        $req = AccountDeletionRequest::findOrFail($id);

        if ($req->status === 'requested') {
            $req->confirm();
        }

        $req->purge();

        session()->flash('status', 'Compte purgé.');
    }

    #[Layout('layouts.admin')]
    public function render()
    {
        $query = AccountDeletionRequest::with('user', 'canceller')->latest('requested_at');

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        return view('livewire.admin.deletion-requests', [
            'requests' => $query->paginate(20),
        ]);
    }
}
```

Create `resources/views/livewire/admin/deletion-requests.blade.php`:

```blade
<div class="p-6">
    <h1 class="text-2xl font-bold mb-6">Demandes de suppression</h1>

    @if (session('status'))
        <p class="text-sm bg-green-50 border border-green-200 text-green-800 p-3 mb-4">{{ session('status') }}</p>
    @endif

    <div class="mb-4">
        <select wire:model.live="statusFilter" class="border border-gray-300 px-3 py-2 text-sm">
            <option value="all">Tous</option>
            <option value="requested">En attente de confirmation</option>
            <option value="confirmed">Confirmées (période de grâce)</option>
            <option value="cancelled">Annulées</option>
            <option value="purged">Purgées</option>
        </select>
    </div>

    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-xs uppercase text-gray-500">
            <tr>
                <th class="text-left p-3">Utilisateur</th>
                <th class="text-left p-3">Statut</th>
                <th class="text-left p-3">Demandée le</th>
                <th class="text-left p-3">Purge prévue</th>
                <th class="text-left p-3">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($requests as $r)
                <tr class="border-b">
                    <td class="p-3">
                        {{ $r->user?->first_name }} {{ $r->user?->last_name }}
                        <br><span class="text-xs text-gray-500">{{ $r->user?->email ?? '[compte purgé]' }}</span>
                    </td>
                    <td class="p-3">{{ $r->status }}</td>
                    <td class="p-3">{{ $r->requested_at?->isoFormat('D MMM YYYY') }}</td>
                    <td class="p-3">{{ $r->scheduled_purge_at?->isoFormat('D MMM YYYY') ?? '—' }}</td>
                    <td class="p-3">
                        @if (in_array($r->status, ['requested', 'confirmed']))
                            <input type="text" wire:model="cancelReason.{{ $r->id }}"
                                   placeholder="Raison (optionnelle)"
                                   class="border border-gray-300 px-2 py-1 text-xs mb-1 w-full">
                            <button wire:click="cancel({{ $r->id }})"
                                    class="text-xs bg-gray-200 px-2 py-1 hover:bg-gray-300">Annuler</button>
                            <button wire:click="forcePurge({{ $r->id }})"
                                    wire:confirm="Purger immédiatement ce compte ? Cette action est irréversible."
                                    class="text-xs bg-red-600 text-white px-2 py-1 hover:bg-red-700">Purger maintenant</button>
                        @else
                            —
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="p-6 text-center text-gray-500">Aucune demande.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-4">{{ $requests->links() }}</div>
</div>
```

- [ ] **Step 8.6 — Register the route**

In `routes/web.php`, inside the admin group:

```php
Route::get('/suppressions', \App\Livewire\Admin\DeletionRequests::class)->name('deletions');
```

(Named as `admin.deletions` thanks to the `admin.` name prefix on the group.)

- [ ] **Step 8.7 — Add a nav entry in the admin sidebar**

Run: `grep -rn "admin.users" resources/views/layouts resources/views/admin 2>/dev/null`

In the matching sidebar file, add a new `<li>` alongside the other admin links:

```blade
<li>
    <a href="{{ route('admin.deletions') }}"
       class="{{ request()->routeIs('admin.deletions') ? 'text-white' : 'text-gray-400' }} hover:text-white">
        Suppressions
    </a>
</li>
```

- [ ] **Step 8.8 — Run tests**

Run: `./vendor/bin/pest tests/Feature/Admin/DeletionRequestsAdminTest.php`
Expected: 5 passed.

- [ ] **Step 8.9 — Commit**

```bash
git add app/Livewire/Admin/DeletionRequests.php \
        resources/views/livewire/admin/deletion-requests.blade.php \
        app/Mail/AdminDeletionPendingWithContent.php \
        resources/views/mail/account/admin-pending-content.blade.php \
        app/Livewire/Account/ConfirmDeletion.php \
        routes/web.php \
        resources/views/layouts/ \
        tests/Feature/Admin/DeletionRequestsAdminTest.php
git commit -m "feat(account-deletion): admin page for reviewing and forcing deletions"
```

---

## Task 9: Full-suite verification & manual smoke test

- [ ] **Step 9.1 — Run the entire suite**

Run: `./vendor/bin/pest`
Expected: all green.

- [ ] **Step 9.2 — Run static analysis**

Run: `./vendor/bin/phpstan analyse --memory-limit=1G` (if configured)
Expected: no new errors.

- [ ] **Step 9.3 — Manual smoke test (checklist)**

- [ ] Visit `/cgu`, `/politique-de-confidentialite`, `/mentions-legales` → 200, footer links work.
- [ ] Register a new user without the CGU checkbox → validation error; with checkbox → account created.
- [ ] As a logged-in member, open `/profil/modifier`, click «Supprimer mon compte», enter password + tick, submit → success screen.
- [ ] Check mail log: confirmation email received with signed URL.
- [ ] Click the confirmation URL → «Suppression confirmée» page; member is logged out; `is_active` is 0 in DB.
- [ ] Log back in → redirected to cancel page. Click cancel → account reactivated.
- [ ] Start a new deletion, click confirm; in DB set `scheduled_purge_at` to `now()` ; run `php artisan accounts:purge-expired` → account gone, posts/comments anonymised.
- [ ] As an admin, visit `/admin/suppressions` → all requests visible ; `force purge` and `cancel` both work.

- [ ] **Step 9.4 — Commit any follow-up fix**

If the smoke test surfaces issues, fix them, add a targeted test, rerun the suite, and commit with an explicit `fix(account-deletion): ...` message.

---

## Self-Review Checklist

- Every spec section has a task: legal pages (Task 2), registration checkbox (Task 3), deletion request (Task 4), confirm flow (Task 5), grace-period cancel + login redirect (Task 6), anonymisation + purge command (Task 7), admin (Task 8). Covered.
- No TBD / TODO / placeholder language in steps.
- Type consistency: `startFor`, `confirm`, `cancel`, `markPurged`, `purge`, `isTokenExpired`, `dueForPurge` used consistently across Task 1, 4, 5, 6, 7, 8.
- `displayAuthorName` accessor name reused identically on both BlogPost and BlogComment.
- `account.deletion.confirm` / `account.deletion.cancel` / `admin.deletions` route names reused consistently.
- `afterCommit()` ordering for `AccountDeletionCompleted` is explicit.
- `RedirectIfDeletionPending` explicitly whitelists `account.deletion.cancel`, `account.deletion.confirm`, and `logout` to avoid redirect loops.
