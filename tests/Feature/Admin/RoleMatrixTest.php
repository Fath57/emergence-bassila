<?php

use App\Models\User;

it('admin can see the role matrix page', function () {
    $admin = User::factory()->create(['email_verified_at' => now()]);
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->get('/admin/roles')
        ->assertSuccessful()
        ->assertSee('Rôles')
        ->assertSee('permissions')
        ->assertSee('Articles')
        ->assertSee('posts.create')
        ->assertSee('admin.access');
});

it('forbids non-admin users from the role matrix', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->assignRole('member');

    $this->actingAs($user)
        ->get('/admin/roles')
        ->assertForbidden();
});
