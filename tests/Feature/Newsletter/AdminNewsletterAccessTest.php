<?php

use App\Models\User;

beforeEach(function () {
    $this->seed(\Database\Seeders\RolePermissionSeeder::class);

    $this->admin = User::factory()->create(['email_verified_at' => now(), 'is_active' => true]);
    $this->admin->assignRole('admin');

    $this->member = User::factory()->create(['email_verified_at' => now(), 'is_active' => true]);
    $this->member->assignRole('member');
});

it('admin can access the newsletter campaigns page', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.newsletter'))
        ->assertOk()
        ->assertSee('Campagnes');
});

it('non-admin member is forbidden from newsletter pages', function () {
    $this->actingAs($this->member)
        ->get(route('admin.newsletter'))
        ->assertForbidden();
});

it('non-admin member is forbidden from newsletter subscribers page', function () {
    $this->actingAs($this->member)
        ->get(route('admin.newsletter.subscribers'))
        ->assertForbidden();
});

it('unauthenticated user is redirected from newsletter admin', function () {
    $this->get(route('admin.newsletter'))
        ->assertRedirect(route('login'));
});
