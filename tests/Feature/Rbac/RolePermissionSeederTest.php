<?php

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

it('creates the 3 target roles', function () {
    expect(Role::where('name', 'admin')->exists())->toBeTrue()
        ->and(Role::where('name', 'editor')->exists())->toBeTrue()
        ->and(Role::where('name', 'member')->exists())->toBeTrue();
});

it('does not leave legacy user or moderator roles', function () {
    expect(Role::where('name', 'user')->exists())->toBeFalse()
        ->and(Role::where('name', 'moderator')->exists())->toBeFalse();
});

it('creates the 17 permissions across 8 domains', function () {
    $expected = [
        'posts.create', 'posts.publish.own',
        'posts.edit.own', 'posts.edit.any', 'posts.delete.any',
        'comments.moderate',
        'profiles.moderate',
        'users.view', 'users.invite', 'users.edit', 'users.assign-role',
        'roles.view',
        'settings.manage',
        'newsletter.subscribers.view',
        'newsletter.campaigns.compose',
        'newsletter.campaigns.send',
        'admin.access',
    ];

    expect(Permission::count())->toBeGreaterThanOrEqual(17);

    foreach ($expected as $name) {
        expect(Permission::where('name', $name)->exists())
            ->toBeTrue("Missing permission: {$name}");
    }
});

it('gives the admin role all 17 permissions', function () {
    $admin = Role::where('name', 'admin')->first();
    expect($admin->permissions->count())->toBeGreaterThanOrEqual(17);
});

it('gives the editor role a minimal publish-own set plus admin.access', function () {
    $editor = Role::where('name', 'editor')->first();
    $names = $editor->permissions->pluck('name')->sort()->values()->all();

    expect($names)->toBe([
        'admin.access',
        'posts.create',
        'posts.edit.own',
        'posts.publish.own',
    ]);
});

it('gives the member role only posts.create and posts.edit.own', function () {
    $member = Role::where('name', 'member')->first();
    $names = $member->permissions->pluck('name')->sort()->values()->all();

    expect($names)->toBe([
        'posts.create',
        'posts.edit.own',
    ]);
});
