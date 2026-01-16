<?php
/**
 * Test Cart API Fix
 * Run this to test if cart API is working
 */
require_once 'config/config.php';

echo "<h1>Cart API Test</h1>";
echo "<pre>";

// Test 1: Database Connection
echo "\n1. Database Connection\n";
try {
    $db = db();
    echo "✓ Database connection successful\n";
    
    // Check cart table structure
    $stmt = $db->query("DESCRIBE cart");
    echo "\nCart table structure:\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  - {$row['Field']}: {$row['Type']} " . 
             ($row['Null'] === 'YES' ? 'NULL' : 'NOT NULL') . 
             ($row['Default'] !== null ? " DEFAULT {$row['Default']}" : '') . "\n";
    }
} catch (Exception $e) {
    echo "✗ Database error: " . $e->getMessage() . "\n";
}

// Test 2: Session
echo "\n2. Session Test\n";
echo "Session ID: " . session_id() . "\n";
echo "User logged in: " . (isLoggedIn() ? 'Yes' : 'No') . "\n";
if (isLoggedIn()) {
    echo "User ID: " . getCurrentUserId() . "\n";
}

// Test 3: Cart Count API
echo "\n3. Test Cart Count API\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, SITE_URL . '/api/cart.php?action=count');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "Response: $response\n";

if ($httpCode === 200) {
    $data = json_decode($response, true);
    if ($data && isset($data['success'])) {
        echo "✓ Cart count: " . ($data['count'] ?? 0) . "\n";
    } else {
        echo "✗ Invalid JSON response\n";
    }
} else {
    echo "✗ HTTP error\n";
}

// Test 4: Check for products
echo "\n4. Available Products\n";
try {
    $stmt = $db->query("SELECT id, name, price, stock FROM products WHERE is_active = 1 LIMIT 5");
    $products = $stmt->fetchAll();
    if ($products) {
        echo "✓ Found " . count($products) . " products:\n";
        foreach ($products as $p) {
            echo "  - ID: {$p['id']}, {$p['name']}, {$p['price']} RON, Stock: {$p['stock']}\n";
        }
    } else {
        echo "✗ No products found\n";
    }
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

// Test 5: Check error logs
echo "\n5. Recent Error Logs\n";
$logFile = SITE_ROOT . '/logs/cart_debug.log';
if (file_exists($logFile)) {
    $logs = file($logFile);
    $recentLogs = array_slice($logs, -10);
    echo "Last 10 log entries:\n";
    foreach ($recentLogs as $log) {
        echo "  " . trim($log) . "\n";
    }
} else {
    echo "No log file found at: $logFile\n";
}

// Test 6: Test Add to Cart (if products exist)
if (!empty($products)) {
    echo "\n6. Test Add to Cart\n";
    $testProduct = $products[0];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, SITE_URL . '/api/cart.php');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'action' => 'add',
        'product_id' => $testProduct['id'],
        'quantity' => 1
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "HTTP Code: $httpCode\n";
    echo "Response: $response\n";
    
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        if ($data && isset($data['success'])) {
            if ($data['success']) {
                echo "✓ Product added successfully!\n";
            } else {
                echo "✗ Failed: " . ($data['message'] ?? 'Unknown error') . "\n";
            }
        } else {
            echo "✗ Invalid JSON response\n";
        }
    } else {
        echo "✗ HTTP error\n";
    }
}

echo "\n</pre>";
echo "<p><a href='/index.php'>Back to Home</a></p>";
