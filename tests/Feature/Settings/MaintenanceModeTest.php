<?php

use App\Models\Setting;
use App\Models\User;

beforeEach(function () {
    Setting::create([
        'key' => 'site.maintenance_mode', 'value' => '1', 'type' => 'bool',
        'group' => 'site', 'sort_order' => 1,
        'label' => 'Mode maintenance', 'description' => null,
    ]);
    Setting::create([
        'key' => 'site.maintenance_message', 'value' => 'Maintenance en cours',
        'type' => 'string', 'group' => 'site', 'sort_order' => 2,
        'label' => 'Message', 'description' => null,
    ]);
});

it('blocks guests with a 503 when maintenance mode is enabled', function () {
    $response = $this->get('/');

    $response->assertStatus(503)
             ->assertSee('Maintenance en cours');
});

it('allows admin users to bypass maintenance mode', function () {
    $admin = User::factory()->create(['email_verified_at' => now()]);
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->get('/')
        ->assertSuccessful();
});

it('keeps the login route accessible during maintenance', function () {
    $this->get('/connexion')->assertSuccessful();
});

it('keeps the admin routes accessible during maintenance for logged-in admins', function () {
    $admin = User::factory()->create(['email_verified_at' => now()]);
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->get('/admin')
        ->assertSuccessful();
});

it('passes through when maintenance mode is disabled', function () {
    Setting::where('key', 'site.maintenance_mode')->update(['value' => '0']);

    $this->get('/')->assertSuccessful();
});
