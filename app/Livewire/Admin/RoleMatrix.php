<?php

namespace App\Livewire\Admin;

use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Stub — full implementation in ② Task 10.
 */
class RoleMatrix extends Component
{
    public function mount(): void
    {
        $this->authorize('roles.view');
    }

    #[Layout('layouts.admin')]
    public function render()
    {
        return <<<'HTML'
        <div>
            <h1 class="text-2xl font-bold text-[#111827]">Rôles &amp; permissions</h1>
            <p class="text-sm text-gray-400 mt-2">Implémentation complète en cours (Task 10).</p>
        </div>
        HTML;
    }
}
