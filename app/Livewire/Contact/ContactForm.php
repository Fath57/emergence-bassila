<?php

namespace App\Livewire\Contact;

use App\Mail\ContactMessageReceived;
use App\Mail\ContactMessageSent;
use App\Models\ContactMessage;
use App\Models\Profile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class ContactForm extends Component
{
    public Profile $profile;

    public string $subject = '';
    public string $message = '';
    public bool $sent = false;

    protected function rules(): array
    {
        return [
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'min:10', 'max:2000'],
        ];
    }

    public function send(): void
    {
        $key = 'contact:' . Auth::id();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('message', 'Trop de messages envoyés. Veuillez patienter avant de réessayer.');
            return;
        }

        RateLimiter::hit($key, 60);

        $this->validate();

        $contact = ContactMessage::create([
            'from_user_id' => Auth::id(),
            'to_user_id'   => $this->profile->user_id,
            'subject'      => $this->subject,
            'message'      => $this->message,
        ]);

        $contact->load(['sender', 'receiver']);

        Mail::to($this->profile->user)->queue(new ContactMessageReceived($contact));
        Mail::to(Auth::user())->queue(new ContactMessageSent($contact));

        $this->subject = '';
        $this->message = '';
        $this->sent    = true;
    }

    public function render()
    {
        return view('livewire.contact.contact-form');
    }
}
