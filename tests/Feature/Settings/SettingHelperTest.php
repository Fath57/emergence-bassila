<?php

use App\Models\Setting;

it('returns the default when the key is missing', function () {
    $result = setting('nonexistent.key', 'fallback');
    expect($result)->toBe('fallback');
});

it('returns null as default when none is provided for a missing key', function () {
    $result = setting('also.nonexistent');
    expect($result)->toBeNull();
});

it('returns the casted value for an existing bool setting', function () {
    Setting::create([
        'key'         => 'flag.enabled',
        'value'       => '1',
        'type'        => 'bool',
        'group'       => 'test',
        'label'       => 'Flag',
        'description' => null,
        'sort_order'  => 0,
    ]);

    expect(setting('flag.enabled'))->toBe(true);
});

it('returns the casted value for an existing string setting', function () {
    Setting::create([
        'key'         => 'site.motto',
        'value'       => 'Connectés, engagés, inspirants.',
        'type'        => 'string',
        'group'       => 'test',
        'label'       => 'Motto',
        'description' => null,
        'sort_order'  => 0,
    ]);

    expect(setting('site.motto'))->toBe('Connectés, engagés, inspirants.');
});

it('reflects value changes on the next call after save', function () {
    $s = Setting::create([
        'key'         => 'flag.live',
        'value'       => '0',
        'type'        => 'bool',
        'group'       => 'test',
        'label'       => 'Live',
        'description' => null,
        'sort_order'  => 0,
    ]);

    expect(setting('flag.live'))->toBe(false);

    $s->update(['value' => '1']);

    expect(setting('flag.live'))->toBe(true);
});

it('serializes cache payload as plain array to survive cross-request deserialization', function () {
    // Regression guard: storing a full Eloquent Collection in the cache
    // causes "incomplete object" errors when the cache is read back from a
    // persisted store (database driver) across HTTP requests. The helper
    // must cache a plain array of primitives, not an Eloquent Collection.

    Setting::create([
        'key' => 'cross.request', 'value' => '1', 'type' => 'bool',
        'group' => 'test', 'sort_order' => 0,
        'label' => 'Cross-request flag', 'description' => null,
    ]);

    setting('cross.request'); // populate the cache

    $cached = \Illuminate\Support\Facades\Cache::get('settings.all');

    expect($cached)->toBeArray();
    expect($cached['cross.request'])->toBeArray()
        ->and($cached['cross.request']['value'])->toBe('1')
        ->and($cached['cross.request']['type'])->toBe('bool');

    // Simulate what happens cross-request: manually serialize+unserialize
    // the cached payload. Any Eloquent model inside would trigger the
    // incomplete-object error; a plain array round-trips cleanly.
    $serialized   = serialize($cached);
    $unserialized = unserialize($serialized);

    expect($unserialized)->toEqual($cached);
});
