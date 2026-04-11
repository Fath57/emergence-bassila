<?php

namespace App\Livewire\Admin;

use App\Mail\InvitationMail;
use App\Models\User;
use App\Models\UserInvitation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

class InviteUser extends Component
{
    public string $email = '';
    public string $first_name = '';
    public string $last_name = '';
    /** @var 'admin'|'editor'|'member' */
    public string $role = 'member';
    public string $message = '';

    public function mount(): void
    {
        $this->authorize('users.invite');
    }

    protected function rules(): array
    {
        return [
            'email'      => ['required', 'email', 'max:255'],
            'first_name' => ['nullable', 'string', 'max:100'],
            'last_name'  => ['nullable', 'string', 'max:100'],
            'role'       => ['required', 'in:admin,editor,member'],
            'message'    => ['nullable', 'string', 'max:500'],
        ];
    }

    public function send(): void
    {
        $this->authorize('users.invite');

        $this->validate();

        if (User::where('email', $this->email)->exists()) {
            $this->addError('email', 'Ce membre fait déjà partie de la plateforme.');
            return;
        }

        if (UserInvitation::where('email', $this->email)->whereNull('accepted_at')->exists()) {
            $this->addError('email', 'Une invitation est déjà en attente pour cet email. Vous pouvez la relancer depuis la liste.');
            return;
        }

        $invitation = UserInvitation::create([
            'email'       => $this->email,
            'first_name'  => $this->first_name ?: null,
            'last_name'   => $this->last_name ?: null,
            'role'        => $this->role,
            'message'     => $this->message ?: null,
            'token'       => Str::random(64),
            'invited_by'  => Auth::id(),
            'expires_at'  => now()->addDays(7),
        ]);

        Mail::to($invitation->email)->queue(new InvitationMail($invitation));

        session()->flash('success', "Invitation envoyée à {$invitation->email}.");

        $this->redirect(route('admin.users', ['tab' => 'pending']), navigate: true);
    }

    #[Layout('layouts.admin')]
    public function render()
    {
        return view('livewire.admin.invite-user');
    }
}
