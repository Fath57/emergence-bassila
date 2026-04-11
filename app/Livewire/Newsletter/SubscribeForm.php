<?php

namespace App\Livewire\Newsletter;

use App\Models\NewsletterSubscriber;
use Livewire\Component;

class SubscribeForm extends Component
{
    public string $email = '';
    public string $firstName = '';
    public bool $pending = false;

    protected function rules(): array
    {
        return [
            'email'     => ['required', 'email'],
            'firstName' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function subscribe(): void
    {
        $this->validate();

        $email = strtolower(trim($this->email));

        $existing = NewsletterSubscriber::where('email', $email)->first();

        if ($existing) {
            if ($existing->isConfirmed()) {
                $this->addError('email', 'Cette adresse est déjà inscrite à notre newsletter.');
                return;
            }

            // Unsubscribed or pending: re-enable
            $existing->update([
                'first_name'      => $this->firstName ?: $existing->first_name,
                'unsubscribed_at' => null,
                'confirmed_at'    => now(),
            ]);
        } else {
            NewsletterSubscriber::create([
                'email'        => $email,
                'first_name'   => $this->firstName ?: null,
                'source'       => 'public_form',
                'confirmed_at' => now(),
            ]);
        }

        $this->email     = '';
        $this->firstName = '';
        $this->pending   = true;
    }

    public function render()
    {
        return view('livewire.newsletter.subscribe-form');
    }
}
