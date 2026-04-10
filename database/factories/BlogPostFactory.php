<?php

namespace Database\Factories;

use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BlogPostFactory extends Factory
{
    protected $model = BlogPost::class;

    public function definition(): array
    {
        $title = $this->faker->sentence(5);

        return [
            'user_id'     => User::factory(),
            'title'       => $title,
            'slug'        => Str::slug($title) . '-' . $this->faker->unique()->numberBetween(1, 9999),
            'content'     => $this->faker->paragraphs(3, true),
            'excerpt'     => $this->faker->sentence(),
            'status'      => 'draft',
            'published_at' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => [
            'status'       => 'published',
            'published_at' => now()->subHour(),
        ]);
    }
}
