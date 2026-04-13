<?php

namespace App\Livewire\Admin;

use App\Mail\AccountDeletionCancelled;
use App\Models\AccountDeletionRequest;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class DeletionRequests extends Component
{
    use WithPagination;

    public string $statusFilter = 'all';
    public array $cancelReason = [];

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function cancel(int $id): void
    {
        $req = AccountDeletionRequest::findOrFail($id);
        $reason = $this->cancelReason[$id] ?? null;
        $req->cancel(auth()->user(), $reason);
        $req->user?->update(['is_active' => true]);

        if ($req->user) {
            Mail::to($req->user->email)->queue(new AccountDeletionCancelled($req));
        }

        session()->flash('status', 'Demande annulée.');
    }

    public function forcePurge(int $id): void
    {
        $req = AccountDeletionRequest::findOrFail($id);

        if ($req->status === 'requested') {
            $req->confirm();
        }

        $req->purge();

        session()->flash('status', 'Compte purgé.');
    }

    #[Layout('layouts.admin')]
    public function render()
    {
        $query = AccountDeletionRequest::with('user', 'canceller')->latest('requested_at');

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        return view('livewire.admin.deletion-requests', [
            'requests' => $query->paginate(20),
        ]);
    }
}
