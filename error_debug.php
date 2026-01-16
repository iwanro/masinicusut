<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>DEBUG INFO - DETALIAT</h1>";
echo "<style>body{font-family:Arial,sans-serif;padding:20px;} h2{color:#333;border-bottom:2px solid #007cba;padding-bottom:10px;} .ok{color:green;font-weight:bold;} .err{color:red;font-weight:bold;} pre{background:#f5f5f5;padding:10px;border:1px solid #ddd;}</style>";

// Test 1
echo "<h2>1. PHP Version:</h2>";
echo phpversion();
echo " - " . (version_compare(phpversion(), '8.0.0') >= 0 ? '<span class="ok">✓ PHP 8+</span>' : '<span class="err">✗ PHP vechi</span>');

// Test 2
echo "<h2>2. Extensions:</h2>";
$exts = ['mysqli', 'pdo', 'pdo_mysql', 'mysqlnd'];
foreach ($exts as $ext) {
    $loaded = extension_loaded($ext);
    echo "$ext: " . ($loaded ? '<span class="ok">✓ LOADED</span>' : '<span class="err">✗ NOT LOADED</span>') . "<br>";
}

// Test 3 - Database constants
echo "<h2>3. Database Constants:</h2>";
try {
    if (!defined('SITE_ROOT')) {
        define('SITE_ROOT', __DIR__);
    }

    require_once __DIR__ . '/config/config.php';

    echo "DB_HOST: " . DB_HOST . "<br>";
    echo "DB_NAME: " . DB_NAME . "<br>";
    echo "DB_USER: " . DB_USER . "<br>";
    echo "DB_PASS: " . (defined('DB_PASS') ? '***SET***' : 'NOT DEFINED') . "<br>";
    echo "<span class='ok'>✓ Constants loaded</span><br>";
} catch (Exception $e) {
    echo "<span class='err'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</span><br>";
}

// Test 4 - Database connection attempt
echo "<h2>4. Database Connection Step-by-Step:</h2>";

try {
    echo "Step 1: Loading database.php...<br>";
    require_once __DIR__ . '/config/database.php';
    echo "<span class='ok'>✓ database.php loaded</span><br>";

    echo "Step 2: Calling db()...<br>";
    $db = db();
    echo "<span class='ok'>✓ db() returned something</span><br>";

    echo "Step 3: Checking type...<br>";
    if (is_object($db)) {
        echo "Type: " . get_class($db) . "<br>";
        echo "<span class='ok'>✓ Got database object</span><br>";

        // Test simple query
        echo "Step 4: Testing query...<br>";
        try {
            $result = $db->query("SELECT 1");
            if ($result) {
                echo "<span class='ok'>✓✓✓ DATABASE CONNECTION WORKS!</span><br>";
            }
        } catch (Exception $qe) {
            echo "<span class='err'>✗ Query failed: " . htmlspecialchars($qe->getMessage()) . "</span><br>";
        }
    } else {
        echo "<span class='err'>✗ db() did not return object</span><br>";
    }

} catch (Throwable $e) {
    echo "<span class='err'>✗ ERROR: " . htmlspecialchars($e->getMessage()) . "</span><br>";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "<br>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

// Test 5 - Check index.php
echo "<h2>5. Test index.php loading:</h2>";
echo "<a href='/' target='_blank'>Click here to test main site</a><br>";
echo "<small>If site is blank, check browser console (F12) for errors</small>";
?>
