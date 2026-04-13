<?php

use App\Livewire\Profile\EditProfile;
use App\Models\Profile;
use App\Models\User;
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
        'id'         => $profile->id,
        'first_name' => 'Nouveau',
        'last_name'  => 'Nom',
        'full_name'  => 'Nouveau Nom',
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
    $profile = Profile::factory()->create(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Profile\EditProfile::class)
        ->set('phone', '+229 01 23 45 67')
        ->set('show_phone', true)
        ->set('email_contact', 'pro@example.com')
        ->set('show_email_contact', false)
        ->call('save');

    $this->assertDatabaseHas('profiles', [
        'id'                 => $profile->id,
        'phone'              => '+229 01 23 45 67',
        'show_phone'         => true,
        'email_contact'      => 'pro@example.com',
        'show_email_contact' => false,
    ]);
});
