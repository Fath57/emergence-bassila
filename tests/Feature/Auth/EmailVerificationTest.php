<?php

use App\Livewire\Auth\VerifyEmail;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail as VerifyEmailNotification;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

it('redirects unverified user to verification notice', function () {
    $user = User::factory()->create(['email_verified_at' => null]);

    $this->actingAs($user)
        ->get(route('profile.create'))
        ->assertRedirect(route('verification.notice'));
});

it('allows verified user to access profile creation', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->assignRole('member');

    $this->actingAs($user)
        ->get(route('profile.create'))
        ->assertSuccessful();
});

it('can resend verification email via livewire component', function () {
    Notification::fake();

    $user = User::factory()->create(['email_verified_at' => null]);

    Livewire::actingAs($user)
        ->test(VerifyEmail::class)
        ->call('resend')
        ->assertSet('successMessage', 'Un nouveau lien de vérification a été envoyé à votre adresse email.');

    Notification::assertSentTo($user, VerifyEmailNotification::class);
});
