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
            'email'     => ['required', 'email', 'unique:newsletter_subscribers,email'],
            'firstName' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function subscribe(): void
    {
        $this->validate();

        NewsletterSubscriber::create([
            'email'      => $this->email,
            'first_name' => $this->firstName ?: null,
            'source'     => 'public_form',
        ]);

        $this->email     = '';
        $this->firstName = '';
        $this->pending   = true;
    }

    public function render()
    {
        return view('livewire.newsletter.subscribe-form');
    }
}
