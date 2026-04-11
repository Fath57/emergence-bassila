<?php

use App\Models\User;

it('isLastActiveAdmin returns true for the sole admin', function () {
    $admin = User::factory()->create(['email_verified_at' => now(), 'is_active' => true]);
    $admin->assignRole('admin');

    expect(User::isLastActiveAdmin($admin))->toBeTrue();
});

it('isLastActiveAdmin returns false when two active admins exist', function () {
    $a = User::factory()->create(['email_verified_at' => now(), 'is_active' => true]);
    $a->assignRole('admin');
    $b = User::factory()->create(['email_verified_at' => now(), 'is_active' => true]);
    $b->assignRole('admin');

    expect(User::isLastActiveAdmin($a))->toBeFalse()
        ->and(User::isLastActiveAdmin($b))->toBeFalse();
});

it('isLastActiveAdmin returns false for non-admin users', function () {
    $user = User::factory()->create(['email_verified_at' => now(), 'is_active' => true]);
    $user->assignRole('user');

    expect(User::isLastActiveAdmin($user))->toBeFalse();
});

it('isLastActiveAdmin returns false for an inactive admin', function () {
    $admin = User::factory()->create(['email_verified_at' => now(), 'is_active' => false]);
    $admin->assignRole('admin');

    expect(User::isLastActiveAdmin($admin))->toBeFalse();
});
