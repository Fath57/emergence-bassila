<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class VerifyEmail extends Component
{
    public ?string $successMessage = null;

    public function resend(): void
    {
        $user = Auth::user();

        if ($user && ! $user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
            $this->successMessage = 'Un nouveau lien de vérification a été envoyé à votre adresse email.';
        }
    }

    public function logout(): void
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();

        $this->redirect(route('home'), navigate: true);
    }

    #[Layout('layouts.guest')]
    public function render()
    {
        return view('livewire.auth.verify-email');
    }
}
