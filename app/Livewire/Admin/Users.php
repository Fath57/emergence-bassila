<?php

namespace App\Livewire\Admin;

use App\Mail\InvitationMail;
use App\Models\User;
use App\Models\UserInvitation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Users extends Component
{
    use WithPagination;

    /** @var 'active'|'pending' */
    #[Url]
    public string $tab = 'active';

    #[Url(as: 'q', keep: false)]
    public string $search = '';

    /** @var 'all'|'admin'|'editor'|'member' */
    #[Url]
    public string $roleFilter = 'all';

    /** @var 'all'|'active'|'inactive'|'unverified' */
    #[Url]
    public string $statusFilter = 'all';

    public function mount(): void
    {
        $this->authorize('users.view');
    }

    public function updatingTab(): void { $this->resetPage(); }
    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingRoleFilter(): void { $this->resetPage(); }
    public function updatingStatusFilter(): void { $this->resetPage(); }

    public function resend(int $invitationId): void
    {
        $this->authorize('users.invite');

        $invitation = UserInvitation::findOrFail($invitationId);

        if ($invitation->isAccepted()) {
            session()->flash('error', 'Cette invitation a déjà été acceptée.');
            return;
        }

        $invitation->update([
            'token'      => Str::random(64),
            'expires_at' => now()->addDays(7),
        ]);

        Mail::to($invitation->email)->queue(new InvitationMail($invitation));

        session()->flash('success', "Invitation renvoyée à {$invitation->email}.");
    }

    public function cancelInvitation(int $invitationId): void
    {
        $this->authorize('users.invite');

        $invitation = UserInvitation::findOrFail($invitationId);

        if ($invitation->isAccepted()) {
            session()->flash('error', 'Cette invitation a déjà été acceptée, vous ne pouvez pas la supprimer.');
            return;
        }

        $email = $invitation->email;
        $invitation->delete();

        session()->flash('success', "Invitation pour {$email} annulée.");
    }

    #[Layout('layouts.admin')]
    public function render()
    {
        if ($this->tab === 'pending') {
            $items = UserInvitation::query()
                ->whereNull('accepted_at')
                ->with('invitedBy')
                ->latest('created_at')
                ->paginate(15);
        } else {
            $items = User::query()
                ->with('roles')
                ->when($this->search !== '', function (Builder $q) {
                    $term = '%' . mb_strtolower($this->search) . '%';
                    $q->where(function (Builder $inner) use ($term) {
                        $inner->whereRaw('LOWER(email) LIKE ?', [$term])
                              ->orWhereRaw('LOWER(name) LIKE ?', [$term]);
                    });
                })
                ->when($this->roleFilter !== 'all',
                    fn (Builder $q) => $q->role($this->roleFilter))
                ->when($this->statusFilter === 'active',
                    fn (Builder $q) => $q->where('is_active', true)->whereNotNull('email_verified_at'))
                ->when($this->statusFilter === 'inactive',
                    fn (Builder $q) => $q->where('is_active', false))
                ->when($this->statusFilter === 'unverified',
                    fn (Builder $q) => $q->whereNull('email_verified_at'))
                ->orderByDesc('created_at')
                ->paginate(15);
        }

        $activeCount  = User::count();
        $pendingCount = UserInvitation::whereNull('accepted_at')->count();

        return view('livewire.admin.users', [
            'items'        => $items,
            'activeCount'  => $activeCount,
            'pendingCount' => $pendingCount,
        ]);
    }
}
