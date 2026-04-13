<?php

namespace App\Mail;

use App\Models\AccountDeletionRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountDeletionConfirmed extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public AccountDeletionRequest $request) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Suppression de votre compte confirmée');
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.account.deletion-confirmed',
            with: [
                'user'    => $this->request->user,
                'purgeAt' => $this->request->scheduled_purge_at,
            ],
        );
    }
}
