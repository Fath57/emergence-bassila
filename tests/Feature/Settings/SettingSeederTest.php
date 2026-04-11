<?php

use App\Models\Setting;
use Database\Seeders\SettingSeeder;

it('seeds 7 settings on a fresh database', function () {
    $this->seed(SettingSeeder::class);

    expect(Setting::count())->toBe(7);

    $expectedKeys = [
        'site.registration_open',
        'site.maintenance_mode',
        'site.maintenance_message',
        'blog.public_creation',
        'blog.require_moderation',
        'comments.enabled',
        'comments.require_moderation',
    ];

    foreach ($expectedKeys as $key) {
        expect(Setting::where('key', $key)->exists())->toBeTrue("Missing setting: {$key}");
    }
});

it('preserves admin-edited values on re-run while re-syncing metadata', function () {
    $this->seed(SettingSeeder::class);

    $setting = Setting::where('key', 'blog.public_creation')->first();
    $setting->update([
        'value' => '0',
        'label' => 'LABEL HACKED BY ADMIN',
    ]);

    $this->seed(SettingSeeder::class);

    $reloaded = Setting::where('key', 'blog.public_creation')->first();

    expect($reloaded->value)->toBe('0')
        ->and($reloaded->casted_value)->toBe(false);

    expect($reloaded->label)->toBe("Création d'articles par les membres");
});

it('seeds the string-typed maintenance message with its default text', function () {
    $this->seed(SettingSeeder::class);

    $msg = Setting::where('key', 'site.maintenance_message')->first();

    expect($msg->type)->toBe('string')
        ->and($msg->value)->toContain('temporairement indisponible');
});
