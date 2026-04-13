<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Register extends Component
{
    public string $first_name = '';
    public string $last_name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public bool $accepts_terms = false;

    protected function rules(): array
    {
        return [
            'first_name'    => ['required', 'string', 'max:100'],
            'last_name'     => ['required', 'string', 'max:100'],
            'email'         => ['required', 'email', 'unique:users,email'],
            'password'      => ['required', 'min:8', 'confirmed'],
            'accepts_terms' => ['accepted'],
        ];
    }

    protected function messages(): array
    {
        return [
            'accepts_terms.accepted' => 'Vous devez accepter les CGU et la Politique de confidentialité pour créer un compte.',
        ];
    }

    public function register(): void
    {
        $validated = $this->validate();

        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name'  => $validated['last_name'],
            'email'      => $validated['email'],
            'password'   => bcrypt($validated['password']),
        ]);

        $user->assignRole('member');

        event(new Registered($user));

        Auth::login($user);

        $this->redirect(route('verification.notice'), navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.register')
            ->layout('layouts.guest');
    }
}
