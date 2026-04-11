<?php

use App\Livewire\Admin\EditUser;
use App\Models\User;
use Livewire\Livewire;

it('cannot demote the last active admin', function () {
    $admin = User::factory()->create(['email_verified_at' => now(), 'is_active' => true]);
    $admin->assignRole('admin');

    Livewire::actingAs($admin)
        ->test(EditUser::class, ['user' => $admin])
        ->call('changeRole', 'member');

    // State preserved — demotion blocked by the anti-lockout guard
    expect($admin->fresh()->hasRole('admin'))->toBeTrue()
        ->and($admin->fresh()->hasRole('member'))->toBeFalse();
});

it('cannot deactivate the last active admin', function () {
    $admin = User::factory()->create(['email_verified_at' => now(), 'is_active' => true]);
    $admin->assignRole('admin');

    Livewire::actingAs($admin)
        ->test(EditUser::class, ['user' => $admin])
        ->call('toggleActive');

    // State preserved — deactivation blocked by the anti-lockout guard
    expect($admin->fresh()->is_active)->toBeTrue();
});

it('can demote an admin when another active admin exists', function () {
    $adminA = User::factory()->create(['email_verified_at' => now(), 'is_active' => true]);
    $adminA->assignRole('admin');
    $adminB = User::factory()->create(['email_verified_at' => now(), 'is_active' => true]);
    $adminB->assignRole('admin');

    Livewire::actingAs($adminA)
        ->test(EditUser::class, ['user' => $adminB])
        ->call('changeRole', 'member');

    expect($adminB->fresh()->hasRole('admin'))->toBeFalse()
        ->and($adminB->fresh()->hasRole('member'))->toBeTrue();
});

it('can deactivate an admin when another active admin exists', function () {
    $adminA = User::factory()->create(['email_verified_at' => now(), 'is_active' => true]);
    $adminA->assignRole('admin');
    $adminB = User::factory()->create(['email_verified_at' => now(), 'is_active' => true]);
    $adminB->assignRole('admin');

    Livewire::actingAs($adminA)
        ->test(EditUser::class, ['user' => $adminB])
        ->call('toggleActive');

    expect($adminB->fresh()->is_active)->toBeFalse();
});

it('admin can promote a member to editor', function () {
    $admin = User::factory()->create(['email_verified_at' => now(), 'is_active' => true]);
    $admin->assignRole('admin');
    $member = User::factory()->create(['email_verified_at' => now(), 'is_active' => true]);
    $member->assignRole('member');

    Livewire::actingAs($admin)
        ->test(EditUser::class, ['user' => $member])
        ->call('changeRole', 'editor');

    expect($member->fresh()->hasRole('editor'))->toBeTrue()
        ->and($member->fresh()->hasRole('member'))->toBeFalse();
});

it('admin can update a user basic info', function () {
    $admin = User::factory()->create(['email_verified_at' => now()]);
    $admin->assignRole('admin');
    $user = User::factory()->create(['email_verified_at' => now(), 'first_name' => 'Old', 'last_name' => 'Name']);
    $user->assignRole('member');

    Livewire::actingAs($admin)
        ->test(EditUser::class, ['user' => $user])
        ->set('first_name', 'New')
        ->set('last_name', 'Fullname')
        ->call('save')
        ->assertHasNoErrors();

    $fresh = $user->fresh();
    expect($fresh->first_name)->toBe('New')
        ->and($fresh->last_name)->toBe('Fullname');
});
