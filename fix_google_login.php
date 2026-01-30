<?php
/**
 * Google Login Fix Script
 * This script will help you fix the Google authentication error
 */

echo "🔧 Google Login Fix Script\n";
echo "=========================\n\n";

// Check current configuration
$envFile = '.env';
if (!file_exists($envFile)) {
    echo "❌ .env file not found!\n";
    exit(1);
}

$envContent = file_get_contents($envFile);

// Extract current values
preg_match('/GOOGLE_CLIENT_ID=(.+)/', $envContent, $clientIdMatch);
preg_match('/GOOGLE_CLIENT_SECRET=(.+)/', $envContent, $clientSecretMatch);
preg_match('/GOOGLE_REDIRECT_URL=(.+)/', $envContent, $redirectMatch);

$clientId = isset($clientIdMatch[1]) ? trim($clientIdMatch[1]) : '';
$clientSecret = isset($clientSecretMatch[1]) ? trim($clientSecretMatch[1]) : '';
$redirectUrl = isset($redirectMatch[1]) ? trim($redirectMatch[1]) : '';

echo "📋 Current Configuration:\n";
echo "------------------------\n";
echo "Client ID: " . ($clientId ? "✅ Set" : "❌ Missing") . "\n";
echo "Client Secret: " . ($clientSecret && $clientSecret !== 'GOCSPX-PLACEHOLDER_ADD_YOUR_SECRET_HERE' ? "✅ Set" : "❌ Missing or Placeholder") . "\n";
echo "Redirect URL: " . ($redirectUrl ? "✅ Set" : "❌ Missing") . "\n\n";

// Check the main issue
if ($clientSecret === 'GOCSPX-PLACEHOLDER_ADD_YOUR_SECRET_HERE' || empty($clientSecret)) {
    echo "🚨 MAIN ISSUE FOUND:\n";
    echo "-------------------\n";
    echo "Your Client Secret is missing or still a placeholder!\n\n";
    
    echo "🔑 TO FIX THIS:\n";
    echo "1. Go to: https://console.cloud.google.com/apis/credentials\n";
    echo "2. Find your OAuth client: 298504334910-d473nbkn4o3scme102jk2vi4pilvse2p.apps.googleusercontent.com\n";
    echo "3. Click on it to view details\n";
    echo "4. Copy the Client Secret (starts with GOCSPX-)\n";
    echo "5. Replace the line in .env file:\n";
    echo "   FROM: GOOGLE_CLIENT_SECRET=GOCSPX-PLACEHOLDER_ADD_YOUR_SECRET_HERE\n";
    echo "   TO:   GOOGLE_CLIENT_SECRET=GOCSPX-your_actual_secret_here\n\n";
    
    echo "📝 EXAMPLE:\n";
    echo "If your Client Secret is: GOCSPX-AbCdEf123456789012345678901234\n";
    echo "Your .env should have: GOOGLE_CLIENT_SECRET=GOCSPX-AbCdEf123456789012345678901234\n\n";
} else {
    echo "✅ Configuration looks good!\n\n";
}

// Check if redirect URI is set in Google Console
echo "🌐 GOOGLE CONSOLE SETUP:\n";
echo "-----------------------\n";
echo "Make sure in your Google Cloud Console OAuth client, you have this redirect URI:\n";
echo "http://127.0.0.1:8000/auth/google/callback\n\n";

// Test steps
echo "🧪 TESTING STEPS:\n";
echo "----------------\n";
echo "1. Add your real Client Secret to .env\n";
echo "2. Run: php artisan config:clear\n";
echo "3. Visit: http://127.0.0.1:8000/login\n";
echo "4. Click 'Continue with Gmail Account'\n\n";

// Alternative login
echo "🔐 ALTERNATIVE LOGIN:\n";
echo "-------------------\n";
echo "While fixing Google login, you can use:\n";
echo "Email: admin@pos.com\n";
echo "Password: admin123\n\n";

echo "💡 NEED HELP?\n";
echo "------------\n";
echo "If you're still having issues:\n";
echo "1. Check Laravel logs: storage/logs/laravel.log\n";
echo "2. Make sure your Google project has Google+ API enabled\n";
echo "3. Verify your redirect URI matches exactly\n\n";

echo "🎯 The error 'Google authentication failed' happens because:\n";
echo "   - Missing or invalid Client Secret\n";
echo "   - Redirect URI mismatch\n";
echo "   - Google+ API not enabled\n\n";

echo "✨ Once you add the real Client Secret, Gmail login will work!\n";
?>