<?php
// Comprehensive Telegram Bot Test Script
$botToken = '8516986555:AAH3enGgrbjWPKnQRPwXRQHKVfGgqiQ2Rhw';

function makeRequest($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    return ['response' => $response, 'code' => $httpCode];
}

echo "🤖 Telegram Bot Test Script\n";
echo "==========================\n\n";

// Step 1: Test bot token
echo "1. Testing bot token...\n";
$result = makeRequest("https://api.telegram.org/bot{$botToken}/getMe");
if ($result['code'] === 200) {
    $data = json_decode($result['response'], true);
    if ($data['ok']) {
        echo "✅ Bot token is valid!\n";
        echo "   Bot name: {$data['result']['first_name']}\n";
        echo "   Username: @{$data['result']['username']}\n\n";
    } else {
        echo "❌ Bot token is invalid!\n";
        exit(1);
    }
} else {
    echo "❌ Failed to connect to Telegram API\n";
    exit(1);
}

// Step 2: Get chat ID
echo "2. Looking for recent messages...\n";
$result = makeRequest("https://api.telegram.org/bot{$botToken}/getUpdates");
if ($result['code'] === 200) {
    $data = json_decode($result['response'], true);
    
    if ($data['ok'] && !empty($data['result'])) {
        echo "✅ Found recent messages!\n\n";
        
        $chatIds = [];
        foreach ($data['result'] as $update) {
            if (isset($update['message']['chat'])) {
                $chat = $update['message']['chat'];
                $chatId = $chat['id'];
                $chatType = $chat['type'];
                $name = isset($chat['first_name']) ? $chat['first_name'] : '';
                $lastName = isset($chat['last_name']) ? $chat['last_name'] : '';
                $username = isset($chat['username']) ? '@' . $chat['username'] : '';
                
                if (!in_array($chatId, $chatIds)) {
                    $chatIds[] = $chatId;
                    echo "   Chat ID: {$chatId}\n";
                    echo "   Type: {$chatType}\n";
                    echo "   Name: {$name} {$lastName} {$username}\n";
                    echo "   ---\n";
                }
            }
        }
        
        if (count($chatIds) === 1) {
            $chatId = $chatIds[0];
            echo "\n3. Updating .env file with Chat ID: {$chatId}\n";
            
            // Update .env file
            $envFile = '.env';
            $envContent = file_get_contents($envFile);
            $envContent = preg_replace(
                '/TELEGRAM_CHAT_ID=.*/',
                "TELEGRAM_CHAT_ID={$chatId}",
                $envContent
            );
            file_put_contents($envFile, $envContent);
            echo "✅ .env file updated!\n\n";
            
            // Step 3: Send test message
            echo "4. Sending test receipt message...\n";
            $testMessage = "🛒 *RECEIPT TEST MESSAGE*\n\n";
            $testMessage .= "📋 *Receipt:* `TEST-001`\n";
            $testMessage .= "👤 *Customer:* Test Customer\n";
            $testMessage .= "💳 *Payment:* Cash\n";
            $testMessage .= "💰 *Total:* \$25.50\n";
            $testMessage .= "💵 *Paid:* \$30.00\n";
            $testMessage .= "💸 *Change:* \$4.50\n";
            $testMessage .= "📅 *Date:* " . date('M d, Y H:i') . "\n\n";
            $testMessage .= "*Items Purchased:*\n";
            $testMessage .= "• Test Product x2 = \$25.50\n\n";
            $testMessage .= "📊 *Breakdown:*\n";
            $testMessage .= "Subtotal: \$23.18\n";
            $testMessage .= "Tax: \$2.32\n";
            $testMessage .= "*Total: \$25.50*\n\n";
            $testMessage .= "✅ *Your Telegram receipt system is working!*";
            
            $sendUrl = "https://api.telegram.org/bot{$botToken}/sendMessage?" . http_build_query([
                'chat_id' => $chatId,
                'text' => $testMessage,
                'parse_mode' => 'Markdown'
            ]);
            
            $result = makeRequest($sendUrl);
            if ($result['code'] === 200) {
                $data = json_decode($result['response'], true);
                if ($data['ok']) {
                    echo "✅ Test receipt message sent successfully!\n";
                    echo "   Check your Telegram for the test receipt!\n\n";
                    echo "🎉 SETUP COMPLETE!\n";
                    echo "Your Telegram bot is now ready to send receipt notifications!\n";
                } else {
                    echo "❌ Failed to send test message: " . $data['description'] . "\n";
                }
            } else {
                echo "❌ Failed to send test message. HTTP Code: {$result['code']}\n";
            }
            
        } else {
            echo "\nMultiple chats found. Please manually update your .env file.\n";
        }
    } else {
        echo "❌ No recent messages found.\n";
        echo "\nPlease:\n";
        echo "1. Go to https://t.me/LucKMart_bot\n";
        echo "2. Click 'START' or send any message\n";
        echo "3. Run this script again: php telegram_test.php\n";
    }
}

// Clean up
unlink('get_chat_id.php');
echo "\nCleaned up temporary files.\n";
?>