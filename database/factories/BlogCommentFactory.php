<?php

namespace Database\Factories;

use App\Models\BlogComment;
use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BlogCommentFactory extends Factory
{
    protected $model = BlogComment::class;

    public function definition(): array
    {
        return [
            'blog_post_id' => BlogPost::factory()->published(),
            'user_id'      => User::factory(),
            'content'      => $this->faker->paragraph(),
            'moderated_at' => null,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn () => [
            'moderated_at' => now(),
        ]);
    }
}
