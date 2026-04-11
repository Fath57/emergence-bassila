<?php

use App\Models\Setting;

beforeEach(function () {
    Setting::create([
        'key' => 'site.registration_open', 'value' => '0', 'type' => 'bool',
        'group' => 'site', 'sort_order' => 1,
        'label' => 'Inscriptions ouvertes', 'description' => null,
    ]);
});

it('redirects /inscription to /connexion when registration is closed', function () {
    $response = $this->get('/inscription');

    $response->assertRedirect(route('login'));
    $response->assertSessionHas('error');
});

it('flashes a French message explaining registration is closed', function () {
    $this->get('/inscription');

    expect(session('error'))->toBe('Les inscriptions sont temporairement fermées.');
});

it('allows /inscription through when registration is open', function () {
    Setting::where('key', 'site.registration_open')->update(['value' => '1']);

    $this->get('/inscription')->assertSuccessful();
});
