<?php

namespace App\Policies;

use App\Models\BlogPost;
use App\Models\User;

class BlogPostPolicy
{
    public function create(User $user): bool
    {
        if ($user->can('admin.access')) {
            return $user->can('posts.create');
        }

        return $user->can('posts.create') && setting('blog.public_creation', true);
    }

    public function update(User $user, BlogPost $post): bool
    {
        return $user->id === $post->user_id
            ? $user->can('posts.edit.own')
            : $user->can('posts.edit.any');
    }

    public function delete(User $user, BlogPost $post): bool
    {
        return $user->id === $post->user_id || $user->can('posts.delete.any');
    }

    public function publish(User $user, BlogPost $post): bool
    {
        return $user->id === $post->user_id
            ? $user->can('posts.publish.own')
            : $user->can('posts.edit.any');
    }
}
