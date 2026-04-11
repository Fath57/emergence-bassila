<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Spatie\Activitylog\Models\Activity;

class EditUser extends Component
{
    public User $user;

    public string $first_name = '';
    public string $last_name = '';
    public string $email = '';

    public function mount(User $user): void
    {
        $this->authorize('users.edit');

        $this->user       = $user;
        $this->first_name = $user->first_name;
        $this->last_name  = $user->last_name;
        $this->email      = $user->email;
    }

    public function save(): void
    {
        $this->authorize('users.edit');

        $this->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name'  => ['required', 'string', 'max:100'],
            'email'      => ['required', 'email', 'unique:users,email,' . $this->user->id],
        ]);

        $this->user->update([
            'first_name' => $this->first_name,
            'last_name'  => $this->last_name,
            'email'      => $this->email,
        ]);

        session()->flash('success', 'Informations mises à jour.');
    }

    public function changeRole(string $newRole): void
    {
        $this->authorize('users.assign-role');

        if (! in_array($newRole, ['admin', 'editor', 'member'], true)) {
            return;
        }

        // Anti-lockout guard
        if ($this->user->hasRole('admin')
            && $newRole !== 'admin'
            && User::isLastActiveAdmin($this->user)) {
            session()->flash('error', 'Impossible : au moins un administrateur actif doit rester.');
            return;
        }

        $oldRole = $this->user->roles->first()?->name;
        $this->user->syncRoles([$newRole]);

        activity('users')
            ->performedOn($this->user->fresh())
            ->causedBy(Auth::user())
            ->withProperties(['old' => $oldRole, 'new' => $newRole])
            ->log('role_changed');

        session()->flash('success', 'Rôle mis à jour.');
    }

    public function toggleActive(): void
    {
        $this->authorize('users.edit');

        // Anti-lockout guard on deactivation
        if ($this->user->is_active && User::isLastActiveAdmin($this->user)) {
            session()->flash('error', 'Impossible : au moins un administrateur actif doit rester.');
            return;
        }

        $this->user->update(['is_active' => ! $this->user->is_active]);

        session()->flash('success', $this->user->is_active ? 'Compte réactivé.' : 'Compte désactivé.');
    }

    #[Layout('layouts.admin')]
    public function render()
    {
        $history = Activity::query()
            ->where('subject_type', User::class)
            ->where('subject_id', $this->user->id)
            ->with('causer')
            ->latest('id')
            ->limit(20)
            ->get();

        $currentRole = $this->user->roles->first()?->name ?? 'member';
        $isLastActiveAdmin = User::isLastActiveAdmin($this->user);

        return view('livewire.admin.edit-user', [
            'history'           => $history,
            'currentRole'       => $currentRole,
            'isLastActiveAdmin' => $isLastActiveAdmin,
        ]);
    }
}
