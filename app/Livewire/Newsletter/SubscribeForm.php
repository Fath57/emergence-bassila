<?php

namespace App\Livewire\Newsletter;

use App\Models\NewsletterSubscription;
use Livewire\Component;

class SubscribeForm extends Component
{
    public string $email = '';
    public bool $subscribed = false;

    protected function rules(): array
    {
        return [
            'email' => ['required', 'email', 'unique:newsletter_subscriptions,email'],
        ];
    }

    public function subscribe(): void
    {
        $this->validate();

        NewsletterSubscription::create(['email' => $this->email]);

        $this->email = '';
        $this->subscribed = true;
    }

    public function render()
    {
        return view('livewire.newsletter.subscribe-form');
    }
}
