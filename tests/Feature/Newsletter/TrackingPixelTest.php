<?php

use App\Models\NewsletterCampaign;
use App\Models\NewsletterCampaignSend;
use App\Models\NewsletterSubscriber;
use App\Models\User;

beforeEach(function () {
    $this->seed(\Database\Seeders\RolePermissionSeeder::class);
    $this->admin = User::factory()->create(['email_verified_at' => now(), 'is_active' => true]);
    $this->admin->assignRole('admin');
});

it('pixel endpoint returns a 1x1 GIF and records the open', function () {
    $campaign = NewsletterCampaign::create([
        'subject'    => 'Campaign',
        'content'    => '<p>Hi</p>',
        'status'     => 'sending',
        'created_by' => $this->admin->id,
    ]);

    $sub  = NewsletterSubscriber::create(['email' => 'track@example.com', 'confirmed_at' => now()]);
    $send = $campaign->sends()->create([
        'subscriber_id' => $sub->id,
        'open_token'    => 'tracktoken12345678901234567890ab',
        'sent_at'       => now(),
    ]);

    $response = $this->get('/newsletter/pixel/tracktoken12345678901234567890ab.gif');

    $response->assertOk()
             ->assertHeader('Content-Type', 'image/gif');

    expect($send->fresh()->opened_at)->not->toBeNull();
    expect($campaign->fresh()->opens_count)->toBe(1);
});

it('pixel endpoint is idempotent (second hit does not double-count)', function () {
    $campaign = NewsletterCampaign::create([
        'subject'    => 'Campaign',
        'content'    => '<p>Hi</p>',
        'status'     => 'sending',
        'created_by' => $this->admin->id,
    ]);

    $sub  = NewsletterSubscriber::create(['email' => 'idempotent@example.com', 'confirmed_at' => now()]);
    $send = $campaign->sends()->create([
        'subscriber_id' => $sub->id,
        'open_token'    => 'idempotenttoken1234567890123456',
        'sent_at'       => now(),
        'opened_at'     => now()->subMinute(),
    ]);

    $this->get('/newsletter/pixel/idempotenttoken1234567890123456.gif')->assertOk();

    // opens_count should still be 0 (was not incremented on second hit)
    expect($campaign->fresh()->opens_count)->toBe(0);
});
