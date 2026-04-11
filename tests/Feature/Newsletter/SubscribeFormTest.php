<?php

use App\Livewire\Newsletter\SubscribeForm;
use App\Models\NewsletterSubscriber;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

beforeEach(function () {
    Mail::fake();
});

it('creates a confirmed subscriber immediately (single opt-in)', function () {
    Livewire::test(SubscribeForm::class)
        ->set('email', 'fatima@example.com')
        ->set('firstName', 'Fatima')
        ->call('subscribe')
        ->assertHasNoErrors()
        ->assertSet('pending', true);

    $sub = NewsletterSubscriber::where('email', 'fatima@example.com')->sole();
    expect($sub->confirmed_at)->not->toBeNull()
        ->and($sub->first_name)->toBe('Fatima')
        ->and($sub->source)->toBe('public_form');

    Mail::assertNothingQueued();
});

it('rejects an invalid email format', function () {
    Livewire::test(SubscribeForm::class)
        ->set('email', 'not-an-email')
        ->call('subscribe')
        ->assertHasErrors(['email'])
        ->assertSet('pending', false);

    expect(NewsletterSubscriber::count())->toBe(0);
});

it('shows an error when the email is already confirmed', function () {
    NewsletterSubscriber::create([
        'email'        => 'already@example.com',
        'confirmed_at' => now(),
    ]);

    Livewire::test(SubscribeForm::class)
        ->set('email', 'already@example.com')
        ->call('subscribe')
        ->assertHasErrors(['email'])
        ->assertSet('pending', false);

    expect(NewsletterSubscriber::where('email', 'already@example.com')->count())->toBe(1);
});

it('re-subscribes an unsubscribed email without creating a duplicate', function () {
    $sub = NewsletterSubscriber::create([
        'email'           => 'revenant@example.com',
        'confirmed_at'    => now()->subMonth(),
        'unsubscribed_at' => now()->subDays(5),
    ]);

    Livewire::test(SubscribeForm::class)
        ->set('email', 'revenant@example.com')
        ->call('subscribe')
        ->assertHasNoErrors()
        ->assertSet('pending', true);

    $fresh = $sub->fresh();
    expect($fresh->unsubscribed_at)->toBeNull()
        ->and($fresh->confirmed_at)->not->toBeNull();

    expect(NewsletterSubscriber::count())->toBe(1);
});

it('subscribes a pending (never confirmed) email directly', function () {
    $sub = NewsletterSubscriber::create([
        'email' => 'pending@example.com',
    ]);

    Livewire::test(SubscribeForm::class)
        ->set('email', 'pending@example.com')
        ->call('subscribe')
        ->assertHasNoErrors()
        ->assertSet('pending', true);

    expect($sub->fresh()->confirmed_at)->not->toBeNull();
    expect(NewsletterSubscriber::count())->toBe(1);
});
