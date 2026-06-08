<?php

use App\Models\Setting;
use App\Models\User;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;

/**
 * Stub Socialite so the callback resolves a fixed Google profile without
 * making a real OAuth round-trip.
 */
function fakeGoogleUser(array $attrs = []): void
{
    $abstract = Mockery::mock(SocialiteUser::class);
    $abstract->shouldReceive('getId')->andReturn($attrs['id'] ?? 'google-123');
    $abstract->shouldReceive('getEmail')->andReturn($attrs['email'] ?? 'nouveau@gmail.com');
    $abstract->shouldReceive('getName')->andReturn($attrs['name'] ?? 'Jean Dupont');
    $abstract->shouldReceive('getAvatar')->andReturn($attrs['avatar'] ?? 'https://lh3.googleusercontent.com/a/photo');

    $provider = Mockery::mock(Provider::class);
    $provider->shouldReceive('user')->andReturn($abstract);

    Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
}

it('redirects to Google from the redirect endpoint', function () {
    $provider = Mockery::mock(Provider::class);
    $provider->shouldReceive('redirect')->andReturn(redirect('https://accounts.google.com/o/oauth2/auth'));
    Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

    $this->get(route('auth.google.redirect'))
        ->assertRedirect('https://accounts.google.com/o/oauth2/auth');
});

it('creates a new verified member account from a Google profile', function () {
    fakeGoogleUser(['email' => 'nouveau@gmail.com', 'name' => 'Jean Dupont', 'id' => 'g-1']);

    $response = $this->get(route('auth.google.callback'));

    $response->assertRedirect(route('profile.create'));
    $this->assertAuthenticated();

    $user = User::where('email', 'nouveau@gmail.com')->first();
    expect($user)->not->toBeNull()
        ->and($user->google_id)->toBe('g-1')
        ->and($user->first_name)->toBe('Jean')
        ->and($user->last_name)->toBe('Dupont')
        ->and($user->hasVerifiedEmail())->toBeTrue()
        ->and($user->hasRole('member'))->toBeTrue();
});

it('links Google to an existing account matched by email', function () {
    $existing = User::factory()->create([
        'email' => 'membre@gmail.com',
        'google_id' => null,
    ]);
    $existing->assignRole('member');

    fakeGoogleUser(['email' => 'membre@gmail.com', 'id' => 'g-existing']);

    $response = $this->get(route('auth.google.callback'));

    $response->assertRedirect(route('directory.index'));
    $this->assertAuthenticatedAs($existing);
    expect($existing->fresh()->google_id)->toBe('g-existing');
});

it('does not create a duplicate when the Google id is already linked', function () {
    $existing = User::factory()->create(['email' => 'deja@gmail.com', 'google_id' => 'g-known']);
    $existing->assignRole('member');

    fakeGoogleUser(['email' => 'deja@gmail.com', 'id' => 'g-known']);

    $this->get(route('auth.google.callback'));

    expect(User::where('email', 'deja@gmail.com')->count())->toBe(1);
    $this->assertAuthenticatedAs($existing);
});

it('blocks new Google sign-ups when registration is closed', function () {
    Setting::create([
        'key' => 'site.registration_open', 'value' => '0', 'type' => 'bool',
        'group' => 'site', 'sort_order' => 1,
        'label' => 'Inscriptions ouvertes', 'description' => null,
    ]);

    fakeGoogleUser(['email' => 'refuse@gmail.com', 'id' => 'g-blocked']);

    $response = $this->get(route('auth.google.callback'));

    $response->assertRedirect(route('login'));
    $response->assertSessionHas('error');
    $this->assertGuest();
    expect(User::where('email', 'refuse@gmail.com')->exists())->toBeFalse();
});

it('rejects a deactivated account on Google login', function () {
    $user = User::factory()->create([
        'email' => 'inactif@gmail.com',
        'google_id' => 'g-off',
        'is_active' => false,
    ]);
    $user->assignRole('member');

    fakeGoogleUser(['email' => 'inactif@gmail.com', 'id' => 'g-off']);

    $response = $this->get(route('auth.google.callback'));

    $response->assertRedirect(route('login'));
    $response->assertSessionHas('error');
    $this->assertGuest();
});
