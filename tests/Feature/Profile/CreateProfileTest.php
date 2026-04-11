<?php

use App\Livewire\Profile\CreateProfile;
use App\Models\Country;
use App\Models\Profile;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

it('renders the create profile form for authenticated verified users', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->assignRole('member');

    $this->actingAs($user)
        ->get(route('profile.create'))
        ->assertSuccessful();
});

it('can create a profile without avatar', function () {
    Storage::fake('s3');

    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->assignRole('member');
    $sector  = Sector::factory()->create();
    $country = Country::create(['name' => 'Bénin', 'code' => 'BJ', 'flag' => '🇧🇯', 'sort_order' => 1]);

    Livewire::actingAs($user)
        ->test(CreateProfile::class)
        ->set('first_name', 'Jean')
        ->set('last_name', 'Dupont')
        ->set('job_title', 'Développeur')
        ->set('country_id', $country->id)
        ->set('sector_id', $sector->id)
        ->call('save');

    $this->assertDatabaseHas('profiles', [
        'user_id'    => $user->id,
        'first_name' => 'Jean',
        'last_name'  => 'Dupont',
        'full_name'  => 'Jean Dupont', // generated column
        'job_title'  => 'Développeur',
    ]);
});

it('validates required fields for profile creation', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->assignRole('member');

    // mount() prefills first_name / last_name from the user; clear them so required rules trigger
    Livewire::actingAs($user)
        ->test(CreateProfile::class)
        ->set('first_name', '')
        ->set('last_name', '')
        ->call('save')
        ->assertHasErrors(['first_name', 'last_name', 'job_title', 'sector_id']);
});

it('shows public profile page', function () {
    $profile = Profile::factory()->create();

    $this->get(route('profile.show', $profile))
        ->assertSuccessful()
        ->assertSee($profile->full_name);
});
