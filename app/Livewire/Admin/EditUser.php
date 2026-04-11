<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Stub — full implementation in ② Task 9.
 */
class EditUser extends Component
{
    public User $user;

    public function mount(User $user): void
    {
        $this->authorize('users.edit');
        $this->user = $user;
    }

    #[Layout('layouts.admin')]
    public function render()
    {
        return <<<'HTML'
        <div>
            <h1 class="text-2xl font-bold text-[#111827]">Éditer un utilisateur</h1>
            <p class="text-sm text-gray-400 mt-2">Implémentation complète en cours (Task 9).</p>
        </div>
        HTML;
    }
}
