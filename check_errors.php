<?php
/**
 * Check PHP Errors
 * This file helps identify PHP errors causing white pages
 */

// Enable all error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "<h1>PHP Error Check</h1>";
echo "<pre>";

echo "1. PHP Version: " . PHP_VERSION . "\n";
echo "2. Server: " . $_SERVER['SERVER_SOFTWARE'] . "\n\n";

// Test config loading
echo "3. Testing config.php...\n";
try {
    require_once 'config/config.php';
    echo "✓ Config loaded successfully\n";
    echo "  - SITE_ROOT: " . SITE_ROOT . "\n";
    echo "  - SITE_URL: " . SITE_URL . "\n";
    echo "  - DB_NAME: " . DB_NAME . "\n";
} catch (Exception $e) {
    echo "✗ Config error: " . $e->getMessage() . "\n";
    die();
}

// Test database connection
echo "\n4. Testing database connection...\n";
try {
    $db = db();
    echo "✓ Database connected\n";
} catch (Exception $e) {
    echo "✗ Database error: " . $e->getMessage() . "\n";
}

// Test session
echo "\n5. Testing session...\n";
echo "  - Session status: " . (session_status() === PHP_SESSION_ACTIVE ? 'Active' : 'Inactive') . "\n";
echo "  - Session ID: " . session_id() . "\n";

// Test functions
echo "\n6. Testing functions...\n";
try {
    echo "  - e() function: " . (function_exists('e') ? '✓' : '✗') . "\n";
    echo "  - formatPrice() function: " . (function_exists('formatPrice') ? '✓' : '✗') . "\n";
    echo "  - isLoggedIn() function: " . (function_exists('isLoggedIn') ? '✓' : '✗') . "\n";
    echo "  - getSetting() function: " . (function_exists('getSetting') ? '✓' : '✗') . "\n";
} catch (Exception $e) {
    echo "✗ Function error: " . $e->getMessage() . "\n";
}

// Test auth functions
echo "\n7. Testing auth...\n";
echo "  - Logged in: " . (isLoggedIn() ? 'Yes' : 'No') . "\n";
echo "  - Is admin: " . (isAdmin() ? 'Yes' : 'No') . "\n";

// Test SEO functions
echo "\n8. Testing SEO functions...\n";
if (file_exists(SITE_ROOT . '/includes/seo.php')) {
    require_once SITE_ROOT . '/includes/seo.php';
    echo "  - getMetaTitle() function: " . (function_exists('getMetaTitle') ? '✓' : '✗') . "\n";
    echo "  - getMetaDescription() function: " . (function_exists('getMetaDescription') ? '✓' : '✗') . "\n";
} else {
    echo "  ✗ SEO file not found\n";
}

// Test pages
echo "\n9. Testing page includes...\n";
$testPages = [
    'login' => 'pages/login.php',
    'register' => 'pages/register.php',
    'catalog' => 'pages/catalog.php',
    'cart' => 'pages/cart.php'
];

foreach ($testPages as $name => $path) {
    if (file_exists(SITE_ROOT . '/' . $path)) {
        echo "  ✓ $name page exists\n";
    } else {
        echo "  ✗ $name page missing\n";
    }
}

// Check error logs
echo "\n10. Checking error logs...\n";
$logFile = SITE_ROOT . '/logs/php_errors.log';
if (file_exists($logFile)) {
    $logs = file($logFile);
    if (count($logs) > 0) {
        echo "  Found " . count($logs) . " error entries. Last 5:\n";
        $recentLogs = array_slice($logs, -5);
        foreach ($recentLogs as $log) {
            echo "  " . trim($log) . "\n";
        }
    } else {
        echo "  ✓ No errors logged\n";
    }
} else {
    echo "  No error log file found\n";
}

echo "\n</pre>";
echo "<p><strong>Test complete!</strong></p>";
echo "<p><a href='/index.php'>Back to Home</a> | ";
echo "<a href='/pages/login.php'>Test Login Page</a> | ";
echo "<a href='/pages/register.php'>Test Register Page</a></p>";
