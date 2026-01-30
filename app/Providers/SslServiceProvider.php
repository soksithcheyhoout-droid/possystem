<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class SslServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Fix SSL certificate issue for Windows
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $cacertPath = base_path('cacert.pem');
            
            if (file_exists($cacertPath)) {
                // Set default cURL options
                $defaults = [
                    CURLOPT_CAINFO => $cacertPath,
                    CURLOPT_SSL_VERIFYPEER => true,
                    CURLOPT_SSL_VERIFYHOST => 2,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_CONNECTTIMEOUT => 10,
                ];
                
                // Apply to all HTTP requests
                config(['services.google.guzzle' => ['curl' => $defaults]]);
            }
        }
    }
}
