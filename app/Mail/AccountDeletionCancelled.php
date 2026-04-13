<?php

namespace App\Mail;

use App\Models\AccountDeletionRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountDeletionCancelled extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public AccountDeletionRequest $request) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Votre demande de suppression a été annulée');
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.account.deletion-cancelled',
            with: ['user' => $this->request->user],
        );
    }
}
