<?php
/**
 * Quick SSL Fix - Run this before testing Google OAuth
 */

// Disable SSL verification for testing (NOT for production)
if (function_exists('curl_setopt_array')) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
    ]);
    curl_close($ch);
}

// Set environment variable
putenv('CURL_CA_BUNDLE=' . __DIR__ . '/cacert.pem');

echo "SSL verification temporarily disabled for testing\n";
echo "Visit: http://127.0.0.1:8000/login and test Google login\n";
?>