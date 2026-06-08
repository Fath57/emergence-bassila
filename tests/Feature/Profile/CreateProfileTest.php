<?php

use App\Livewire\Profile\CreateProfile;
use App\Models\Country;
use App\Models\Profile;
use App\Models\Sector;
use App\Models\User;
use App\Models\Village;
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
    $sector = Sector::factory()->create();
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
        'user_id' => $user->id,
        'first_name' => 'Jean',
        'last_name' => 'Dupont',
        'full_name' => 'Jean Dupont', // generated column
        'job_title' => 'Développeur',
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

it('persists show_phone, show_email_contact and show_whatsapp correctly', function () {
    Storage::fake('s3');

    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->assignRole('member');
    $sector = Sector::factory()->create();
    $country = Country::create(['name' => 'Bénin', 'code' => 'BJ', 'flag' => '🇧🇯', 'sort_order' => 1]);

    Livewire::actingAs($user)
        ->test(CreateProfile::class)
        ->set('first_name', 'Jean')
        ->set('last_name', 'Dupont')
        ->set('job_title', 'Développeur')
        ->set('country_id', $country->id)
        ->set('sector_id', $sector->id)
        ->set('phone', '+22960000000')
        ->set('show_phone', true)
        ->set('email_contact', 'jean@example.com')
        ->set('show_email_contact', false)
        ->set('whatsapp', '+22961000000')
        ->set('show_whatsapp', true)
        ->call('save');

    $this->assertDatabaseHas('profiles', [
        'user_id' => $user->id,
        'phone' => '+22960000000',
        'show_phone' => true,
        'email_contact' => 'jean@example.com',
        'show_email_contact' => false,
        'whatsapp' => '+22961000000',
        'show_whatsapp' => true,
    ]);
});

it('shows public profile page', function () {
    $profile = Profile::factory()->create();

    $this->get(route('profile.show', $profile))
        ->assertSuccessful()
        ->assertSee($profile->full_name);
});

it('saves gender, whatsapp and village_id on profile creation', function () {
    Storage::fake('s3');

    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->assignRole('member');
    $sector = Sector::factory()->create();
    $country = Country::create(['name' => 'Bénin', 'code' => 'BJ', 'flag' => '🇧🇯', 'sort_order' => 1]);
    $village = Village::create(['name' => 'Bassila', 'arrondissement' => 'Bassila', 'is_active' => true, 'sort_order' => 1]);

    Livewire::actingAs($user)
        ->test(CreateProfile::class)
        ->set('first_name', 'Fatou')
        ->set('last_name', 'Idrissou')
        ->set('job_title', 'Enseignante')
        ->set('country_id', $country->id)
        ->set('sector_id', $sector->id)
        ->set('gender', 'F')
        ->set('whatsapp', '+22901000000')
        ->set('show_whatsapp', true)
        ->set('village_id', $village->id)
        ->call('save');

    $this->assertDatabaseHas('profiles', [
        'user_id' => $user->id,
        'gender' => 'F',
        'whatsapp' => '+22901000000',
        'show_whatsapp' => true,
        'village_id' => $village->id,
    ]);
});

it('saves the education level on profile creation', function () {
    Storage::fake('s3');

    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->assignRole('member');
    $sector = Sector::factory()->create();
    $country = Country::create(['name' => 'Bénin', 'code' => 'BJ', 'flag' => '🇧🇯', 'sort_order' => 1]);

    Livewire::actingAs($user)
        ->test(CreateProfile::class)
        ->set('first_name', 'Awa')
        ->set('last_name', 'Sambo')
        ->set('job_title', 'Sage-femme')
        ->set('country_id', $country->id)
        ->set('sector_id', $sector->id)
        ->set('education_level', 'Licence (BAC+3)')
        ->call('save');

    $this->assertDatabaseHas('profiles', [
        'user_id' => $user->id,
        'education_level' => 'Licence (BAC+3)',
    ]);
});

it('rejects an education level outside the allowed list', function () {
    Storage::fake('s3');

    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->assignRole('member');
    $sector = Sector::factory()->create();
    $country = Country::create(['name' => 'Bénin', 'code' => 'BK', 'flag' => '🇧🇯', 'sort_order' => 1]);

    Livewire::actingAs($user)
        ->test(CreateProfile::class)
        ->set('first_name', 'Awa')
        ->set('last_name', 'Sambo')
        ->set('job_title', 'Sage-femme')
        ->set('country_id', $country->id)
        ->set('sector_id', $sector->id)
        ->set('education_level', 'PhD inventé')
        ->call('save')
        ->assertHasErrors(['education_level']);
});

it('rejects invalid gender value', function () {
    Storage::fake('s3');

    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->assignRole('member');
    $sector = Sector::factory()->create();
    $country = Country::create(['name' => 'Bénin', 'code' => 'BX', 'flag' => '🇧🇯', 'sort_order' => 1]);

    Livewire::actingAs($user)
        ->test(CreateProfile::class)
        ->set('first_name', 'Jean')
        ->set('last_name', 'Test')
        ->set('job_title', 'Dev')
        ->set('country_id', $country->id)
        ->set('sector_id', $sector->id)
        ->set('gender', 'X')
        ->call('save')
        ->assertHasErrors(['gender']);
});
