<?php

use App\Livewire\Admin\Settings as AdminSettings;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\SettingSeeder;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;

beforeEach(function () {
    $this->seed(SettingSeeder::class);
});

it('renders all 3 groups with their settings for an admin', function () {
    $admin = User::factory()->create(['email_verified_at' => now()]);
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->get('/admin/parametres')
        ->assertSuccessful()
        ->assertSee('Site')
        ->assertSee('Blog')
        ->assertSee('Commentaires')
        ->assertSee('Inscriptions ouvertes')
        ->assertSee('articles par les membres')    // partial match avoids apostrophe escaping
        ->assertSee('Commentaires activés');
});

it('forbids access to non-admin users', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->assignRole('member');

    $this->actingAs($user)
        ->get('/admin/parametres')
        ->assertForbidden();
});

it('updates a setting value and writes an activity log row on save', function () {
    $admin = User::factory()->create(['email_verified_at' => now()]);
    $admin->assignRole('admin');

    Livewire::actingAs($admin)
        ->test(AdminSettings::class)
        ->set('values.blog.public_creation', false)
        ->call('save')
        ->assertHasNoErrors();

    expect(Setting::where('key', 'blog.public_creation')->value('value'))->toBe('0');
    expect(Setting::where('key', 'blog.public_creation')->value('updated_by'))->toBe($admin->id);

    // Use id ordering (not created_at) — the seeder and the update can share the
    // same created_at second, which makes latest() on timestamps non-deterministic.
    $activity = Activity::inLog('settings')->latest('id')->first();
    expect($activity)->not->toBeNull()
        ->and($activity->description)->toBe('setting.updated')
        ->and($activity->properties['old']['value'] ?? null)->toBe('1')
        ->and($activity->properties['attributes']['value'] ?? null)->toBe('0');
});

it('updates the string-typed maintenance message', function () {
    $admin = User::factory()->create(['email_verified_at' => now()]);
    $admin->assignRole('admin');

    Livewire::actingAs($admin)
        ->test(AdminSettings::class)
        ->set('values.site.maintenance_message', 'Nouveau message de maintenance.')
        ->call('save');

    expect(Setting::where('key', 'site.maintenance_message')->value('value'))
        ->toBe('Nouveau message de maintenance.');
});

it('renders the activity history section when there are recent changes', function () {
    $admin = User::factory()->create(['email_verified_at' => now()]);
    $admin->assignRole('admin');

    Livewire::actingAs($admin)
        ->test(AdminSettings::class)
        ->set('values.comments.enabled', false)
        ->call('save');

    $this->actingAs($admin)
        ->get('/admin/parametres')
        ->assertSee('Historique des modifications')
        ->assertSee('comments.enabled');
});
