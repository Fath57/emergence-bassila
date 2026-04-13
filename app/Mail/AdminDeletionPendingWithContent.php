<?php

namespace App\Mail;

use App\Models\AccountDeletionRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminDeletionPendingWithContent extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public AccountDeletionRequest $request, public int $publishedPostCount) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: '[Admin] Demande de suppression d\'un membre avec contenus publiés');
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.account.admin-pending-content',
            with: [
                'user'       => $this->request->user,
                'postCount'  => $this->publishedPostCount,
                'purgeAt'    => $this->request->scheduled_purge_at,
                'adminUrl'   => route('admin.deletions'),
            ],
        );
    }
}
