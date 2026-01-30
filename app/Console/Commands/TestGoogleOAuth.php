<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestGoogleOAuth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'google:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Google OAuth configuration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Testing Google OAuth Configuration...');
        $this->newLine();

        // Check if credentials are set
        $clientId = config('services.google.client_id');
        $clientSecret = config('services.google.client_secret');
        $redirectUrl = config('services.google.redirect');

        $this->table(['Setting', 'Status', 'Value'], [
            ['Client ID', $clientId ? '✅ Set' : '❌ Missing', $clientId ? substr($clientId, 0, 20) . '...' : 'Not configured'],
            ['Client Secret', $clientSecret ? '✅ Set' : '❌ Missing', $clientSecret ? substr($clientSecret, 0, 20) . '...' : 'Not configured'],
            ['Redirect URL', $redirectUrl ? '✅ Set' : '❌ Missing', $redirectUrl ?: 'Not configured'],
        ]);

        $this->newLine();

        if (!$clientId || !$clientSecret) {
            $this->error('❌ Google OAuth is not configured!');
            $this->newLine();
            $this->info('📋 To set up Google OAuth:');
            $this->info('1. Follow the guide in SETUP_GOOGLE_LOGIN_STEP_BY_STEP.md');
            $this->info('2. Add credentials to your .env file:');
            $this->info('   GOOGLE_CLIENT_ID=your_client_id_here');
            $this->info('   GOOGLE_CLIENT_SECRET=your_client_secret_here');
            $this->info('3. Run: php artisan config:clear');
            return 1;
        }

        $this->info('✅ Google OAuth appears to be configured!');
        $this->newLine();
        $this->info('🚀 Next steps:');
        $this->info('1. Visit: http://localhost/login');
        $this->info('2. Click "Continue with Google"');
        $this->info('3. Test with your Gmail account');
        
        return 0;
    }
}
