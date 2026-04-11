<?php

namespace App\Mail;

use App\Models\UserInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public UserInvitation $invitation) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Vous êtes invité à rejoindre Bassila Émergence",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.invitation',
            with: [
                'invitation'    => $this->invitation,
                'invitedByName' => $this->invitation->invitedBy->name,
                'acceptUrl'     => route('invitation.accept', ['token' => $this->invitation->token]),
            ],
        );
    }
}
