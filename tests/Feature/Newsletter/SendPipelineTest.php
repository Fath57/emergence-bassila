<?php

use App\Actions\Newsletter\LaunchCampaignSend;
use App\Jobs\SendNewsletterCampaignMail;
use App\Mail\NewsletterCampaignMail;
use App\Models\NewsletterCampaign;
use App\Models\NewsletterSubscriber;
use App\Models\User;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    $this->seed(\Database\Seeders\RolePermissionSeeder::class);
    $this->admin = User::factory()->create(['email_verified_at' => now(), 'is_active' => true]);
    $this->admin->assignRole('admin');
});

it('LaunchCampaignSend sets campaign to sending and creates send rows', function () {
    Bus::fake();

    $campaign = NewsletterCampaign::create([
        'subject'    => 'Test Campaign',
        'content'    => '<p>Hello</p>',
        'status'     => 'draft',
        'created_by' => $this->admin->id,
    ]);

    NewsletterSubscriber::create(['email' => 'a@example.com', 'confirmed_at' => now()]);
    NewsletterSubscriber::create(['email' => 'b@example.com', 'confirmed_at' => now()]);

    (new LaunchCampaignSend)->run($campaign);

    $campaign->refresh();
    expect($campaign->status)->toBe('sending')
        ->and($campaign->recipients_count)->toBe(2)
        ->and($campaign->sends()->count())->toBe(2);

    Bus::assertBatched(fn ($batch) => $batch->jobs->count() === 2);
});

it('LaunchCampaignSend only creates sends for active subscribers', function () {
    Bus::fake();

    $campaign = NewsletterCampaign::create([
        'subject'    => 'Test Active',
        'content'    => '<p>Hi</p>',
        'status'     => 'draft',
        'created_by' => $this->admin->id,
    ]);

    // Active subscriber
    NewsletterSubscriber::create(['email' => 'active@example.com', 'confirmed_at' => now()]);
    // Pending (never confirmed)
    NewsletterSubscriber::create(['email' => 'pending@example.com']);
    // Unsubscribed
    NewsletterSubscriber::create(['email' => 'unsub@example.com', 'confirmed_at' => now()->subMonth(), 'unsubscribed_at' => now()]);

    (new LaunchCampaignSend)->run($campaign);

    expect($campaign->fresh()->recipients_count)->toBe(1)
        ->and($campaign->sends()->count())->toBe(1);
});

it('LaunchCampaignSend throws when campaign is not draft', function () {
    $campaign = NewsletterCampaign::create([
        'subject'    => 'Already sent',
        'content'    => '<p>Hi</p>',
        'status'     => 'sent',
        'created_by' => $this->admin->id,
    ]);

    expect(fn () => (new LaunchCampaignSend)->run($campaign))
        ->toThrow(\InvalidArgumentException::class);
});

it('SendNewsletterCampaignMail job sends mail and increments sent_count', function () {
    Mail::fake();

    $campaign = NewsletterCampaign::create([
        'subject'    => 'Campaign',
        'content'    => '<p>Body</p>',
        'status'     => 'sending',
        'created_by' => $this->admin->id,
    ]);

    $sub = NewsletterSubscriber::create(['email' => 'recv@example.com', 'confirmed_at' => now()]);

    $send = $campaign->sends()->create([
        'subscriber_id' => $sub->id,
        'open_token'    => 'tok123',
        'sent_at'       => now(),
    ]);

    (new SendNewsletterCampaignMail($send->id))->handle();

    Mail::assertSent(NewsletterCampaignMail::class, fn ($m) => $m->send->id === $send->id);
    expect($campaign->fresh()->sent_count)->toBe(1);
});

it('SendNewsletterCampaignMail failed() records error on the send row', function () {
    $campaign = NewsletterCampaign::create([
        'subject'    => 'Campaign',
        'content'    => '<p>Body</p>',
        'status'     => 'sending',
        'created_by' => $this->admin->id,
    ]);

    $sub  = NewsletterSubscriber::create(['email' => 'err@example.com', 'confirmed_at' => now()]);
    $send = $campaign->sends()->create([
        'subscriber_id' => $sub->id,
        'open_token'    => 'errtok',
        'sent_at'       => now(),
    ]);

    $job = new SendNewsletterCampaignMail($send->id);
    $job->failed(new \RuntimeException('SMTP connection refused'));

    expect($send->fresh()->error)->toContain('SMTP connection refused');
});
