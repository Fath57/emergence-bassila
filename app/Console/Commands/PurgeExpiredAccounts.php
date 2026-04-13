<?php

namespace App\Console\Commands;

use App\Models\AccountDeletionRequest;
use Illuminate\Console\Command;

class PurgeExpiredAccounts extends Command
{
    protected $signature = 'accounts:purge-expired';
    protected $description = 'Purge accounts whose deletion grace period has expired';

    public function handle(): int
    {
        $count = 0;

        AccountDeletionRequest::dueForPurge()->with('user')->get()->each(function ($req) use (&$count) {
            if (! $req->user) {
                $req->markPurged();
                return;
            }

            try {
                $req->purge();
                $count++;
            } catch (\Throwable $e) {
                $this->error("Failed to purge request {$req->id}: {$e->getMessage()}");
                report($e);
            }
        });

        $this->info("Purged {$count} account(s).");
        return self::SUCCESS;
    }
}
