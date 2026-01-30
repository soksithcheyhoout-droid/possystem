<?php
/**
 * Gmail Login Test Script
 * 
 * This script helps you test Gmail login functionality
 * Run this after setting up your Google OAuth credentials
 */

echo "🚀 Gmail Login Test for POS System\n";
echo "==================================\n\n";

// Check if .env file exists
if (!file_exists('.env')) {
    echo "❌ Error: .env file not found!\n";
    exit(1);
}

// Read .env file
$envContent = file_get_contents('.env');

// Check Google OAuth configuration
$clientId = '';
$clientSecret = '';
$redirectUrl = '';

if (preg_match('/GOOGLE_CLIENT_ID=(.+)/', $envContent, $matches)) {
    $clientId = trim($matches[1]);
}

if (preg_match('/GOOGLE_CLIENT_SECRET=(.+)/', $envContent, $matches)) {
    $clientSecret = trim($matches[1]);
}

if (preg_match('/GOOGLE_REDIRECT_URL=(.+)/', $envContent, $matches)) {
    $redirectUrl = trim($matches[1]);
}

echo "📋 Configuration Check:\n";
echo "----------------------\n";

// Check Client ID
if (empty($clientId) || $clientId === 'your_client_id_here') {
    echo "❌ Client ID: Not configured\n";
} else {
    echo "✅ Client ID: " . substr($clientId, 0, 20) . "...\n";
}

// Check Client Secret
if (empty($clientSecret) || $clientSecret === 'your_client_secret_here' || $clientSecret === 'GOCSPX-PLACEHOLDER_ADD_YOUR_SECRET_HERE') {
    echo "❌ Client Secret: Not configured (you need to add this!)\n";
} else {
    echo "✅ Client Secret: " . substr($clientSecret, 0, 15) . "...\n";
}

// Check Redirect URL
if (empty($redirectUrl)) {
    echo "❌ Redirect URL: Not configured\n";
} else {
    echo "✅ Redirect URL: $redirectUrl\n";
}

echo "\n";

// Check if Laravel server is running
$serverRunning = false;
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1:8000');
curl_setopt($ch, CURLOPT_NOBODY, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    $serverRunning = true;
}

echo "🌐 Server Status:\n";
echo "----------------\n";

if ($serverRunning) {
    echo "✅ Laravel server is running at http://127.0.0.1:8000\n";
} else {
    echo "❌ Laravel server is not running\n";
    echo "   Run: php artisan serve\n";
}

echo "\n";

// Provide next steps
echo "📝 Next Steps:\n";
echo "-------------\n";

if (empty($clientSecret) || $clientSecret === 'GOCSPX-PLACEHOLDER_ADD_YOUR_SECRET_HERE') {
    echo "1. ⚠️  Get your Client Secret from Google Cloud Console:\n";
    echo "   - Visit: https://console.cloud.google.com/apis/credentials\n";
    echo "   - Find your OAuth client: 298504334910-d473nbkn4o3scme102jk2vi4pilvse2p.apps.googleusercontent.com\n";
    echo "   - Copy the Client Secret (starts with GOCSPX-)\n";
    echo "   - Replace GOCSPX-PLACEHOLDER_ADD_YOUR_SECRET_HERE in .env file\n\n";
}

if (!$serverRunning) {
    echo "2. 🚀 Start Laravel server:\n";
    echo "   php artisan serve\n\n";
}

echo "3. 🧪 Test Gmail Login:\n";
echo "   - Visit: http://127.0.0.1:8000/login\n";
echo "   - Click 'Continue with Gmail Account'\n";
echo "   - Or visit test page: http://127.0.0.1:8000/auth/google/test\n\n";

echo "4. 🔧 Clear config cache after changes:\n";
echo "   php artisan config:clear\n\n";

// Test URLs
echo "🔗 Test URLs:\n";
echo "------------\n";
echo "Login Page: http://127.0.0.1:8000/login\n";
echo "Test Page:  http://127.0.0.1:8000/auth/google/test\n";
echo "Dashboard:  http://127.0.0.1:8000/admin\n\n";

// Sample Gmail accounts for testing
echo "📧 Test with Any Gmail Account:\n";
echo "------------------------------\n";
echo "You can test with any Gmail account:\n";
echo "- Your personal Gmail\n";
echo "- A test Gmail account\n";
echo "- Any Google account\n\n";

echo "✨ The system will automatically create a user account for any Gmail user!\n";
echo "🎉 Happy testing!\n";
?>