<?php

namespace App\Policies;

use App\Opportunity;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OpportunityPolicy
{
    use HandlesAuthorization;

    /**
     * Déterminer si l'utilisateur peut créer des opportunités
     */
    public function create(User $user)
    {
        // Tous les utilisateurs actifs peuvent créer des opportunités
        return $user->status === 'active';
    }

    /**
     * Déterminer si l'utilisateur peut modifier l'opportunité
     */
    public function update(User $user, Opportunity $opportunity)
    {
        // L'auteur ou un admin/modérateur peut modifier
        return $user->id === $opportunity->user_id
            || $user->role === 'admin'
            || $user->role === 'moderator';
    }

    /**
     * Déterminer si l'utilisateur peut supprimer l'opportunité
     */
    public function delete(User $user, Opportunity $opportunity)
    {
        // L'auteur ou un admin/modérateur peut supprimer
        return $user->id === $opportunity->user_id
            || $user->role === 'admin'
            || $user->role === 'moderator';
    }
}
