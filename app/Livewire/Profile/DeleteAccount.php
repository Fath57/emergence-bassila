<?php

namespace App\Livewire\Profile;

use App\Mail\AccountDeletionRequested;
use App\Models\AccountDeletionRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class DeleteAccount extends Component
{
    public string $password = '';
    public bool $understood = false;
    public bool $submitted = false;

    public function submit(): void
    {
        $this->validate([
            'password'   => ['required', 'string'],
            'understood' => ['accepted'],
        ]);

        $user = auth()->user();

        if (! Hash::check($this->password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => 'Mot de passe incorrect.',
            ]);
        }

        if (User::isLastActiveAdmin($user)) {
            throw ValidationException::withMessages([
                'lockout' => 'Vous êtes le dernier administrateur actif. Retirez d\'abord ce rôle via un autre administrateur.',
            ]);
        }

        if ($user->hasPendingDeletion()) {
            throw ValidationException::withMessages([
                'password' => 'Une demande de suppression est déjà en cours.',
            ]);
        }

        $req = AccountDeletionRequest::startFor($user);

        Mail::to($user->email)->queue(new AccountDeletionRequested($req));

        $this->reset(['password', 'understood']);
        $this->submitted = true;
    }

    public function render()
    {
        return view('livewire.profile.delete-account');
    }
}
