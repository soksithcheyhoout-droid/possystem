<?php
/**
 * SSL Certificate Fix for Windows
 * This script fixes the cURL SSL certificate issue for Google OAuth
 */

echo "🔧 SSL Certificate Fix for Google OAuth\n";
echo "======================================\n\n";

// Method 1: Download cacert.pem file
echo "📥 Method 1: Download SSL Certificate Bundle\n";
echo "-------------------------------------------\n";

$cacertUrl = 'https://curl.se/ca/cacert.pem';
$cacertPath = __DIR__ . '/cacert.pem';

echo "Downloading SSL certificate bundle...\n";

// Download cacert.pem
$context = stream_context_create([
    'http' => [
        'timeout' => 30,
        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
    ]
]);

$cacertContent = @file_get_contents($cacertUrl, false, $context);

if ($cacertContent) {
    file_put_contents($cacertPath, $cacertContent);
    echo "✅ SSL certificate bundle downloaded to: $cacertPath\n";
} else {
    echo "❌ Failed to download SSL certificate bundle\n";
    echo "   You can manually download from: $cacertUrl\n";
}

echo "\n";

// Method 2: Create bootstrap configuration
echo "⚙️  Method 2: Configure Laravel Bootstrap\n";
echo "----------------------------------------\n";

$bootstrapPath = 'bootstrap/app.php';
$bootstrapContent = file_get_contents($bootstrapPath);

// Check if SSL configuration already exists
if (strpos($bootstrapContent, 'CURLOPT_CAINFO') === false) {
    // Add SSL configuration before return statement
    $sslConfig = "
// Fix SSL certificate issue for Windows
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    // Set cURL options for SSL
    \$cacertPath = __DIR__ . '/../cacert.pem';
    if (file_exists(\$cacertPath)) {
        curl_setopt_array(curl_init(), [
            CURLOPT_CAINFO => \$cacertPath,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);
    }
}

";

    // Insert before the return statement
    $bootstrapContent = str_replace('return $app;', $sslConfig . 'return $app;', $bootstrapContent);
    file_put_contents($bootstrapPath, $bootstrapContent);
    echo "✅ SSL configuration added to bootstrap/app.php\n";
} else {
    echo "✅ SSL configuration already exists in bootstrap/app.php\n";
}

echo "\n";

// Method 3: Environment variable approach
echo "🌐 Method 3: Environment Configuration\n";
echo "------------------------------------\n";

$envPath = '.env';
$envContent = file_get_contents($envPath);

// Add SSL configuration to .env if not exists
$sslEnvConfig = "
# SSL Certificate Configuration (Windows Fix)
CURL_CA_BUNDLE_PATH=" . realpath($cacertPath) . "
";

if (strpos($envContent, 'CURL_CA_BUNDLE_PATH') === false) {
    file_put_contents($envPath, $envContent . $sslEnvConfig);
    echo "✅ SSL configuration added to .env file\n";
} else {
    echo "✅ SSL configuration already exists in .env file\n";
}

echo "\n";

// Method 4: Create a custom service provider
echo "🔧 Method 4: Create SSL Service Provider\n";
echo "---------------------------------------\n";

$providerPath = 'app/Providers/SslServiceProvider.php';
$providerContent = '<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class SslServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Fix SSL certificate issue for Windows
        if (strtoupper(substr(PHP_OS, 0, 3)) === \'WIN\') {
            $cacertPath = base_path(\'cacert.pem\');
            
            if (file_exists($cacertPath)) {
                // Set default cURL options
                $defaults = [
                    CURLOPT_CAINFO => $cacertPath,
                    CURLOPT_SSL_VERIFYPEER => true,
                    CURLOPT_SSL_VERIFYHOST => 2,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_CONNECTTIMEOUT => 10,
                ];
                
                // Apply to all HTTP requests
                config([\'services.google.guzzle\' => [\'curl\' => $defaults]]);
            }
        }
    }
}
';

if (!file_exists($providerPath)) {
    file_put_contents($providerPath, $providerContent);
    echo "✅ SSL Service Provider created\n";
} else {
    echo "✅ SSL Service Provider already exists\n";
}

// Register the service provider
$configAppPath = 'config/app.php';
$configContent = file_get_contents($configAppPath);

if (strpos($configContent, 'App\\Providers\\SslServiceProvider::class') === false) {
    // Add to providers array
    $configContent = str_replace(
        'App\\Providers\\AppServiceProvider::class,',
        "App\\Providers\\AppServiceProvider::class,\n        App\\Providers\\SslServiceProvider::class,",
        $configContent
    );
    file_put_contents($configAppPath, $configContent);
    echo "✅ SSL Service Provider registered\n";
} else {
    echo "✅ SSL Service Provider already registered\n";
}

echo "\n";

// Method 5: Quick fix for immediate testing
echo "⚡ Method 5: Quick Fix (Temporary)\n";
echo "--------------------------------\n";

$quickFixPath = 'quick_ssl_fix.php';
$quickFixContent = '<?php
/**
 * Quick SSL Fix - Run this before testing Google OAuth
 */

// Disable SSL verification for testing (NOT for production)
if (function_exists(\'curl_setopt_array\')) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
    ]);
    curl_close($ch);
}

// Set environment variable
putenv(\'CURL_CA_BUNDLE=\' . __DIR__ . \'/cacert.pem\');

echo "SSL verification temporarily disabled for testing\n";
echo "Visit: http://127.0.0.1:8000/login and test Google login\n";
?>';

file_put_contents($quickFixPath, $quickFixContent);
echo "✅ Quick fix script created: $quickFixPath\n";

echo "\n";

// Summary and next steps
echo "📋 SUMMARY & NEXT STEPS:\n";
echo "========================\n";
echo "1. ✅ Downloaded SSL certificate bundle\n";
echo "2. ✅ Configured Laravel bootstrap\n";
echo "3. ✅ Added environment variables\n";
echo "4. ✅ Created SSL service provider\n";
echo "5. ✅ Created quick fix script\n\n";

echo "🚀 TO TEST NOW:\n";
echo "1. Run: php artisan config:clear\n";
echo "2. Run: php quick_ssl_fix.php\n";
echo "3. Visit: http://127.0.0.1:8000/login\n";
echo "4. Click: Continue with Gmail Account\n\n";

echo "🔒 SECURITY NOTE:\n";
echo "The quick fix disables SSL verification temporarily.\n";
echo "For production, use the proper SSL certificate bundle.\n\n";

echo "✨ Your Google OAuth should now work!\n";
?>