<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Socialite\Facades\Socialite;

class GoogleOAuthServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Fix SSL certificate issue for Windows development
        if (app()->environment('local') && strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Configure Guzzle HTTP client for Socialite
            Socialite::buildProvider(
                \Laravel\Socialite\Two\GoogleProvider::class,
                config('services.google')
            )->setHttpClient(
                new \GuzzleHttp\Client([
                    'verify' => false, // Disable SSL verification for local development
                    'timeout' => 30,
                    'connect_timeout' => 10,
                ])
            );
        }
    }

    public function register()
    {
        //
    }
}