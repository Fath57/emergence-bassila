<?php

namespace App\Livewire\Newsletter;

use App\Mail\NewsletterConfirmationMail;
use App\Models\NewsletterSubscriber;
use Illuminate\Support\Str;
use Livewire\Component;
use Illuminate\Support\Facades\Mail;

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

            // Pending or unsubscribed: refresh token and resend
            $token = Str::random(64);
            $existing->update([
                'confirmation_token' => $token,
                'first_name'         => $this->firstName ?: $existing->first_name,
                'unsubscribed_at'    => null,
            ]);

            Mail::to($email)->queue(new NewsletterConfirmationMail($existing->fresh()));
        } else {
            $subscriber = NewsletterSubscriber::create([
                'email'      => $email,
                'first_name' => $this->firstName ?: null,
                'source'     => 'public_form',
            ]);

            Mail::to($email)->queue(new NewsletterConfirmationMail($subscriber));
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
