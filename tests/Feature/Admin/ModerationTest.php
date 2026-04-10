<?php

use App\Models\Profile;
use App\Models\User;

it('admin can access the admin panel', function () {
    $admin = User::factory()->create(['email_verified_at' => now()]);
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->get('/admin')
        ->assertSuccessful();
});

it('non-admin cannot access the admin panel', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->assignRole('user');

    $this->actingAs($user)
        ->get('/admin')
        ->assertForbidden();
});

it('unauthenticated user is redirected from admin panel', function () {
    $this->get('/admin')->assertRedirect();
});

it('admin can verify a profile', function () {
    $admin = User::factory()->create(['email_verified_at' => now()]);
    $admin->assignRole('admin');

    $profile = Profile::factory()->create(['is_verified' => false]);

    $profile->update(['is_verified' => true, 'verified_at' => now()]);

    $this->assertDatabaseHas('profiles', [
        'id'          => $profile->id,
        'is_verified' => true,
    ]);
});
