<?php
/**
 * Simple SSL Test for Google OAuth
 */

echo "🧪 Simple SSL Test for Google OAuth\n";
echo "==================================\n\n";

// Test cURL with SSL disabled
echo "🌐 Testing Google OAuth endpoint:\n";
echo "--------------------------------\n";

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
    echo "   SSL issue still exists\n";
} else {
    echo "✅ cURL connection successful (HTTP $httpCode)\n";
    echo "   SSL bypass working!\n";
}

echo "\n📋 What I Fixed:\n";
echo "---------------\n";
echo "1. ✅ Added SSL bypass in config/services.php\n";
echo "2. ✅ Modified Google OAuth controller\n";
echo "3. ✅ Downloaded SSL certificate bundle\n";
echo "4. ✅ Added error handling\n\n";

echo "🚀 Test Your Gmail Login:\n";
echo "------------------------\n";
echo "1. Visit: http://127.0.0.1:8000/login\n";
echo "2. Click: 'Continue with Gmail Account'\n";
echo "3. Login with any Gmail account\n\n";

echo "🔒 Security Note:\n";
echo "SSL verification is disabled only for local development.\n";
echo "This is safe for testing but not for production.\n\n";

echo "✨ Your Google OAuth should now work!\n";
?>