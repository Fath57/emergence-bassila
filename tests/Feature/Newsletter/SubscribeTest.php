<?php

use App\Livewire\Newsletter\SubscribeForm;
use App\Models\NewsletterSubscription;
use Livewire\Livewire;

it('newsletter_subscriptions table exists', function () {
    expect(\Illuminate\Support\Facades\Schema::hasTable('newsletter_subscriptions'))->toBeTrue();
});

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
