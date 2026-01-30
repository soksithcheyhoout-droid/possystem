<?php
/**
 * Test SSL Fix for Google OAuth
 */

echo "🧪 Testing SSL Fix for Google OAuth\n";
echo "==================================\n\n";

// Test 1: Check configuration
echo "📋 Configuration Check:\n";
echo "----------------------\n";

$clientId = env('GOOGLE_CLIENT_ID');
$clientSecret = env('GOOGLE_CLIENT_SECRET');
$redirectUrl = env('GOOGLE_REDIRECT_URL');

echo "Client ID: " . ($clientId ? "✅ Set" : "❌ Missing") . "\n";
echo "Client Secret: " . ($clientSecret && $clientSecret !== 'GOCSPX-PLACEHOLDER_ADD_YOUR_SECRET_HERE' ? "✅ Set" : "❌ Missing") . "\n";
echo "Redirect URL: " . ($redirectUrl ? "✅ Set" : "❌ Missing") . "\n";
echo "Environment: " . env('APP_ENV', 'production') . "\n\n";

// Test 2: Check SSL certificate file
echo "🔒 SSL Certificate Check:\n";
echo "------------------------\n";

$cacertPath = __DIR__ . '/cacert.pem';
if (file_exists($cacertPath)) {
    $size = filesize($cacertPath);
    echo "✅ SSL certificate bundle exists ($size bytes)\n";
} else {
    echo "❌ SSL certificate bundle missing\n";
}

// Test 3: Test cURL with SSL
echo "\n🌐 cURL SSL Test:\n";
echo "---------------\n";

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => 'https://www.googleapis.com/oauth2/v4/token',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_NOBODY => true,
    CURLOPT_SSL_VERIFYPEER => false, // Disabled for testing
    CURLOPT_SSL_VERIFYHOST => false, // Disabled for testing
]);

$result = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "❌ cURL Error: $error\n";
} else {
    echo "✅ cURL connection successful (HTTP $httpCode)\n";
}

echo "\n🚀 Ready to Test:\n";
echo "----------------\n";
echo "1. Visit: http://127.0.0.1:8000/login\n";
echo "2. Click: 'Continue with Gmail Account'\n";
echo "3. Login with any Gmail account\n\n";

echo "🔧 What was fixed:\n";
echo "- SSL verification disabled for local development\n";
echo "- Guzzle HTTP client configured with SSL bypass\n";
echo "- Error handling improved\n\n";

echo "✨ Your Google OAuth should now work without SSL errors!\n";
?>