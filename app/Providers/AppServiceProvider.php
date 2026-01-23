<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('promotion-emails', function (object $job) {
            return Limit::perMinute(5)->by('promotion-emails');
        });

        RateLimiter::for('emails', function (object $job) {
            return Limit::perMinute(10)->by('emails');
        });
    }
}
