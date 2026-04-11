<?php

use App\Livewire\Auth\AcceptInvitation;
use App\Models\User;
use App\Models\UserInvitation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Livewire;

it('returns 404 when the invitation token is unknown', function () {
    $this->get('/invitation/nonexistent-token-xyz')->assertNotFound();
});

it('redirects to login with a flash message when the invitation is expired', function () {
    $inviter = User::factory()->create(['email_verified_at' => now()]);
    $inviter->assignRole('admin');

    $invitation = UserInvitation::create([
        'email'       => 'stale@example.com',
        'role'        => 'member',
        'token'       => Str::random(64),
        'invited_by'  => $inviter->id,
        'expires_at'  => now()->subDay(),
    ]);

    $this->get(route('invitation.accept', ['token' => $invitation->token]))
        ->assertRedirect(route('login'));

    expect(session('error'))->toContain('expiré');
});

it('redirects to login when the invitation has already been accepted', function () {
    $inviter = User::factory()->create(['email_verified_at' => now()]);
    $inviter->assignRole('admin');

    $invitation = UserInvitation::create([
        'email'        => 'used@example.com',
        'role'         => 'member',
        'token'        => Str::random(64),
        'invited_by'   => $inviter->id,
        'expires_at'   => now()->addDays(7),
        'accepted_at'  => now()->subHour(),
    ]);

    $this->get(route('invitation.accept', ['token' => $invitation->token]))
        ->assertRedirect(route('login'));

    expect(session('error'))->toContain('déjà été utilisée');
});

it('creates a user with the correct role and auto-logs in on the happy path', function () {
    $inviter = User::factory()->create(['email_verified_at' => now()]);
    $inviter->assignRole('admin');

    $invitation = UserInvitation::create([
        'email'       => 'newbie@example.com',
        'first_name'  => 'Newbie',
        'last_name'   => 'Member',
        'role'        => 'member',
        'token'       => Str::random(64),
        'invited_by'  => $inviter->id,
        'expires_at'  => now()->addDays(7),
    ]);

    Livewire::test(AcceptInvitation::class, ['token' => $invitation->token])
        ->set('first_name', 'Newbie')
        ->set('last_name', 'Member')
        ->set('password', 'supersecret')
        ->set('password_confirmation', 'supersecret')
        ->call('accept')
        ->assertRedirect(route('profile.create'));

    $user = User::where('email', 'newbie@example.com')->first();
    expect($user)->not->toBeNull()
        ->and($user->first_name)->toBe('Newbie')
        ->and($user->last_name)->toBe('Member')
        ->and($user->is_active)->toBeTrue()
        ->and($user->hasRole('member'))->toBeTrue();

    expect($invitation->fresh()->accepted_at)->not->toBeNull();
    expect(Auth::check())->toBeTrue();
    expect(Auth::id())->toBe($user->id);
});

it('enforces password confirmation on accept', function () {
    $inviter = User::factory()->create(['email_verified_at' => now()]);
    $inviter->assignRole('admin');

    $invitation = UserInvitation::create([
        'email'       => 'mismatch@example.com',
        'role'        => 'member',
        'token'       => Str::random(64),
        'invited_by'  => $inviter->id,
        'expires_at'  => now()->addDays(7),
    ]);

    Livewire::test(AcceptInvitation::class, ['token' => $invitation->token])
        ->set('first_name', 'Foo')
        ->set('last_name', 'Bar')
        ->set('password', 'supersecret')
        ->set('password_confirmation', 'different')
        ->call('accept')
        ->assertHasErrors('password');

    expect(User::where('email', 'mismatch@example.com')->exists())->toBeFalse();
});
