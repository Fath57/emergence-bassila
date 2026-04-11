<?php

namespace App\Livewire\Auth;

use App\Models\User;
use App\Models\UserInvitation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

class AcceptInvitation extends Component
{
    public ?UserInvitation $invitation = null;

    public string $first_name = '';
    public string $last_name = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function mount(string $token): void
    {
        $this->invitation = UserInvitation::where('token', $token)->first();

        if (! $this->invitation) {
            abort(404);
        }

        if ($this->invitation->isAccepted()) {
            session()->flash('error', 'Cette invitation a déjà été utilisée.');
            $this->redirect(route('login'), navigate: true);
            return;
        }

        if ($this->invitation->isExpired()) {
            session()->flash('error', "Cette invitation a expiré. Demandez à l'administrateur de vous en envoyer une nouvelle.");
            $this->redirect(route('login'), navigate: true);
            return;
        }

        $this->first_name = (string) ($this->invitation->first_name ?? '');
        $this->last_name  = (string) ($this->invitation->last_name ?? '');
    }

    protected function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name'  => ['required', 'string', 'max:100'],
            'password'   => ['required', 'min:8', 'confirmed'],
        ];
    }

    public function accept(): void
    {
        $this->validate();

        $invitation = $this->invitation;

        $user = DB::transaction(function () use ($invitation) {
            $user = User::create([
                'first_name' => $this->first_name,
                'last_name'  => $this->last_name,
                'email'      => $invitation->email,
                'password'   => bcrypt($this->password),
                'is_active'  => true,
            ]);

            // email_verified_at is not in the fillable list, so mark it explicitly.
            // The invitation link in the email is de facto proof of email ownership.
            $user->markEmailAsVerified();

            $user->assignRole($invitation->role);

            $invitation->update(['accepted_at' => now()]);

            return $user;
        });

        Auth::login($user);
        session()->regenerate();
        session()->flash('success', "Bienvenue sur Bassila Émergence ! Complétez votre profil pour rejoindre l'annuaire.");

        $this->redirect(route('profile.create'), navigate: true);
    }

    #[Layout('layouts.guest')]
    public function render()
    {
        return view('livewire.auth.accept-invitation');
    }
}
