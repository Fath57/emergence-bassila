<?php

use App\Livewire\Profile\EditProfile;
use App\Models\Country;
use App\Models\Profile;
use App\Models\Sector;
use App\Models\User;
use App\Models\Village;
use Livewire\Livewire;

it('can edit own profile', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->assignRole('member');
    $profile = Profile::factory()->create(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test(EditProfile::class)
        ->set('first_name', 'Nouveau')
        ->set('last_name', 'Nom')
        ->set('job_title', 'Nouveau Poste')
        ->set('country', 'France')
        ->set('sector_id', $profile->sector_id)
        ->call('save');

    $this->assertDatabaseHas('profiles', [
        'id' => $profile->id,
        'first_name' => 'Nouveau',
        'last_name' => 'Nom',
        'full_name' => 'Nouveau Nom',
    ]);
});

it('cannot edit another user\'s profile', function () {
    $owner = User::factory()->create(['email_verified_at' => now()]);
    $owner->assignRole('member');
    $other = User::factory()->create(['email_verified_at' => now()]);
    $other->assignRole('member');
    $profile = Profile::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($other)
        ->get(route('profile.edit'))
        ->assertRedirect();
});

it('saves phone, email_contact and visibility flags', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->assignRole('member');
    $profile = Profile::factory()->create([
        'user_id' => $user->id,
        'show_email_contact' => true,  // force known starting state so we can verify false was written
    ]);

    Livewire::actingAs($user)
        ->test(EditProfile::class)
        ->set('phone', '+229 01 23 45 67')
        ->set('show_phone', true)
        ->set('email_contact', 'pro@example.com')
        ->set('show_email_contact', false)
        ->call('save');

    $this->assertDatabaseHas('profiles', [
        'id' => $profile->id,
        'phone' => '+229 01 23 45 67',
        'show_phone' => true,
        'email_contact' => 'pro@example.com',
        'show_email_contact' => false,
    ]);
});

it('saves gender, whatsapp and village_id on profile edit', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->assignRole('member');

    $sector = Sector::factory()->create();
    $country = Country::create(['name' => 'Bénin', 'code' => 'BY', 'flag' => '🇧🇯', 'sort_order' => 1]);
    $village = Village::create(['name' => 'Manigri', 'arrondissement' => 'Manigri', 'is_active' => true, 'sort_order' => 1]);

    $profile = Profile::factory()->create([
        'user_id' => $user->id,
        'country' => $country->name,
        'country_id' => $country->id,
        'sector_id' => $sector->id,
    ]);

    Livewire::actingAs($user)
        ->test(EditProfile::class)
        ->set('gender', 'M')
        ->set('whatsapp', '+22900000001')
        ->set('show_whatsapp', true)
        ->set('village_id', $village->id)
        ->call('save');

    $this->assertDatabaseHas('profiles', [
        'id' => $profile->id,
        'gender' => 'M',
        'whatsapp' => '+22900000001',
        'show_whatsapp' => true,
        'village_id' => $village->id,
    ]);
});

it('unchecks visibility when contact field is cleared', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->assignRole('member');
    Profile::factory()->create([
        'user_id' => $user->id,
        'email_contact' => 'x@example.com',
        'show_email_contact' => true,
        'phone' => '+229111',
        'show_phone' => true,
        'whatsapp' => '+229222',
        'show_whatsapp' => true,
    ]);

    Livewire::actingAs($user)
        ->test(EditProfile::class)
        ->set('email_contact', '')
        ->assertSet('show_email_contact', false)
        ->set('phone', '')
        ->assertSet('show_phone', false)
        ->set('whatsapp', '')
        ->assertSet('show_whatsapp', false);
});
