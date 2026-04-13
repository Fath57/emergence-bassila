<?php

namespace App\Livewire\Account;

use App\Mail\AccountDeletionConfirmed;
use App\Models\AccountDeletionRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Layout;
use Livewire\Component;

class ConfirmDeletion extends Component
{
    public AccountDeletionRequest $request;
    public string $state;

    public function mount(string $token): void
    {
        $req = AccountDeletionRequest::where('confirmation_token', $token)->first();

        abort_unless($req, 404);

        if ($req->status !== 'requested') {
            $this->request = $req;
            $this->state = 'already-handled';
            return;
        }

        if ($req->isTokenExpired()) {
            $req->cancel($req->user, 'token-expired');
            $this->request = $req;
            $this->state = 'expired';
            return;
        }

        $req->confirm();
        $req->user->update(['is_active' => false]);

        if (Auth::check() && Auth::id() === $req->user_id) {
            Auth::logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
        }

        Mail::to($req->user->email)->queue(new AccountDeletionConfirmed($req));

        $this->request = $req;
        $this->state = 'confirmed';
    }

    #[Layout('layouts.guest')]
    public function render()
    {
        return view('livewire.account.confirm-deletion');
    }
}
