<?php

namespace App\Jobs;

use App\Mail\NewsletterCampaignMail;
use App\Models\NewsletterCampaignSend;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendNewsletterCampaignMail implements ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        public readonly int $sendId,
    ) {}

    public function middleware(): array
    {
        return [new RateLimited('newsletter-send')];
    }

    public function handle(): void
    {
        // Skip if the batch was cancelled
        if ($this->batch()?->cancelled()) {
            return;
        }

        $send = NewsletterCampaignSend::with(['campaign', 'subscriber'])->find($this->sendId);

        if (! $send) {
            return;
        }

        // sendNow() bypasses the ShouldQueue interface — this job IS the queue worker
        Mail::to($send->subscriber->email)
            ->sendNow(new NewsletterCampaignMail($send->campaign, $send->subscriber, $send));

        $send->campaign->increment('sent_count');
    }

    public function failed(Throwable $e): void
    {
        $send = NewsletterCampaignSend::find($this->sendId);

        if ($send) {
            $send->update(['error' => $e->getMessage()]);
        }
    }
}
