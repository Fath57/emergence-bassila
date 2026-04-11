<?php

namespace Database\Factories;

use App\Models\Profile;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProfileFactory extends Factory
{
    protected $model = Profile::class;

    public function definition(): array
    {
        return [
            'user_id'    => User::factory(),
            'first_name' => $this->faker->firstName(),
            'last_name'  => $this->faker->lastName(),
            'job_title'  => $this->faker->jobTitle(),
            'company'    => $this->faker->company(),
            'country'    => $this->faker->country(),
            'city'       => $this->faker->city(),
            'sector_id'  => Sector::factory(),
            'avatar_url' => null,
            'bio'        => $this->faker->sentence(),
            'is_verified' => false,
        ];
    }

    public function verified(): static
    {
        return $this->state(fn () => [
            'is_verified' => true,
            'verified_at' => now(),
        ]);
    }
}
