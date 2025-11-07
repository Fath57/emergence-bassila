<?php

namespace App\Policies;

use App\User;
use App\News;

class NewsPolicy
{
    /**
     * Determine if the user can create news
     */
    public function create(User $user)
    {
        // Tous les utilisateurs actifs peuvent créer des actualités
        return $user->status === 'active';
    }

    /**
     * Determine if the user can update the news
     */
    public function update(User $user, News $news)
    {
        return $user->id === $news->user_id || $user->role === 'admin' || $user->role === 'moderator';
    }

    /**
     * Determine if the user can delete the news
     */
    public function delete(User $user, News $news)
    {
        return $user->id === $news->user_id || $user->role === 'admin' || $user->role === 'moderator';
    }
}
