<?php

namespace App\Mail;

use App\Models\AccountDeletionRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountDeletionRequested extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public AccountDeletionRequest $request) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Confirmation de la suppression de votre compte');
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.account.deletion-requested',
            with: [
                'user'       => $this->request->user,
                'confirmUrl' => route('account.deletion.confirm', ['token' => $this->request->confirmation_token]),
                'ttlHours'   => AccountDeletionRequest::TOKEN_TTL_HOURS,
            ],
        );
    }
}
