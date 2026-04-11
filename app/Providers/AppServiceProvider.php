<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Throttle newsletter sending to 300 mails per minute per worker
        RateLimiter::for('newsletter-send', function () {
            return Limit::perMinute(300);
        });
    }
}
