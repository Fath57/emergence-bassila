<?php

namespace Database\Factories;

use App\Models\AccountDeletionRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<AccountDeletionRequest>
 */
class AccountDeletionRequestFactory extends Factory
{
    protected $model = AccountDeletionRequest::class;

    public function definition(): array
    {
        return [
            'user_id'            => User::factory(),
            'status'             => 'requested',
            'confirmation_token' => Str::random(64),
            'requested_at'       => now(),
        ];
    }

    public function confirmed(): self
    {
        return $this->state(fn () => [
            'status'             => 'confirmed',
            'confirmation_token' => null,
            'confirmed_at'       => now(),
            'scheduled_purge_at' => now()->addDays(AccountDeletionRequest::GRACE_DAYS),
        ]);
    }

    public function cancelled(): self
    {
        return $this->state(fn () => [
            'status'       => 'cancelled',
            'cancelled_at' => now(),
            'cancelled_by' => User::factory(),
        ]);
    }

    public function purged(): self
    {
        return $this->state(fn () => [
            'status'    => 'purged',
            'purged_at' => now(),
        ]);
    }
}
