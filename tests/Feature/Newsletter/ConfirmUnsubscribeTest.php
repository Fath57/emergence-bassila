<?php

use App\Models\NewsletterSubscriber;

it('confirm token marks subscriber as confirmed and clears the token', function () {
    $sub = NewsletterSubscriber::create([
        'email' => 'confirm@example.com',
        'confirmation_token' => 'abc123validtoken000000000000000000000000000000000000000000000000',
    ]);

    $this->get('/newsletter/confirmer/abc123validtoken000000000000000000000000000000000000000000000000')
        ->assertOk()
        ->assertSee('Inscription confirmée');

    $fresh = $sub->fresh();
    expect($fresh->confirmed_at)->not->toBeNull()
        ->and($fresh->confirmation_token)->toBeNull();
});

it('confirming an already-confirmed token is idempotent and shows success', function () {
    $sub = NewsletterSubscriber::create([
        'email' => 'already-confirmed@example.com',
        'confirmed_at' => now()->subHour(),
    ]);
    // confirmed_at set → confirmation_token was cleared, but test with a leftover token
    $sub->update(['confirmation_token' => 'leftovertoken0000000000000000000000000000000000000000000000000']);

    $this->get('/newsletter/confirmer/leftovertoken0000000000000000000000000000000000000000000000000')
        ->assertOk()
        ->assertSee('Inscription confirmée');

    // confirmed_at unchanged (still set)
    expect($sub->fresh()->confirmed_at)->not->toBeNull();
});

it('invalid confirmation token shows error page', function () {
    $this->get('/newsletter/confirmer/totallyfaketoken')
        ->assertOk()
        ->assertSee('Lien invalide');
});

it('GET unsubscribe with valid token marks subscriber unsubscribed', function () {
    $sub = NewsletterSubscriber::create([
        'email' => 'unsub@example.com',
        'confirmed_at' => now(),
        'unsubscribe_token' => 'unsubtoken00000000000000000000000000000000000000000000000000000',
    ]);

    $this->get('/newsletter/desabonner/unsubtoken00000000000000000000000000000000000000000000000000000')
        ->assertOk()
        ->assertSee('Désabonnement enregistré');

    expect($sub->fresh()->unsubscribed_at)->not->toBeNull();
});

it('POST unsubscribe works without CSRF token (RFC 8058)', function () {
    $sub = NewsletterSubscriber::create([
        'email' => 'rfc8058@example.com',
        'confirmed_at' => now(),
        'unsubscribe_token' => 'rfc8058token000000000000000000000000000000000000000000000000000',
    ]);

    // withoutMiddleware is not needed — the CSRF exception is in bootstrap/app.php
    $this->post('/newsletter/desabonner/rfc8058token000000000000000000000000000000000000000000000000000')
        ->assertOk()
        ->assertSee('Désabonnement enregistré');

    expect($sub->fresh()->unsubscribed_at)->not->toBeNull();
});
