<?php

use App\Livewire\Profile\CreateProfile;
use App\Models\Profile;
use App\Models\Sector;
use App\Models\User;
use Livewire\Livewire;

it('renders the create profile form for authenticated verified users', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->assignRole('user');

    $this->actingAs($user)
        ->get(route('profile.create'))
        ->assertSuccessful();
});

it('can create a profile without avatar', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->assignRole('user');
    $sector = Sector::factory()->create();

    Livewire::actingAs($user)
        ->test(CreateProfile::class)
        ->set('full_name', 'Jean Dupont')
        ->set('job_title', 'Développeur')
        ->set('country', 'Bénin')
        ->set('sector_id', $sector->id)
        ->call('save');

    $this->assertDatabaseHas('profiles', [
        'user_id'   => $user->id,
        'full_name' => 'Jean Dupont',
        'job_title' => 'Développeur',
    ]);
});

it('validates required fields for profile creation', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->assignRole('user');

    Livewire::actingAs($user)
        ->test(CreateProfile::class)
        ->call('save')
        ->assertHasErrors(['full_name', 'job_title', 'country', 'sector_id']);
});

it('shows public profile page', function () {
    $profile = Profile::factory()->create();

    $this->get(route('profile.show', $profile))
        ->assertSuccessful()
        ->assertSee($profile->full_name);
});
