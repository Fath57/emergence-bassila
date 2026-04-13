<?php

namespace App\Livewire\Account;

use App\Mail\AccountDeletionCancelled;
use App\Models\AccountDeletionRequest;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Layout;
use Livewire\Component;

class CancelDeletion extends Component
{
    public ?AccountDeletionRequest $request = null;

    public function mount(): void
    {
        $user = auth()->user();
        abort_unless($user, 403);

        $this->request = $user->deletionRequest()
            ->whereIn('status', ['requested', 'confirmed'])
            ->first();

        abort_unless($this->request, 404);
    }

    public function cancel()
    {
        $user = auth()->user();
        $this->request->cancel($user);
        $user->update(['is_active' => true]);

        Mail::to($user->email)->queue(new AccountDeletionCancelled($this->request));

        session()->flash('status', 'Suppression annulée. Bienvenue à nouveau.');

        return redirect('/');
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.account.cancel-deletion');
    }
}
