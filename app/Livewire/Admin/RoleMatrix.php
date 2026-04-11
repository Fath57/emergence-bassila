<?php

namespace App\Livewire\Admin;

use Livewire\Attributes\Layout;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleMatrix extends Component
{
    public function mount(): void
    {
        $this->authorize('roles.view');
    }

    #[Layout('layouts.admin')]
    public function render()
    {
        // Group permissions by their domain (first dot-separated segment).
        $grouped = Permission::query()
            ->orderBy('name')
            ->get()
            ->groupBy(fn (Permission $p) => explode('.', $p->name)[0]);

        // Roles in a fixed display order: admin → editor → member.
        $roles = Role::with('permissions')
            ->orderByRaw("
                CASE name
                    WHEN 'admin'  THEN 1
                    WHEN 'editor' THEN 2
                    WHEN 'member' THEN 3
                    ELSE 4
                END
            ")
            ->get();

        return view('livewire.admin.role-matrix', [
            'grouped' => $grouped,
            'roles'   => $roles,
        ]);
    }
}
