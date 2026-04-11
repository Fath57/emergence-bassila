<?php

use App\Livewire\Newsletter\SubscribeForm;
use App\Mail\NewsletterConfirmationMail;
use App\Models\NewsletterSubscriber;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

beforeEach(function () {
    Mail::fake();
});

it('creates a pending subscriber and queues a confirmation mail', function () {
    Livewire::test(SubscribeForm::class)
        ->set('email', 'fatima@example.com')
        ->set('firstName', 'Fatima')
        ->call('subscribe')
        ->assertHasNoErrors()
        ->assertSet('pending', true);

    $sub = NewsletterSubscriber::where('email', 'fatima@example.com')->sole();
    expect($sub->confirmed_at)->toBeNull()
        ->and($sub->confirmation_token)->not->toBeNull()
        ->and($sub->unsubscribe_token)->not->toBeNull()
        ->and($sub->first_name)->toBe('Fatima')
        ->and($sub->source)->toBe('public_form');

    Mail::assertQueued(NewsletterConfirmationMail::class, function ($mail) use ($sub) {
        return $mail->subscriber->id === $sub->id;
    });
});

it('rejects an invalid email format', function () {
    Livewire::test(SubscribeForm::class)
        ->set('email', 'not-an-email')
        ->call('subscribe')
        ->assertHasErrors(['email'])
        ->assertSet('pending', false);

    expect(NewsletterSubscriber::count())->toBe(0);
    Mail::assertNothingQueued();
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
    Mail::assertNothingQueued();
});

it('re-subscribes an unsubscribed email and sends a new confirmation', function () {
    $sub = NewsletterSubscriber::create([
        'email'            => 'revenant@example.com',
        'confirmed_at'     => now()->subMonth(),
        'unsubscribed_at'  => now()->subDays(5),
    ]);

    Livewire::test(SubscribeForm::class)
        ->set('email', 'revenant@example.com')
        ->call('subscribe')
        ->assertHasNoErrors()
        ->assertSet('pending', true);

    $fresh = $sub->fresh();
    expect($fresh->unsubscribed_at)->toBeNull()
        ->and($fresh->confirmation_token)->not->toBeNull();

    Mail::assertQueued(NewsletterConfirmationMail::class, fn ($m) => $m->subscriber->id === $sub->id);
    expect(NewsletterSubscriber::count())->toBe(1); // no duplicate
});

it('resends confirmation to a pending subscriber without creating a duplicate', function () {
    $sub = NewsletterSubscriber::create([
        'email'      => 'pending@example.com',
        'first_name' => 'Ibrahim',
    ]);
    $oldToken = $sub->confirmation_token;

    Livewire::test(SubscribeForm::class)
        ->set('email', 'pending@example.com')
        ->call('subscribe')
        ->assertHasNoErrors()
        ->assertSet('pending', true);

    $fresh = $sub->fresh();
    expect($fresh->confirmation_token)->not->toBe($oldToken) // new token issued
        ->and($fresh->confirmed_at)->toBeNull();

    Mail::assertQueued(NewsletterConfirmationMail::class, fn ($m) => $m->subscriber->id === $sub->id);
    expect(NewsletterSubscriber::count())->toBe(1);
});
