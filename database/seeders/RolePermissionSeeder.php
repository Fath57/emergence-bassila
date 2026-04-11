<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Full RBAC seeder for sub-project ② RBAC.
 *
 * - Creates 17 permissions across 8 domains
 * - Creates 3 target roles: admin, editor, member
 * - Syncs each role's permission set (idempotent via syncPermissions)
 * - Migrates any legacy `user` / `moderator` role assignments to
 *   `member` / `editor` respectively, then deletes the legacy role rows.
 * - Forgets Spatie's permission cache on entry and exit to prevent
 *   stale lookups.
 */
class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->seedPermissions();
        $this->seedRolesWithPermissions();
        $this->migrateLegacyRoleNames();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function seedPermissions(): void
    {
        $permissions = [
            // Posts
            'posts.create', 'posts.publish.own',
            'posts.edit.own', 'posts.edit.any', 'posts.delete.any',
            // Comments
            'comments.moderate',
            // Profiles
            'profiles.moderate',
            // Users
            'users.view', 'users.invite', 'users.edit', 'users.assign-role',
            // Roles
            'roles.view',
            // Settings (already seeded by the baseline RoleSeeder, re-added here for completeness)
            'settings.manage',
            // Newsletter (consumed by sub-project ④)
            'newsletter.subscribers.view',
            'newsletter.campaigns.compose',
            'newsletter.campaigns.send',
            // Admin panel gate (already seeded by the baseline RoleSeeder)
            'admin.access',
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }
    }

    private function seedRolesWithPermissions(): void
    {
        $definitions = [
            'admin'  => Permission::pluck('name')->all(),
            'editor' => [
                'posts.create',
                'posts.publish.own',
                'posts.edit.own',
                'admin.access',
            ],
            'member' => [
                'posts.create',
                'posts.edit.own',
            ],
        ];

        foreach ($definitions as $roleName => $permissionNames) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($permissionNames);
        }
    }

    private function migrateLegacyRoleNames(): void
    {
        $legacyMap = [
            'user'      => 'member',
            'moderator' => 'editor',
        ];

        foreach ($legacyMap as $old => $new) {
            $oldRole = Role::where('name', $old)->first();
            $newRole = Role::where('name', $new)->first();

            if ($oldRole && $newRole) {
                // Re-point any user who had the old role to the new role
                DB::table('model_has_roles')
                    ->where('role_id', $oldRole->id)
                    ->update(['role_id' => $newRole->id]);

                $oldRole->delete();
            }
        }
    }
}
