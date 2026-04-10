<?php

namespace App\Mail;

use App\Models\Profile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProfileVerificationApproved extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly Profile $profile,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[Bassila Network] Votre profil a été vérifié !',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.profile.approved',
        );
    }
}
