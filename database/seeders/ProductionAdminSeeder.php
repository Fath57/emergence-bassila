<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * One-off seeder to create the production admin account.
 *
 * Reads these env vars (all required):
 *   - ADMIN_EMAIL
 *   - ADMIN_PASSWORD
 *   - ADMIN_FIRST_NAME
 *   - ADMIN_LAST_NAME
 *
 * Usage:
 *   dokku config:set --no-restart emergence-bassila \
 *     ADMIN_EMAIL=... ADMIN_PASSWORD=... ADMIN_FIRST_NAME=... ADMIN_LAST_NAME=...
 *   dokku run emergence-bassila php artisan db:seed --class=ProductionAdminSeeder --force
 *   dokku config:unset emergence-bassila ADMIN_EMAIL ADMIN_PASSWORD ADMIN_FIRST_NAME ADMIN_LAST_NAME
 *
 * This file is deleted in a follow-up commit once the admin is created.
 */
class ProductionAdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_EMAIL');
        $password = env('ADMIN_PASSWORD');
        $firstName = env('ADMIN_FIRST_NAME');
        $lastName = env('ADMIN_LAST_NAME');

        if (! $email || ! $password || ! $firstName || ! $lastName) {
            throw new \RuntimeException(
                'ProductionAdminSeeder requires ADMIN_EMAIL, ADMIN_PASSWORD, '.
                'ADMIN_FIRST_NAME, ADMIN_LAST_NAME env vars to be set.'
            );
        }

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );

        $user->assignRole('admin');

        $this->command->info("Admin ready: id={$user->id} email={$user->email} roles=".$user->roles->pluck('name')->implode(','));
    }
}
