<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SetupGoogleOAuth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'google:setup {--client-id=} {--client-secret=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup Google OAuth credentials';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 Google OAuth Setup Helper');
        $this->newLine();

        // Check if credentials are provided via options
        $clientId = $this->option('client-id');
        $clientSecret = $this->option('client-secret');

        // If not provided via options, ask for them
        if (!$clientId) {
            $this->warn('⚠️  You provided an API Key, but we need OAuth credentials.');
            $this->info('📋 To get OAuth credentials:');
            $this->info('1. Visit: https://console.cloud.google.com/');
            $this->info('2. Go to "APIs & Services" > "Credentials"');
            $this->info('3. Create "OAuth 2.0 Client ID"');
            $this->info('4. Use redirect URI: ' . url('/auth/google/callback'));
            $this->newLine();

            $clientId = $this->ask('Enter your Google Client ID (starts with numbers, ends with .apps.googleusercontent.com)');
        }

        if (!$clientSecret) {
            $clientSecret = $this->ask('Enter your Google Client Secret (starts with GOCSPX-)');
        }

        if (!$clientId || !$clientSecret) {
            $this->error('❌ Both Client ID and Client Secret are required.');
            return 1;
        }

        // Validate format
        if (!str_contains($clientId, '.apps.googleusercontent.com')) {
            $this->warn('⚠️  Client ID format looks incorrect. It should end with .apps.googleusercontent.com');
        }

        if (!str_starts_with($clientSecret, 'GOCSPX-')) {
            $this->warn('⚠️  Client Secret format looks incorrect. It should start with GOCSPX-');
        }

        // Update .env file
        $this->updateEnvFile($clientId, $clientSecret);

        $this->info('✅ Google OAuth credentials have been added to your .env file!');
        $this->newLine();

        // Clear config cache
        $this->call('config:clear');

        // Test the configuration
        $this->info('🧪 Testing configuration...');
        $this->call('google:test');

        $this->newLine();
        $this->info('🎉 Setup complete! You can now test Gmail login at:');
        $this->info('   ' . url('/login'));

        return 0;
    }

    private function updateEnvFile($clientId, $clientSecret)
    {
        $envPath = base_path('.env');
        $envContent = File::get($envPath);

        // Update or add Google OAuth credentials
        $patterns = [
            '/GOOGLE_CLIENT_ID=.*/' => 'GOOGLE_CLIENT_ID=' . $clientId,
            '/GOOGLE_CLIENT_SECRET=.*/' => 'GOOGLE_CLIENT_SECRET=' . $clientSecret,
        ];

        foreach ($patterns as $pattern => $replacement) {
            if (preg_match($pattern, $envContent)) {
                $envContent = preg_replace($pattern, $replacement, $envContent);
            } else {
                // If not found, add at the end
                $envContent .= "\n" . $replacement;
            }
        }

        File::put($envPath, $envContent);
    }
}
