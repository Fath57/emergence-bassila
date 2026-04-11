<?php

namespace App\Livewire\Admin;

use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Stub — full implementation in ② Task 8.
 *
 * Allows the route `admin.users.invite` to resolve so the Users list page
 * can link to it. Replaced with the real invite form in the next task.
 */
class InviteUser extends Component
{
    public function mount(): void
    {
        $this->authorize('users.invite');
    }

    #[Layout('layouts.admin')]
    public function render()
    {
        return <<<'HTML'
        <div>
            <h1 class="text-2xl font-bold text-[#111827]">Inviter un utilisateur</h1>
            <p class="text-sm text-gray-400 mt-2">Implémentation complète en cours (Task 8).</p>
        </div>
        HTML;
    }
}
