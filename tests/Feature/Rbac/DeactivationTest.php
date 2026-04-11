<?php

use App\Livewire\Auth\Login;
use App\Models\Profile;
use App\Models\User;
use Livewire\Livewire;

it('deactivated users cannot log in', function () {
    $user = User::factory()->create([
        'email'             => 'deactivated@example.com',
        'password'          => bcrypt('password123'),
        'email_verified_at' => now(),
        'is_active'         => false,
    ]);
    $user->assignRole('member');

    Livewire::test(Login::class)
        ->set('email', 'deactivated@example.com')
        ->set('password', 'password123')
        ->call('login')
        ->assertHasErrors('email');

    expect(auth()->check())->toBeFalse();
});

it('active users can still log in', function () {
    $user = User::factory()->create([
        'email'             => 'active@example.com',
        'password'          => bcrypt('password123'),
        'email_verified_at' => now(),
        'is_active'         => true,
    ]);
    $user->assignRole('member');

    Livewire::test(Login::class)
        ->set('email', 'active@example.com')
        ->set('password', 'password123')
        ->call('login')
        ->assertHasNoErrors();
});

it('returns 404 for the public profile of a deactivated user', function () {
    $user = User::factory()->create(['email_verified_at' => now(), 'is_active' => false]);
    $user->assignRole('member');
    $profile = Profile::factory()->for($user)->create();

    $this->get(route('profile.show', $profile))->assertNotFound();
});

it('shows the public profile of an active user', function () {
    $user = User::factory()->create(['email_verified_at' => now(), 'is_active' => true]);
    $user->assignRole('member');
    $profile = Profile::factory()->for($user)->create();

    $this->get(route('profile.show', $profile))->assertSuccessful();
});

it('hides deactivated users from the directory', function () {
    $activeUser = User::factory()->create(['email_verified_at' => now(), 'is_active' => true]);
    $activeUser->assignRole('member');
    $activeProfile = Profile::factory()->for($activeUser)->create(['first_name' => 'Alice', 'last_name' => 'Active']);

    $deactivatedUser = User::factory()->create(['email_verified_at' => now(), 'is_active' => false]);
    $deactivatedUser->assignRole('member');
    $deactivatedProfile = Profile::factory()->for($deactivatedUser)->create(['first_name' => 'Bob', 'last_name' => 'Banned']);

    $this->get(route('directory.index'))
        ->assertSuccessful()
        ->assertSee('Alice Active')
        ->assertDontSee('Bob Banned');
});
