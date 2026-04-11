<?php

namespace App\Mail;

use App\Models\NewsletterCampaign;
use App\Models\NewsletterCampaignSend;
use App\Models\NewsletterSubscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

class NewsletterCampaignMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly NewsletterCampaign $campaign,
        public readonly NewsletterSubscriber $subscriber,
        public readonly NewsletterCampaignSend $send,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->campaign->subject,
        );
    }

    public function headers(): Headers
    {
        $unsubscribeUrl  = url('/newsletter/desabonner/' . $this->subscriber->unsubscribe_token);
        $trackingPixelUrl = url('/newsletter/pixel/' . $this->send->open_token . '.gif');

        return new Headers(
            text: [
                // RFC 8058 one-click unsubscribe
                'List-Unsubscribe'      => '<' . $unsubscribeUrl . '>, <mailto:desabonnement@bassila-emergence.fr?subject=unsubscribe>',
                'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
                // For open tracking
                'X-Campaign-Id'         => (string) $this->campaign->id,
                'X-Tracking-Pixel'      => $trackingPixelUrl,
            ],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.newsletter.newsletter',
        );
    }
}
