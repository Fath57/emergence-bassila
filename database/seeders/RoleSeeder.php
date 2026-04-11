<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    /**
     * Seed roles + baseline permissions.
     *
     * Current scope: the 2 permissions needed by sub-project ① Site Settings
     * (`admin.access` for the admin panel gate, `settings.manage` for the
     * /admin/parametres page).
     *
     * The full 17-permission set from sub-project ② RBAC will extend this
     * seeder at that sub-project's implementation time. Until then, the
     * `editor` and `moderator` roles remain unchanged and the other legacy
     * `hasRole('admin')` checks elsewhere in the codebase stay in place.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $roles = ['admin', 'moderator', 'user'];
        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        // Baseline permissions needed by sub-project ① Site Settings
        $permissions = [
            'admin.access',       // access to /admin/* routes
            'settings.manage',    // edit site settings at /admin/parametres
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
        }

        // Attach all baseline permissions to the admin role (idempotent).
        $admin = Role::where('name', 'admin')->first();
        $admin->givePermissionTo($permissions);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
