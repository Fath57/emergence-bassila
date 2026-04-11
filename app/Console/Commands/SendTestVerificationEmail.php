<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

/**
 * Temporary one-off command to dispatch a verification email without
 * creating a new unverified account. Removed once the French-branded
 * VerifyEmailMail is confirmed to render correctly in production.
 */
class SendTestVerificationEmail extends Command
{
    protected $signature = 'mail:test-verify {user_id}';

    protected $description = 'Dispatch the email verification mailable to the given user id.';

    public function handle(): int
    {
        $user = User::find($this->argument('user_id'));

        if (! $user) {
            $this->error("User #{$this->argument('user_id')} not found.");
            return self::FAILURE;
        }

        $user->sendEmailVerificationNotification();

        $this->info("Queued verification email to {$user->email} (user #{$user->id}).");

        return self::SUCCESS;
    }
}
