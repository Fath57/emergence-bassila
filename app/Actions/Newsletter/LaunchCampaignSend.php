<?php

namespace App\Actions\Newsletter;

use App\Jobs\SendNewsletterCampaignMail;
use App\Models\NewsletterCampaign;
use App\Models\NewsletterSubscriber;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class LaunchCampaignSend
{
    public function run(NewsletterCampaign $campaign): void
    {
        if ($campaign->status !== 'draft') {
            throw new \InvalidArgumentException("Campaign #{$campaign->id} is not in draft status.");
        }

        $subscribers = NewsletterSubscriber::active()->get();

        if ($subscribers->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($campaign, $subscribers) {
            // Create send rows and collect IDs for job dispatch
            $now  = now();
            $rows = $subscribers->map(fn ($sub) => [
                'campaign_id'   => $campaign->id,
                'subscriber_id' => $sub->id,
                'open_token'    => Str::random(32),
                'sent_at'       => $now,
            ])->toArray();

            // Insert in chunks to avoid packet size limits
            foreach (array_chunk($rows, 500) as $chunk) {
                DB::table('newsletter_campaign_sends')->insertOrIgnore($chunk);
            }

            $campaign->update([
                'status'           => 'sending',
                'sent_at'          => $now,
                'recipients_count' => $subscribers->count(),
            ]);
        });

        // Reload send IDs after insert
        $sendIds = $campaign->sends()->pluck('id');

        $jobs = $sendIds->map(fn ($id) => new SendNewsletterCampaignMail($id))->all();

        Bus::batch($jobs)
            ->name("campaign-{$campaign->id}")
            ->then(function (Batch $batch) use ($campaign) {
                $campaign->update([
                    'status'      => 'sent',
                    'finished_at' => now(),
                ]);
            })
            ->catch(function (Batch $batch, Throwable $e) use ($campaign) {
                $campaign->update(['status' => 'failed']);
            })
            ->allowFailures()
            ->onQueue('newsletters')
            ->dispatch();
    }
}
