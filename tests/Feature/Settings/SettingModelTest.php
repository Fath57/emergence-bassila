<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

it('casts the value according to the type column', function () {
    $bool = Setting::create([
        'key'         => 'test.bool',
        'value'       => '1',
        'type'        => 'bool',
        'group'       => 'test',
        'label'       => 'Bool flag',
        'description' => null,
        'sort_order'  => 0,
    ]);

    $string = Setting::create([
        'key'         => 'test.string',
        'value'       => 'hello world',
        'type'        => 'string',
        'group'       => 'test',
        'label'       => 'String setting',
        'description' => null,
        'sort_order'  => 0,
    ]);

    expect($bool->casted_value)->toBe(true)
        ->and($string->casted_value)->toBe('hello world');

    $bool->update(['value' => '0']);
    expect($bool->fresh()->casted_value)->toBe(false);
});

it('invalidates the settings.all cache on save and delete', function () {
    Cache::put('settings.all', 'pre-existing', now()->addHour());
    expect(Cache::get('settings.all'))->toBe('pre-existing');

    $setting = Setting::create([
        'key'         => 'test.cache',
        'value'       => '1',
        'type'        => 'bool',
        'group'       => 'test',
        'label'       => 'Cache flag',
        'description' => null,
        'sort_order'  => 0,
    ]);

    expect(Cache::get('settings.all'))->toBeNull();

    Cache::put('settings.all', 'post-create-repopulated', now()->addHour());
    $setting->delete();
    expect(Cache::get('settings.all'))->toBeNull();
});
