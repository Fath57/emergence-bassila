<?php

namespace App\Policies;

use App\Models\BlogPost;
use App\Models\User;

class BlogPostPolicy
{
    public function create(User $user): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return setting('blog.public_creation', true);
    }

    public function update(User $user, BlogPost $post): bool
    {
        return $user->id === $post->user_id || $user->hasRole('admin');
    }

    public function delete(User $user, BlogPost $post): bool
    {
        return $user->id === $post->user_id || $user->hasRole('admin');
    }
}
