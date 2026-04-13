<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountDeletionCompleted extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $firstName) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Votre compte Bassila Emergence a été supprimé');
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.account.deletion-completed',
            with: ['firstName' => $this->firstName],
        );
    }
}
