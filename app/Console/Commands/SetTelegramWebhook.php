<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use TelegramBot\Api\BotApi;

class SetTelegramWebhook extends Command
{
    protected $signature = 'telegram:set-webhook {url?}';
    protected $description = 'Set the webhook URL for Telegram bot';

    public function handle()
    {
        $token = config('services.telegram.bot_token');
        
        if (!$token) {
            $this->error('Telegram bot token not configured!');
            return 1;
        }

        $url = $this->argument('url') ?: $this->ask('Enter your webhook URL (e.g., https://yourdomain.com/telegram/webhook)');
        
        if (!$url) {
            $this->error('Webhook URL is required!');
            return 1;
        }

        try {
            $bot = new BotApi($token);
            
            // Handle SSL issues in development
            if (config('app.env') === 'local') {
                $bot->setCurlOption(CURLOPT_SSL_VERIFYPEER, false);
                $bot->setCurlOption(CURLOPT_SSL_VERIFYHOST, false);
            }
            
            $result = $bot->setWebhook($url);
            
            if ($result) {
                $this->info("✅ Webhook set successfully!");
                $this->info("URL: {$url}");
                $this->info("Your bot will now receive updates at this URL.");
            } else {
                $this->error("❌ Failed to set webhook!");
            }
            
        } catch (\Exception $e) {
            $this->error("❌ Error setting webhook: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}