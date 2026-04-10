<?php

use App\Livewire\Profile\EditProfile;
use App\Models\Profile;
use App\Models\User;
use Livewire\Livewire;

it('can edit own profile', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->assignRole('user');
    $profile = Profile::factory()->create(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test(EditProfile::class)
        ->set('full_name', 'Nouveau Nom')
        ->set('job_title', 'Nouveau Poste')
        ->set('country', 'France')
        ->set('sector_id', $profile->sector_id)
        ->call('save');

    $this->assertDatabaseHas('profiles', [
        'id'        => $profile->id,
        'full_name' => 'Nouveau Nom',
    ]);
});

it('cannot edit another user\'s profile', function () {
    $owner = User::factory()->create(['email_verified_at' => now()]);
    $owner->assignRole('user');
    $other = User::factory()->create(['email_verified_at' => now()]);
    $other->assignRole('user');
    $profile = Profile::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($other)
        ->get(route('profile.edit'))
        ->assertRedirect();
});
