<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Exception;

class GetTelegramChatId extends Command
{
    protected $signature = 'telegram:get-chat-id';
    protected $description = 'Get your Telegram chat ID by checking recent messages';

    public function handle()
    {
        $token = config('services.telegram.bot_token');
        
        if (!$token) {
            $this->error('Telegram bot token not configured. Please set TELEGRAM_BOT_TOKEN in your .env file.');
            return 1;
        }

        try {
            $bot = new BotApi($token);
            // Handle SSL issues in development
            if (config('app.env') === 'local') {
                $bot->setCurlOption(CURLOPT_SSL_VERIFYPEER, false);
                $bot->setCurlOption(CURLOPT_SSL_VERIFYHOST, false);
            }
            $updates = $bot->getUpdates();
            
            if (empty($updates)) {
                $this->warn('No recent messages found.');
                $this->info('To get your chat ID:');
                $this->info('1. Send a message to your bot on Telegram');
                $this->info('2. Run this command again');
                $this->info('');
                $this->info('Or manually get your chat ID:');
                $this->info('- Personal: Message @userinfobot');
                $this->info('- Group: Add bot to group, then visit:');
                $this->info("  https://api.telegram.org/bot{$token}/getUpdates");
                return 0;
            }

            $this->info('Recent chats found:');
            $this->info('');
            
            $chatIds = [];
            foreach ($updates as $update) {
                $chat = $update->getMessage()->getChat();
                $chatId = $chat->getId();
                $chatType = $chat->getType();
                $chatTitle = $chat->getTitle() ?: $chat->getFirstName() . ' ' . $chat->getLastName();
                
                if (!in_array($chatId, $chatIds)) {
                    $chatIds[] = $chatId;
                    $this->info("Chat ID: {$chatId}");
                    $this->info("Type: {$chatType}");
                    $this->info("Name/Title: {$chatTitle}");
                    $this->info('---');
                }
            }
            
            if (count($chatIds) == 1) {
                $chatId = $chatIds[0];
                $this->info("Found one chat. You can use this Chat ID: {$chatId}");
                $this->info('');
                $this->ask('Would you like me to update your .env file with this Chat ID? (y/n)', 'y');
                
                if ($this->confirm('Update .env file?')) {
                    $this->updateEnvFile($chatId);
                    $this->info('✅ Chat ID updated in .env file!');
                    $this->info('You can now test the connection in the admin panel.');
                }
            } else {
                $this->info('Multiple chats found. Choose the appropriate Chat ID and update your .env file manually.');
            }
            
        } catch (Exception $e) {
            $this->error('Error connecting to Telegram: ' . $e->getMessage());
            $this->info('Please check your bot token and try again.');
            return 1;
        }

        return 0;
    }

    private function updateEnvFile($chatId)
    {
        $envFile = base_path('.env');
        $envContent = file_get_contents($envFile);
        
        $envContent = preg_replace(
            '/TELEGRAM_CHAT_ID=.*/',
            "TELEGRAM_CHAT_ID={$chatId}",
            $envContent
        );
        
        file_put_contents($envFile, $envContent);
    }
}
