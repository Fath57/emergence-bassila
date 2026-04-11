<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class VerifyEmailMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $user) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Confirmez votre adresse email — Bassila Émergence',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.verify-email',
            with: [
                'user'       => $this->user,
                'verifyUrl'  => $this->buildVerificationUrl(),
                'firstName'  => $this->user->first_name,
            ],
        );
    }

    /**
     * Mirrors Laravel's default VerifyEmail::verificationUrl() logic so the
     * generated link validates against \Illuminate\Foundation\Auth\EmailVerificationRequest.
     */
    private function buildVerificationUrl(): string
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id'   => $this->user->getKey(),
                'hash' => sha1($this->user->getEmailForVerification()),
            ]
        );
    }
}
