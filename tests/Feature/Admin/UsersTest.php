<?php

use App\Models\User;

it('allows an admin to see the users list page', function () {
    $admin = User::factory()->create(['email_verified_at' => now()]);
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->get('/admin/utilisateurs')
        ->assertSuccessful()
        ->assertSee('Tous les utilisateurs')
        ->assertSee('Invitations en attente');
});

it('forbids non-admin users from the users list page', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->assignRole('member');

    $this->actingAs($user)
        ->get('/admin/utilisateurs')
        ->assertForbidden();
});

it('filters users by role', function () {
    $admin = User::factory()->create(['email_verified_at' => now()]);
    $admin->assignRole('admin');

    $editor = User::factory()->create(['email_verified_at' => now(), 'first_name' => 'Ed', 'last_name' => 'Itor']);
    $editor->assignRole('editor');

    $member = User::factory()->create(['email_verified_at' => now(), 'first_name' => 'Mem', 'last_name' => 'Ber']);
    $member->assignRole('member');

    $this->actingAs($admin)
        ->get('/admin/utilisateurs?roleFilter=editor')
        ->assertSuccessful()
        ->assertSee('Ed Itor')
        ->assertDontSee('Mem Ber');
});
