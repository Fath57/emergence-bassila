# Newsletter Management — Design Spec

**Project:** Bassila Émergence — Admin autonomy roadmap, sub-project ④
**Date:** 2026-04-11
**Status:** Draft — awaiting user review

---

## 1. Context

Bassila Émergence currently has a `NewsletterSubscription` model (only
`email + created_at`) and a basic `SubscribeForm` Livewire component that
stores submitted emails. Nothing more: **no double opt-in, no unsubscribe
token, no admin UI, no campaigns, no actual email delivery**. The table is
empty — it is pure infrastructure stub.

This sub-project builds a full "standard MVP" newsletter system on top of
that foundation:

- Replaces `newsletter_subscriptions` with a richer `newsletter_subscribers`
  table supporting double opt-in, unsubscribe tokens, source tracking, and
  soft-unsubscribe state
- Introduces `newsletter_campaigns` and `newsletter_campaign_sends` for
  composing campaigns and tracking per-subscriber delivery + open state
- Provides admin UI to manage subscribers, compose campaigns via the TipTap
  editor from sub-project ③, preview, test-send, and launch real sends via
  queued jobs
- Provides public pages for subscription confirmation, one-click unsubscribe
  (RFC 8058 compliant), and pixel-based open tracking
- Enforces rate limiting at `300 mails/min` to protect SMTP reputation
- Guarantees idempotent sends via a unique constraint on the pivot table

It is the **fourth and last** sub-project of the admin autonomy roadmap. It
depends on ② RBAC for its permission contracts (`newsletter.subscribers.view`,
`newsletter.campaigns.compose`, `newsletter.campaigns.send`) and on
③ Blog editor for the TipTap editor component and `BlogContentSanitizer`
service — both reused verbatim.

## 2. Goals

- Replace the empty `newsletter_subscriptions` table with `newsletter_subscribers` supporting full lifecycle (pending → confirmed → unsubscribed)
- Implement **double opt-in** via a 64-char `confirmation_token` with 7-day expiry
- Implement **one-click unsubscribe** via `unsubscribe_token` (no expiry), accepting both GET and POST per RFC 8058
- Build a `NewsletterCampaign` model with composer UI reusing the TipTap editor from ③
- Ship a queued send pipeline using `Bus::batch()` with 50-subscriber chunks, rate-limited globally at 300/min
- Track per-send state in `newsletter_campaign_sends` pivot with unique `(campaign_id, subscriber_id)` constraint for idempotence
- Ship pixel-based open tracking with per-send opaque tokens (not enumerable)
- Provide admin pages: dashboard, campaigns list, campaign composer, subscribers list, CSV import
- Provide public pages: confirmation page, unsubscribe page, pixel endpoint
- Include `List-Unsubscribe` and `List-Unsubscribe-Post` mail headers on every campaign for Gmail/Outlook native unsubscribe
- Privacy-preserving re-subscribe: submitting an already-confirmed email does **not** trigger a second confirmation mail (prevents email enumeration)
- Full French UI, consistent with the existing `layouts.admin` and `layouts.guest` aesthetics

## 3. Non-Goals

Explicitly out of scope:

- **Click tracking** on links inside mails (URL rewriting to count clicks)
- **Segmentation** (sending to a filtered subset like "editors only" or "verified profiles")
- **A/B testing** on subject lines
- **Template library** (reusable campaign templates saved in DB)
- **Automations / drip campaigns** (sending X days after signup, etc.)
- **Bounce handling** (auto-marking bouncing addresses as unsubscribed via mail provider webhook)
- **External ESP** (Mailchimp, Brevo, Postmark) — uses Laravel's default `MAIL_MAILER`
- **Scheduled sending** — no `scheduled_for` column, no cron-based delivery
- **Multi-language** — single French template
- **Preference center** (recipient choosing frequency or topics)
- **HTML + plain-text alternative bodies** — HTML only at MVP; plain-text fallback added if deliverability requires it

## 4. Architecture overview

```
┌─────────────────────────────────────────────────────────────────────┐
│                         Public surface                             │
│                                                                     │
│   /                               → footer SubscribeForm            │
│   /newsletter/confirmer/{token}   → ConfirmSubscription             │
│   /newsletter/desabonner/{token}  → Unsubscribe (GET + POST)        │
│   /newsletter/open/{token}        → pixel tracking (1x1 PNG)        │
└──────────────────┬──────────────────────────────────────────────────┘
                   │
┌──────────────────┴──────────────────────────────────────────────────┐
│                         Admin surface                              │
│                                                                     │
│   /admin/newsletter                           → Dashboard           │
│   /admin/newsletter/campagnes                 → Campaigns list      │
│   /admin/newsletter/campagnes/creer           → CreateCampaign      │
│   /admin/newsletter/campagnes/{id}            → EditCampaign        │
│   /admin/newsletter/campagnes/{id}/preview    → iframe render       │
│   /admin/newsletter/abonnes                   → Subscribers list    │
│   /admin/newsletter/abonnes/importer          → ImportCsv           │
└──────────────────┬──────────────────────────────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────────────────────────────┐
│                         Send pipeline                              │
│                                                                     │
│  Admin clicks [Envoyer]                                             │
│         │                                                           │
│         ▼                                                           │
│  LaunchCampaignSend action:                                         │
│     - snapshot recipients (confirmed + not unsubscribed)            │
│     - set status=sending, recipients_count, sent_at                 │
│         │                                                           │
│         ▼                                                           │
│  Bus::batch( chunks of 50 → SendCampaignBatch )                    │
│         │                                                           │
│         ├─► SendCampaignBatch (chunk 1) ────┐                      │
│         ├─► SendCampaignBatch (chunk 2) ────┤                      │
│         ├─► SendCampaignBatch (chunk N) ────┤                      │
│         │                                    │                      │
│         │   Each batch:                     │                      │
│         │     - RateLimited (300/min global) │                      │
│         │     - Idempotence check on pivot   │                      │
│         │     - Mail::to() + pivot row       │                      │
│         │     - increment sent_count          │                      │
│         │                                    │                      │
│         ▼                                    ▼                      │
│  FinalizeCampaign (batched .then callback):                        │
│     status = 'sent', finished_at = now()                           │
└─────────────────────────────────────────────────────────────────────┘
```

### Reused from previous sub-projects

- **TipTap `<x-tiptap-editor>`** component from ③ — zero new editor code.
- **`BlogContentSanitizer`** service from ③ — sanitizes campaign HTML before
  persistence using the same `blog` HTMLPurifier profile.
- **HTMLPurifier config `blog`** from ③ — same allowlist (tags, iframes,
  attributes). A campaign HTML body is strictly a subset of an article body.
- **Admin layout `layouts.admin`** from the existing admin panel — sidebar
  nav with Newsletter entry added.
- **Mail design language** from the 4 existing transactional mails
  (`mail/contact/*`, `mail/profile/*`) — white header + logo + blue accent
  border + dark navy footer.

## 5. Data model

### 5.1 Migration `replace_newsletter_subscriptions_with_full_schema`

Single migration creating all three new tables in one transaction, dropping
the empty legacy table.

```php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('newsletter_subscriptions');

        Schema::create('newsletter_subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('first_name', 100)->nullable();
            $table->string('confirmation_token', 64)->nullable();
            $table->string('unsubscribe_token', 64)->unique();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('unsubscribed_at')->nullable();
            $table->string('source', 50)->default('public_form');
            $table->timestamp('last_sent_at')->nullable();
            $table->timestamps();

            $table->index('confirmed_at', 'idx_subs_confirmed');
            $table->index('unsubscribed_at', 'idx_subs_unsubscribed');
        });

        Schema::create('newsletter_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('subject', 255);
            $table->longText('content');
            $table->string('preview_text', 150)->nullable();
            $table->enum('status', ['draft', 'sending', 'sent', 'failed'])->default('draft');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('batch_id')->nullable();
            $table->unsignedInteger('recipients_count')->default(0);
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('opens_count')->default(0);
            $table->unsignedInteger('unsubscribes_count')->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index('status', 'idx_campaigns_status');
        });

        Schema::create('newsletter_campaign_sends', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')
                  ->constrained('newsletter_campaigns')
                  ->cascadeOnDelete();
            $table->foreignId('subscriber_id')
                  ->constrained('newsletter_subscribers')
                  ->cascadeOnDelete();
            $table->string('open_token', 32)->unique();
            $table->timestamp('sent_at');
            $table->timestamp('opened_at')->nullable();
            $table->text('error')->nullable();

            $table->unique(['campaign_id', 'subscriber_id'], 'uq_campaign_subscriber');
            $table->index('opened_at', 'idx_sends_opened');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletter_campaign_sends');
        Schema::dropIfExists('newsletter_campaigns');
        Schema::dropIfExists('newsletter_subscribers');

        Schema::create('newsletter_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->timestamp('created_at')->useCurrent();
        });
    }
};
```

### 5.2 Subscriber lifecycle states

```
┌─────────────┐  confirm email  ┌──────────────┐  click unsubscribe  ┌──────────────┐
│  pending    │ ───────────────► │  confirmed   │ ──────────────────► │ unsubscribed │
│             │                  │              │                     │              │
│ confirmed_at│                  │ confirmed_at │                     │unsubscribed_at│
│   = null    │                  │   = set      │                     │   = set       │
└─────────────┘                  └──────────────┘                     └──────────────┘
       │
       │  token expired > 7d
       ▼
   (soft-eligible for re-subscription via public form)
```

| State | Eligible to receive campaigns? | Re-subscribable? |
|---|---|---|
| `pending` (token < 7 days old) | ❌ | N/A (already in flight) |
| `pending` (token expired) | ❌ | ✅ Public form recycles the row |
| `confirmed` | ✅ | N/A |
| `unsubscribed` | ❌ | ✅ Public form recycles the row with new confirmation flow |

### 5.3 Query for send recipients

```php
NewsletterSubscriber::query()
    ->whereNotNull('confirmed_at')
    ->whereNull('unsubscribed_at')
    ->orderBy('id')
    ->pluck('id');
```

A single indexed query, no joins. The `idx_subs_confirmed` and
`idx_subs_unsubscribed` indexes guarantee sub-linear cost on reasonable
subscriber counts.

## 6. Models

### 6.1 `App\Models\NewsletterSubscriber`

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class NewsletterSubscriber extends Model
{
    use LogsActivity;

    protected $fillable = [
        'email', 'first_name',
        'confirmation_token', 'unsubscribe_token',
        'confirmed_at', 'unsubscribed_at',
        'source', 'last_sent_at',
    ];

    protected $casts = [
        'confirmed_at'    => 'datetime',
        'unsubscribed_at' => 'datetime',
        'last_sent_at'    => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (NewsletterSubscriber $sub) {
            if (empty($sub->unsubscribe_token)) {
                $sub->unsubscribe_token = Str::random(64);
            }
        });
    }

    public function isConfirmed(): bool
    {
        return $this->confirmed_at !== null && $this->unsubscribed_at === null;
    }

    public function isPending(): bool
    {
        return $this->confirmed_at === null
            && $this->unsubscribed_at === null
            && $this->created_at->gt(now()->subDays(7));
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->whereNotNull('confirmed_at')->whereNull('unsubscribed_at');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['email', 'confirmed_at', 'unsubscribed_at'])
            ->logOnlyDirty()
            ->useLogName('newsletter');
    }
}
```

### 6.2 `App\Models\NewsletterCampaign`

```php
class NewsletterCampaign extends Model
{
    use LogsActivity;

    protected $fillable = [
        'subject', 'content', 'preview_text', 'status',
        'created_by', 'batch_id',
        'recipients_count', 'sent_count', 'opens_count', 'unsubscribes_count',
        'sent_at', 'finished_at',
    ];

    protected $casts = [
        'sent_at'     => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sends(): HasMany
    {
        return $this->hasMany(NewsletterCampaignSend::class, 'campaign_id');
    }

    public function isEditable(): bool
    {
        return $this->status === 'draft';
    }

    public function isCancellable(): bool
    {
        return in_array($this->status, ['draft', 'failed'], true);
    }

    public function openRate(): float
    {
        return $this->sent_count > 0
            ? round(($this->opens_count / $this->sent_count) * 100, 1)
            : 0.0;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['subject', 'status', 'sent_at'])
            ->logOnlyDirty()
            ->useLogName('newsletter');
    }
}
```

### 6.3 `App\Models\NewsletterCampaignSend`

```php
class NewsletterCampaignSend extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'campaign_id', 'subscriber_id', 'open_token',
        'sent_at', 'opened_at', 'error',
    ];

    protected $casts = [
        'sent_at'   => 'datetime',
        'opened_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (NewsletterCampaignSend $send) {
            if (empty($send->open_token)) {
                $send->open_token = Str::random(32);
            }
        });
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(NewsletterCampaign::class, 'campaign_id');
    }

    public function subscriber(): BelongsTo
    {
        return $this->belongsTo(NewsletterSubscriber::class, 'subscriber_id');
    }
}
```

## 7. Send pipeline

### 7.1 `App\Actions\LaunchCampaignSend`

```php
namespace App\Actions;

use App\Jobs\SendCampaignBatch;
use App\Models\NewsletterCampaign;
use App\Models\NewsletterSubscriber;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;

class LaunchCampaignSend
{
    public function __invoke(NewsletterCampaign $campaign): void
    {
        if ($campaign->status !== 'draft') {
            throw new \DomainException('Only draft campaigns can be sent.');
        }

        $subscriberIds = NewsletterSubscriber::query()
            ->active()
            ->pluck('id')
            ->all();

        if (empty($subscriberIds)) {
            $campaign->update(['status' => 'failed']);
            throw new \DomainException('No active subscribers to send to.');
        }

        $campaign->update([
            'status'           => 'sending',
            'recipients_count' => count($subscriberIds),
            'sent_at'          => now(),
        ]);

        $batches = collect($subscriberIds)->chunk(50)->map(
            fn ($chunk) => new SendCampaignBatch($campaign->id, $chunk->values()->all())
        )->all();

        $busBatch = Bus::batch($batches)
            ->name("newsletter-campaign-{$campaign->id}")
            ->then(function (Batch $batch) use ($campaign) {
                $campaign->update([
                    'status'      => 'sent',
                    'finished_at' => now(),
                ]);
            })
            ->catch(function (Batch $batch, \Throwable $e) use ($campaign) {
                $campaign->update(['status' => 'failed']);
                logger()->error('Newsletter campaign batch failed', [
                    'campaign_id' => $campaign->id,
                    'message'     => $e->getMessage(),
                ]);
            })
            ->dispatch();

        $campaign->update(['batch_id' => $busBatch->id]);
    }
}
```

### 7.2 `App\Jobs\SendCampaignBatch`

```php
namespace App\Jobs;

use App\Mail\NewsletterCampaignMail;
use App\Models\NewsletterCampaign;
use App\Models\NewsletterCampaignSend;
use App\Models\NewsletterSubscriber;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendCampaignBatch implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        public int $campaignId,
        public array $subscriberIds,
    ) {}

    public function middleware(): array
    {
        return [new RateLimited('newsletter-sending')];
    }

    public function handle(): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        $campaign = NewsletterCampaign::find($this->campaignId);
        if (! $campaign) {
            return;
        }

        foreach ($this->subscriberIds as $subId) {
            $subscriber = NewsletterSubscriber::find($subId);
            if (! $subscriber || ! $subscriber->isConfirmed()) {
                continue;
            }

            // Idempotence — skip if already sent to this subscriber for this campaign
            if (NewsletterCampaignSend::where([
                'campaign_id'   => $campaign->id,
                'subscriber_id' => $subscriber->id,
            ])->exists()) {
                continue;
            }

            $send = NewsletterCampaignSend::create([
                'campaign_id'   => $campaign->id,
                'subscriber_id' => $subscriber->id,
                'sent_at'       => now(),
            ]);

            try {
                Mail::to($subscriber->email)
                    ->send(new NewsletterCampaignMail($campaign, $subscriber, $send));

                $campaign->increment('sent_count');
                $subscriber->update(['last_sent_at' => now()]);
            } catch (\Throwable $e) {
                $send->update(['error' => $e->getMessage()]);
                logger()->warning('Newsletter send failed', [
                    'subscriber_id' => $subscriber->id,
                    'error'         => $e->getMessage(),
                ]);
            }
        }
    }
}
```

**Key properties**:
- The unique constraint `uq_campaign_subscriber` on the pivot table
  guarantees that a retried job cannot create duplicate sends.
- The explicit `where(...)->exists()` check is a belt-and-suspenders
  safeguard on top of the DB constraint — it avoids the
  `UniqueConstraintViolationException` on the happy path and simply skips
  the row.
- Individual send failures are captured in `error` column, not bubbled up,
  so one bad address doesn't kill the batch.

### 7.3 Rate limiter — `AppServiceProvider::boot()`

```php
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

RateLimiter::for('newsletter-sending', function () {
    return Limit::perMinute(300);
});
```

Global limit, shared across all workers. If two workers each try to send, the
rate limiter coordinates via the default cache store.

## 8. Pixel tracking endpoint

```php
// routes/web.php
Route::get('/newsletter/open/{token}',
    [NewsletterTrackingController::class, 'open']
)->name('newsletter.open');
```

```php
namespace App\Http\Controllers;

use App\Models\NewsletterCampaignSend;
use Illuminate\Http\Response;

class NewsletterTrackingController extends Controller
{
    public function open(string $token): Response
    {
        $send = NewsletterCampaignSend::where('open_token', $token)->first();

        if ($send && $send->opened_at === null) {
            $send->update(['opened_at' => now()]);
            $send->campaign()->increment('opens_count');
        }

        // Always return the pixel — don't leak whether the token was valid
        return response(
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg=='),
            200,
            [
                'Content-Type'  => 'image/png',
                'Cache-Control' => 'no-store, no-cache, must-revalidate',
                'Pragma'        => 'no-cache',
            ]
        );
    }
}
```

The pixel is a 1x1 transparent PNG embedded as a base64 constant — zero
filesystem dependency, zero cache coherency issues.

## 9. Public pages

### 9.1 `SubscribeForm` (refactored)

```php
namespace App\Livewire\Newsletter;

use App\Mail\NewsletterConfirmationMail;
use App\Models\NewsletterSubscriber;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Livewire\Component;

class SubscribeForm extends Component
{
    public string $email = '';
    public string $firstName = '';
    public bool $subscribed = false;

    protected function rules(): array
    {
        return [
            'email'     => ['required', 'email', 'max:255'],
            'firstName' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function subscribe(): void
    {
        $this->validate();

        $subscriber = NewsletterSubscriber::firstOrNew(['email' => $this->email]);

        // Privacy: never reveal an already-confirmed email via different responses
        if ($subscriber->exists && $subscriber->isConfirmed()) {
            $this->subscribed = true;
            $this->resetFields();
            return;
        }

        $subscriber->fill([
            'first_name'         => $this->firstName ?: null,
            'confirmation_token' => Str::random(64),
            'confirmed_at'       => null,
            'unsubscribed_at'    => null,
            'source'             => request()->routeIs('home') ? 'footer_form' : 'public_form',
        ])->save();

        Mail::to($subscriber->email)
            ->queue(new NewsletterConfirmationMail($subscriber));

        $this->subscribed = true;
        $this->resetFields();
    }

    private function resetFields(): void
    {
        $this->email = '';
        $this->firstName = '';
    }

    public function render()
    {
        return view('livewire.newsletter.subscribe-form');
    }
}
```

Success message: `"Vérifiez votre email pour confirmer votre abonnement."`

### 9.2 `ConfirmSubscription` page

```php
namespace App\Livewire\Newsletter;

use App\Models\NewsletterSubscriber;
use Livewire\Attributes\Layout;
use Livewire\Component;

class ConfirmSubscription extends Component
{
    public bool $success = false;
    public string $message = '';

    public function mount(string $token): void
    {
        $subscriber = NewsletterSubscriber::where('confirmation_token', $token)->first();

        if (! $subscriber) {
            $this->message = "Ce lien de confirmation est invalide ou a déjà été utilisé.";
            return;
        }

        if ($subscriber->created_at->lt(now()->subDays(7))) {
            $this->message = "Ce lien de confirmation a expiré. Re-inscrivez-vous pour recevoir un nouveau lien.";
            return;
        }

        $subscriber->update([
            'confirmed_at'       => now(),
            'confirmation_token' => null,
        ]);

        $this->success = true;
        $this->message = "Merci ! Votre abonnement à la newsletter est confirmé.";
    }

    #[Layout('layouts.guest')]
    public function render()
    {
        return view('livewire.newsletter.confirm-subscription');
    }
}
```

### 9.3 `Unsubscribe` page — one-click + RFC 8058

```php
namespace App\Livewire\Newsletter;

use App\Models\NewsletterCampaignSend;
use App\Models\NewsletterSubscriber;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Unsubscribe extends Component
{
    public bool $wasSubscribed = false;
    public ?string $email = null;

    public function mount(string $token): void
    {
        $subscriber = NewsletterSubscriber::where('unsubscribe_token', $token)->first();

        if (! $subscriber) {
            $this->wasSubscribed = false;
            return;
        }

        if ($subscriber->unsubscribed_at === null) {
            $subscriber->update(['unsubscribed_at' => now()]);

            // Attribute the unsubscribe to the most recent campaign sent to this subscriber
            $lastSend = NewsletterCampaignSend::where('subscriber_id', $subscriber->id)
                ->orderByDesc('sent_at')
                ->first();
            $lastSend?->campaign?->increment('unsubscribes_count');
        }

        $this->wasSubscribed = true;
        $this->email = $subscriber->email;
    }

    #[Layout('layouts.guest')]
    public function render()
    {
        return view('livewire.newsletter.unsubscribe');
    }
}
```

**Public routes** (all added to `routes/web.php`, outside any auth group):

```php
// routes/web.php
Route::get('/newsletter/confirmer/{token}',
    \App\Livewire\Newsletter\ConfirmSubscription::class
)->name('newsletter.confirm');

Route::match(['get', 'post'], '/newsletter/desabonner/{token}',
    \App\Livewire\Newsletter\Unsubscribe::class
)->name('newsletter.unsubscribe');

Route::get('/newsletter/open/{token}',
    [\App\Http\Controllers\NewsletterTrackingController::class, 'open']
)->name('newsletter.open');
```

Register the URL in `VerifyCsrfToken::except` so the POST from external mail
clients (Gmail, Outlook) isn't blocked by CSRF middleware. Add this to
`bootstrap/app.php` in the `withMiddleware` closure:

```php
$middleware->validateCsrfTokens(except: [
    'newsletter/desabonner/*',
]);
```

The page displays "Vous êtes désabonné de la newsletter." with a
"[Me réabonner]" button redirecting to the home page.

## 10. Mail templates

### 10.1 `resources/views/mail/newsletter/confirmation.blade.php`

Same structure as existing `mail/contact/*` templates (white header with
logo + blue accent border + dark navy footer). Content:

> Bonjour,
>
> Vous venez de demander à vous abonner à la newsletter de **Bassila Émergence**.
>
> Pour finaliser votre inscription, merci de cliquer sur le bouton ci-dessous.
>
> [Confirmer mon abonnement] → `route('newsletter.confirm', ['token' => $subscriber->confirmation_token])`
>
> Ce lien est valable 7 jours. Si vous n'êtes pas à l'origine de cette demande, ignorez simplement cet email.

### 10.2 `resources/views/mail/newsletter.blade.php` (campaign template)

```blade
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $campaign->subject }}</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; color: #111827; background: #f5f5f5; margin: 0; padding: 24px 12px;">

@if ($campaign->preview_text)
    <div style="display: none; max-height: 0; overflow: hidden; mso-hide: all;">
        {{ $campaign->preview_text }}
    </div>
@endif

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
    <tr>
        <td align="center">
            <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0"
                   style="max-width: 600px; background: white; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden;">
                <tr>
                    <td style="background: white; padding: 28px 32px; border-bottom: 3px solid #0066CC;">
                        <img src="{{ asset('images/logo-trans.png') }}"
                             alt="Bassila Émergence"
                             width="180" style="display: block; height: auto; border: 0;">
                    </td>
                </tr>
                <tr>
                    <td style="padding: 32px;">
                        {!! $campaign->content !!}
                    </td>
                </tr>
                <tr>
                    <td style="background: #0A1628; padding: 20px 32px; text-align: center;">
                        <p style="margin: 0 0 8px; font-size: 11px; color: rgba(255,255,255,0.4);">
                            © {{ date('Y') }} Bassila Émergence
                        </p>
                        <p style="margin: 0; font-size: 11px;">
                            <a href="{{ route('newsletter.unsubscribe', ['token' => $subscriber->unsubscribe_token]) }}"
                               style="color: rgba(255,255,255,0.7); text-decoration: underline;">
                                Se désabonner
                            </a>
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

@if ($send)
    <img src="{{ route('newsletter.open', ['token' => $send->open_token]) }}"
         width="1" height="1" alt=""
         style="display: block; width: 1px; height: 1px;">
@endif

</body>
</html>
```

### 10.3 `App\Mail\NewsletterCampaignMail` — with RFC 8058 headers

```php
namespace App\Mail;

use App\Models\NewsletterCampaign;
use App\Models\NewsletterCampaignSend;
use App\Models\NewsletterSubscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

class NewsletterCampaignMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public NewsletterCampaign $campaign,
        public NewsletterSubscriber $subscriber,
        public ?NewsletterCampaignSend $send = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->campaign->subject);
    }

    public function headers(): Headers
    {
        $unsubUrl = route('newsletter.unsubscribe', [
            'token' => $this->subscriber->unsubscribe_token,
        ]);

        return new Headers(text: [
            'List-Unsubscribe'      => "<{$unsubUrl}>",
            'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
        ]);
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.newsletter',
            with: [
                'campaign'   => $this->campaign,
                'subscriber' => $this->subscriber,
                'send'       => $this->send,
            ],
        );
    }
}
```

The `List-Unsubscribe` header is **required** by Gmail's bulk sender
guidelines (effective 2024). Without it, campaigns over ~5k daily recipients
get flagged as spam automatically.

### 10.4 `App\Mail\NewsletterConfirmationMail`

Same structure as `NewsletterCampaignMail` but targeted at `mail/newsletter/confirmation.blade.php`, no `send` parameter, no tracking pixel, no
`List-Unsubscribe` headers (it's transactional, not marketing).

## 11. Admin pages

### 11.1 Permissions required

All admin newsletter pages check their specific permission at component
entry via `$this->authorize(...)`:

| Route | Permission |
|---|---|
| `/admin/newsletter` (dashboard) | `newsletter.subscribers.view` |
| `/admin/newsletter/campagnes` | `newsletter.campaigns.compose` |
| `/admin/newsletter/campagnes/creer` | `newsletter.campaigns.compose` |
| `/admin/newsletter/campagnes/{id}` | `newsletter.campaigns.compose` |
| `/admin/newsletter/campagnes/{id}/preview` | `newsletter.campaigns.compose` |
| `/admin/newsletter/abonnes` | `newsletter.subscribers.view` |
| `/admin/newsletter/abonnes/importer` | `newsletter.subscribers.view` |
| `[Envoyer]` button (action) | `newsletter.campaigns.send` |

These permissions are all already seeded by the ② RBAC spec to the `admin`
role; no editor or member has them.

### 11.2 Dashboard `/admin/newsletter`

KPI cards at the top (same styling as existing `/admin/` dashboard):

| Card | Value | Accent |
|---|---|---|
| Abonnés actifs | `count(active())` | blue |
| En attente | `count(pending)` | amber |
| Désabonnés | `count(unsubscribed)` | gray |
| Campagnes envoyées | `count(status='sent')` | green |
| Dernière campagne | title + date + open rate % | neutral |

Two big CTA buttons: `[+ Nouvelle campagne]` and `[+ Importer des abonnés]`.

### 11.3 Campaigns list `/admin/newsletter/campagnes`

Paginated table (15 per page):

| Subject | Status | Recipients | Sent | Opens | Unsubs | Sent at | Actions |

Status badges:
- `draft` → `bg-gray-100 text-gray-700`
- `sending` → `bg-amber-50 text-amber-700 border-amber-200` with animated dot
- `sent` → `bg-green-50 text-green-700 border-green-200`
- `failed` → `bg-red-50 text-red-700 border-red-200`

Sent-count column shows `{sent_count}/{recipients_count}` with a progress bar
for `sending` status. Opens column shows both count and percentage
(`123 (45%)`). Actions per row: `[Éditer]` for drafts, `[Dupliquer]` for all,
`[Supprimer]` for drafts and failed.

Filters bar: search by subject, status dropdown.

### 11.4 Create/Edit campaign `/admin/newsletter/campagnes/{creer,edit}`

Reuses the TipTap editor from ③ verbatim:

```blade
<form wire:submit.prevent="save">
    <input type="text" wire:model.live.debounce.500ms="subject"
           placeholder="Sujet de la campagne"
           class="w-full text-2xl font-bold border-0 border-b border-gray-200 focus:border-[#0066CC] focus:ring-0 px-0 py-3"
           style="font-family: 'Lora', serif;">

    <input type="text" wire:model="previewText"
           placeholder="Preheader — texte court visible dans l'aperçu de la boîte de réception"
           maxlength="150"
           class="w-full border border-gray-200 px-3 py-2 text-sm mt-4">

    <div wire:ignore class="mt-6">
        <x-tiptap-editor
            :initial="$content"
            target-property="content" />
    </div>

    <div class="flex justify-between pt-6">
        <div class="flex gap-2">
            <button type="button" wire:click="openPreview" class="text-sm text-[#0066CC] border border-gray-200 px-4 py-2 hover:border-[#0066CC]">Aperçu</button>
            <button type="button" wire:click="$set('showTestSendModal', true)" class="text-sm text-gray-600 border border-gray-200 px-4 py-2 hover:border-gray-400">Envoyer un test</button>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="bg-white border border-gray-300 text-gray-700 px-4 py-2">Sauvegarder</button>
            <button type="button" wire:click="$set('showSendConfirmModal', true)" class="bg-[#0066CC] hover:bg-blue-800 text-white font-semibold px-6 py-2">Envoyer →</button>
        </div>
    </div>
</form>
```

**Save confirmation modal**:

```
Envoyer cette campagne ?

Sujet : [subject]
Destinataires : 847 abonnés confirmés

Cette action est irréversible une fois l'envoi lancé.

[Annuler]  [Confirmer l'envoi]
```

Clicking Confirm calls `app(LaunchCampaignSend::class)($campaign)` which
transitions the campaign to `sending` and dispatches the Bus batch.

**Test send modal**:

```
Envoyer un test à...

Email : [pré-rempli avec auth()->user()->email]

[Annuler]  [Envoyer le test]
```

Calls `$this->sendTest($email)` which builds a fake `NewsletterSubscriber`
(not persisted) with `first_name = 'Exemple'`, `email = $email`,
`unsubscribe_token = 'TEST'`, and sends synchronously (not queued) with the
`$send` parameter set to `null` in `NewsletterCampaignMail` so no pixel
tracking and no activity log entries.

### 11.5 Preview `/admin/newsletter/campagnes/{id}/preview`

Standalone Livewire component that renders the exact mail template in an
HTML response, suitable for embedding in an iframe on the composer page.
**Returns no pixel, no tracking, no headers** — pure rendered HTML.

```php
class PreviewCampaign extends Component
{
    public function mount(NewsletterCampaign $campaign): void
    {
        $this->authorize('newsletter.campaigns.compose');
        $this->campaign = $campaign;
    }

    public function render()
    {
        $fakeSubscriber = new NewsletterSubscriber([
            'email'              => 'exemple@bassilanetwork.test',
            'first_name'         => 'Exemple',
            'unsubscribe_token'  => 'PREVIEW',
        ]);

        return view('mail.newsletter', [
            'campaign'   => $this->campaign,
            'subscriber' => $fakeSubscriber,
            'send'       => null,
        ]);
    }
}
```

Invoked as a full-page Livewire component, bypassing the admin layout — the
iframe should see only the bare mail HTML.

### 11.6 Subscribers list `/admin/newsletter/abonnes`

Paginated table like `/admin/utilisateurs`:

| Email | Prénom | Statut | Source | Inscrit le | Actions |

Status column rendering:
- `Actif` (green) when `confirmed_at != null AND unsubscribed_at = null`
- `En attente` (amber) when `confirmed_at = null AND created_at > now-7d`
- `Expiré` (gray) when `confirmed_at = null AND created_at <= now-7d`
- `Désabonné` (red) when `unsubscribed_at != null`

Filters: search by email, status dropdown, source dropdown.

Per-row actions:
- `[Désabonner]` — manual, forces `unsubscribed_at = now()` (for confirmed only)
- `[Renvoyer la confirmation]` — regenerates token and re-queues mail (for pending only)
- `[Supprimer]` — hard delete for RGPD "right to erasure"

Top bar: `[+ Inviter manuellement]` (opens a modal to add a single subscriber
with `confirmed_at = now()`), `[Exporter CSV]` (streams a download of the
filtered list).

### 11.7 CSV import `/admin/newsletter/abonnes/importer`

```php
class ImportCsv extends Component
{
    public $csvFile;

    public function import(): void
    {
        $this->authorize('newsletter.subscribers.view');
        $this->validate([
            'csvFile' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        $reader = \League\Csv\Reader::createFromPath($this->csvFile->getRealPath())
            ->setHeaderOffset(0);

        $imported = 0;
        $skipped  = 0;

        foreach ($reader->getRecords() as $row) {
            $email = trim($row['email'] ?? '');
            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $skipped++;
                continue;
            }

            if (NewsletterSubscriber::where('email', $email)->exists()) {
                $skipped++;
                continue;
            }

            NewsletterSubscriber::create([
                'email'        => $email,
                'first_name'   => trim($row['first_name'] ?? '') ?: null,
                'confirmed_at' => now(),
                'source'       => 'admin_import',
            ]);
            $imported++;
        }

        session()->flash('success', "{$imported} abonnés importés, {$skipped} ignorés.");
        $this->redirect(route('admin.newsletter.subscribers'), navigate: true);
    }
}
```

Requires `league/csv` (`composer require league/csv`). Very standard lib,
mature, tiny.

**Mandatory disclaimer** displayed above the form:

> **Attention** — En important cette liste, vous certifiez avoir obtenu
> le consentement préalable de chaque personne listée pour recevoir des
> emails de Bassila Émergence. Bassila Émergence décline toute responsabilité
> en cas d'import non conforme au RGPD.

Rendered with an SVG `exclamation-triangle` icon (heroicons-o), not an emoji.

### 11.8 Admin nav integration

In `layouts/admin.blade.php`, add to `$navItems`:

```php
['route' => 'admin.newsletter.dashboard', 'label' => 'Newsletter', 'icon' => 'envelope'],
```

Add a new SVG branch for the `envelope` icon (heroicons-o `envelope`) in the
same icon chain as `home`, `users`, `document`, `chat`, `cog`, etc.

## 12. Testing strategy — 22 tests

### Unit (3)

1. `NewsletterSubscriberTest::generates_unsubscribe_token_on_create`
2. `NewsletterSubscriberTest::is_confirmed_returns_true_only_when_confirmed_and_not_unsubscribed`
3. `NewsletterCampaignTest::is_editable_only_for_draft_status`

### Feature — subscribe + confirm (5)

4. `SubscribeTest::submit_creates_pending_subscriber_and_queues_confirmation_mail`
5. `SubscribeTest::submit_does_not_leak_already_confirmed_email`
6. `SubscribeTest::recycles_unsubscribed_row_on_resubmit`
7. `ConfirmSubscriptionTest::valid_token_marks_as_confirmed_and_nulls_token`
8. `ConfirmSubscriptionTest::expired_token_shows_error_message`

### Feature — unsubscribe (3)

9. `UnsubscribeTest::valid_token_marks_as_unsubscribed`
10. `UnsubscribeTest::post_accepts_rfc_8058_one_click`
11. `UnsubscribeTest::bumps_unsubscribes_count_on_most_recent_campaign`

### Feature — send pipeline (5)

12. `LaunchCampaignSendTest::dispatches_batches_of_50`
13. `LaunchCampaignSendTest::fails_gracefully_when_no_active_subscribers`
14. `SendCampaignBatchTest::creates_send_rows_and_increments_sent_count`
15. `SendCampaignBatchTest::is_idempotent_on_retry`
16. `SendCampaignBatchTest::skips_unsubscribed_mid_send`

### Feature — tracking (2)

17. `NewsletterTrackingTest::open_endpoint_marks_send_as_opened_and_increments_counter`
18. `NewsletterTrackingTest::open_endpoint_returns_pixel_even_for_invalid_token`

### Feature — admin UI (4)

19. `AdminCampaignsTest::admin_can_create_draft_and_save`
20. `AdminCampaignsTest::admin_can_send_test_mail_without_affecting_subscribers`
21. `AdminSubscribersTest::csv_import_creates_subscribers_with_admin_import_source`
22. `AdminNewsletterAccessTest::non_admin_cannot_access_newsletter_pages`

## 13. Acceptance criteria

- [ ] Public form submission creates a `pending` subscriber and queues a
      confirmation mail. The user cannot tell whether the email was already
      registered (no response differentiation).
- [ ] Clicking the confirmation link marks the row as `confirmed_at = now()`
      and nulls the `confirmation_token`.
- [ ] Confirmation links expire after 7 days; the page shows an explicit
      error message in French if clicked after.
- [ ] Clicking the unsubscribe link in a campaign mail marks the row as
      `unsubscribed_at = now()` on the first click. Subsequent clicks do
      not re-trigger any action.
- [ ] The `/newsletter/desabonner/{token}` route accepts POST without CSRF,
      returning 200 for RFC 8058 one-click clients (Gmail, Outlook).
- [ ] `List-Unsubscribe` and `List-Unsubscribe-Post` headers are present
      on every campaign mail.
- [ ] An admin can create a draft campaign, save it, preview it in a new
      tab, send a test to their own address, and launch the real send.
- [ ] Launching a send transitions `status = sending` and dispatches a
      `Bus::batch()` of `SendCampaignBatch` jobs with 50 subscribers each.
- [ ] The rate limiter `newsletter-sending` throttles at 300 mails/minute.
- [ ] Re-running a batched send job (simulated crash + retry) does **not**
      create duplicate `newsletter_campaign_sends` rows — idempotence via
      unique constraint + explicit check.
- [ ] The pixel tracking endpoint increments `opens_count` exactly once
      per unique `open_token`, even if the pixel is loaded multiple times.
- [ ] The pixel endpoint returns a valid 1x1 PNG regardless of whether the
      token is valid, preventing token enumeration via response differences.
- [ ] The CSV import parses a file with `email,first_name` columns, creates
      subscribers with `confirmed_at = now()` and `source = 'admin_import'`,
      skips duplicates, and displays the import summary.
- [ ] All 22 tests pass.
- [ ] No regression on the existing test suite.

## 14. Cross-project dependencies

**Consumes**:

- **② RBAC** — permissions `newsletter.subscribers.view`,
  `newsletter.campaigns.compose`, `newsletter.campaigns.send`, and
  `admin.access`. All four are defined in the ② RBAC spec's permission
  inventory. The `admin` role has all four; `editor` and `member` have none.

- **③ Blog editor** — the TipTap editor component `<x-tiptap-editor>`, the
  `BlogContentSanitizer` service, and the HTMLPurifier `blog` profile. Zero
  new editor code. **This sub-project cannot be implemented until ③ has
  shipped** — the composer's TipTap instance is a hard dependency.

- **① Settings** — no mandatory setting at MVP. A future `newsletter.enabled`
  kill-switch setting could be added but is not required for this spec.

**Consumed by**: none. This is the terminal sub-project of the admin
autonomy roadmap.

## 15. Open questions / deferred

- **Bounce handling** — an SMTP bounce is currently captured in `error` on
  the `newsletter_campaign_sends` row but does not auto-unsubscribe the
  address. A follow-up could auto-mark subscribers as unsubscribed after
  N consecutive bounces, or after a hard bounce (5xx permanent). Requires
  parsing bounce notifications from the mail provider — out of scope.
- **Click tracking** — URL rewriting to count clicks per link. ~200 LoC.
  Nice-to-have, not blocking.
- **Scheduled sending** — a `scheduled_for` column + an artisan scheduled
  task scanning for campaigns whose time has come would add ~100 LoC.
  Deferred; the admin can always save a draft and manually launch at the
  target time.
- **Preview rendering fidelity** — the preview page uses a fake subscriber.
  Merge tags like `{{ $subscriber->first_name ?? 'abonné' }}` (if ever
  supported) would render differently in preview vs. actual send. At MVP,
  no merge tags — the campaign content is static HTML.
- **Attachment support** — no attachments in MVP (campaigns are HTML-only).
  Files would require a separate upload pipeline and disk storage policy.
