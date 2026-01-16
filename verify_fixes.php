<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificare Fixes - Piese Mașini Cusut</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f8fafc;
            padding: 20px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 30px;
        }
        h1 {
            color: #0f172a;
            margin-bottom: 10px;
            font-size: 28px;
        }
        .subtitle {
            color: #64748b;
            margin-bottom: 30px;
        }
        .test-section {
            margin-bottom: 30px;
            padding: 20px;
            background: #f8fafc;
            border-radius: 8px;
            border-left: 4px solid #3b82f6;
        }
        .test-section h2 {
            color: #1e293b;
            font-size: 18px;
            margin-bottom: 15px;
        }
        .test-item {
            display: flex;
            align-items: center;
            padding: 10px;
            margin-bottom: 8px;
            background: white;
            border-radius: 6px;
        }
        .status {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            margin-right: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
        }
        .status.pass {
            background: #10b981;
            color: white;
        }
        .status.fail {
            background: #ef4444;
            color: white;
        }
        .status.warn {
            background: #f59e0b;
            color: white;
        }
        .test-name {
            flex: 1;
            color: #1e293b;
        }
        .test-detail {
            color: #64748b;
            font-size: 14px;
        }
        .summary {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 30px;
        }
        .summary-card {
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        .summary-card.pass {
            background: #d1fae5;
            color: #065f46;
        }
        .summary-card.fail {
            background: #fee2e2;
            color: #991b1b;
        }
        .summary-card.warn {
            background: #fef3c7;
            color: #92400e;
        }
        .summary-card .number {
            font-size: 32px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .summary-card .label {
            font-size: 14px;
            opacity: 0.8;
        }
        .actions {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #e2e8f0;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #f97316;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            margin-right: 10px;
            font-weight: 600;
        }
        .btn:hover {
            background: #ea580c;
        }
        .btn-secondary {
            background: #64748b;
        }
        .btn-secondary:hover {
            background: #475569;
        }
        pre {
            background: #1e293b;
            color: #e2e8f0;
            padding: 15px;
            border-radius: 6px;
            overflow-x: auto;
            font-size: 13px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Verificare Automată Fixes</h1>
        <p class="subtitle">Status: <?= date('d.m.Y H:i:s') ?></p>

        <?php
        require_once 'config/config.php';
        
        $tests = [];
        $passCount = 0;
        $failCount = 0;
        $warnCount = 0;

        // Test 1: Database Connection
        try {
            $db = db();
            $tests[] = ['name' => 'Database Connection', 'status' => 'pass', 'detail' => 'Connected successfully'];
            $passCount++;
        } catch (Exception $e) {
            $tests[] = ['name' => 'Database Connection', 'status' => 'fail', 'detail' => $e->getMessage()];
            $failCount++;
        }

        // Test 2: Cart Table Structure
        try {
            $stmt = $db->query("DESCRIBE cart");
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $hasSessionId = false;
            $sessionIdNullable = false;
            
            foreach ($columns as $col) {
                if ($col['Field'] === 'session_id') {
                    $hasSessionId = true;
                    $sessionIdNullable = ($col['Null'] === 'YES');
                }
            }
            
            if ($hasSessionId && $sessionIdNullable) {
                $tests[] = ['name' => 'Cart Table Structure', 'status' => 'pass', 'detail' => 'session_id column configured correctly'];
                $passCount++;
            } else {
                $tests[] = ['name' => 'Cart Table Structure', 'status' => 'fail', 'detail' => 'session_id needs to allow NULL'];
                $failCount++;
            }
        } catch (Exception $e) {
            $tests[] = ['name' => 'Cart Table Structure', 'status' => 'fail', 'detail' => $e->getMessage()];
            $failCount++;
        }

        // Test 3: Session
        if (session_status() === PHP_SESSION_ACTIVE && session_id()) {
            $tests[] = ['name' => 'PHP Session', 'status' => 'pass', 'detail' => 'Session ID: ' . substr(session_id(), 0, 10) . '...'];
            $passCount++;
        } else {
            $tests[] = ['name' => 'PHP Session', 'status' => 'fail', 'detail' => 'Session not active'];
            $failCount++;
        }

        // Test 4: Cart API Endpoint
        $cartApiUrl = SITE_URL . '/api/cart.php?action=count';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $cartApiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $data = json_decode($response, true);
            if ($data && isset($data['success'])) {
                $tests[] = ['name' => 'Cart API', 'status' => 'pass', 'detail' => 'Returns valid JSON (count: ' . ($data['count'] ?? 0) . ')'];
                $passCount++;
            } else {
                $tests[] = ['name' => 'Cart API', 'status' => 'fail', 'detail' => 'Invalid JSON response'];
                $failCount++;
            }
        } else {
            $tests[] = ['name' => 'Cart API', 'status' => 'fail', 'detail' => 'HTTP ' . $httpCode];
            $failCount++;
        }

        // Test 5: Products Available
        try {
            $stmt = $db->query("SELECT COUNT(*) FROM products WHERE is_active = 1");
            $count = $stmt->fetchColumn();
            if ($count > 0) {
                $tests[] = ['name' => 'Products Available', 'status' => 'pass', 'detail' => $count . ' active products'];
                $passCount++;
            } else {
                $tests[] = ['name' => 'Products Available', 'status' => 'warn', 'detail' => 'No active products'];
                $warnCount++;
            }
        } catch (Exception $e) {
            $tests[] = ['name' => 'Products Available', 'status' => 'fail', 'detail' => $e->getMessage()];
            $failCount++;
        }

        // Test 6: Brands Available
        try {
            $stmt = $db->query("SELECT COUNT(*) FROM categories WHERE type = 'brand' AND is_active = 1");
            $count = $stmt->fetchColumn();
            if ($count > 0) {
                $tests[] = ['name' => 'Brands Available', 'status' => 'pass', 'detail' => $count . ' active brands'];
                $passCount++;
            } else {
                $tests[] = ['name' => 'Brands Available', 'status' => 'warn', 'detail' => 'No active brands'];
                $warnCount++;
            }
        } catch (Exception $e) {
            $tests[] = ['name' => 'Brands Available', 'status' => 'fail', 'detail' => $e->getMessage()];
            $failCount++;
        }

        // Test 7: Functions Loaded
        $requiredFunctions = ['e', 'formatPrice', 'isLoggedIn', 'getBrands', 'getSetting'];
        $missingFunctions = [];
        foreach ($requiredFunctions as $func) {
            if (!function_exists($func)) {
                $missingFunctions[] = $func;
            }
        }
        
        if (empty($missingFunctions)) {
            $tests[] = ['name' => 'Required Functions', 'status' => 'pass', 'detail' => 'All functions loaded'];
            $passCount++;
        } else {
            $tests[] = ['name' => 'Required Functions', 'status' => 'fail', 'detail' => 'Missing: ' . implode(', ', $missingFunctions)];
            $failCount++;
        }

        // Test 8: Page Files Exist
        $pages = ['login.php', 'register.php', 'catalog.php', 'cart.php', 'product.php'];
        $missingPages = [];
        foreach ($pages as $page) {
            if (!file_exists(SITE_ROOT . '/pages/' . $page)) {
                $missingPages[] = $page;
            }
        }
        
        if (empty($missingPages)) {
            $tests[] = ['name' => 'Page Files', 'status' => 'pass', 'detail' => 'All pages exist'];
            $passCount++;
        } else {
            $tests[] = ['name' => 'Page Files', 'status' => 'fail', 'detail' => 'Missing: ' . implode(', ', $missingPages)];
            $failCount++;
        }

        // Test 9: Logs Directory Writable
        $logsDir = SITE_ROOT . '/logs';
        if (is_dir($logsDir) && is_writable($logsDir)) {
            $tests[] = ['name' => 'Logs Directory', 'status' => 'pass', 'detail' => 'Writable'];
            $passCount++;
        } else {
            $tests[] = ['name' => 'Logs Directory', 'status' => 'warn', 'detail' => 'Not writable or missing'];
            $warnCount++;
        }

        // Test 10: Recent Errors
        $errorLog = SITE_ROOT . '/logs/php_errors.log';
        if (file_exists($errorLog)) {
            $errors = file($errorLog);
            $recentErrors = array_slice($errors, -5);
            if (count($recentErrors) > 0) {
                $tests[] = ['name' => 'Recent Errors', 'status' => 'warn', 'detail' => count($errors) . ' total errors logged'];
                $warnCount++;
            } else {
                $tests[] = ['name' => 'Recent Errors', 'status' => 'pass', 'detail' => 'No errors'];
                $passCount++;
            }
        } else {
            $tests[] = ['name' => 'Recent Errors', 'status' => 'pass', 'detail' => 'No error log'];
            $passCount++;
        }
        ?>

        <!-- Summary Cards -->
        <div class="summary">
            <div class="summary-card pass">
                <div class="number"><?= $passCount ?></div>
                <div class="label">Passed</div>
            </div>
            <div class="summary-card fail">
                <div class="number"><?= $failCount ?></div>
                <div class="label">Failed</div>
            </div>
            <div class="summary-card warn">
                <div class="number"><?= $warnCount ?></div>
                <div class="label">Warnings</div>
            </div>
        </div>

        <!-- Test Results -->
        <div class="test-section">
            <h2>📋 Test Results</h2>
            <?php foreach ($tests as $test): ?>
                <div class="test-item">
                    <div class="status <?= $test['status'] ?>">
                        <?= $test['status'] === 'pass' ? '✓' : ($test['status'] === 'fail' ? '✗' : '!') ?>
                    </div>
                    <div class="test-name"><?= $test['name'] ?></div>
                    <div class="test-detail"><?= htmlspecialchars($test['detail']) ?></div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($failCount > 0): ?>
        <div class="test-section" style="border-left-color: #ef4444;">
            <h2>⚠️ Action Required</h2>
            <p style="margin-bottom: 15px;">Some tests failed. Please follow these steps:</p>
            
            <?php if (in_array('Cart Table Structure', array_column($tests, 'name'))): ?>
            <div style="margin-bottom: 15px;">
                <strong>1. Fix Cart Table:</strong>
                <pre>ALTER TABLE cart 
MODIFY COLUMN session_id VARCHAR(128) DEFAULT NULL,
MODIFY COLUMN user_id INT DEFAULT NULL;

ALTER TABLE cart DROP INDEX IF EXISTS unique_product;
ALTER TABLE cart ADD UNIQUE KEY unique_product (user_id, session_id, product_id);</pre>
            </div>
            <?php endif; ?>
            
            <p>2. Clear browser cache (Ctrl+Shift+R)</p>
            <p>3. Refresh this page to verify fixes</p>
        </div>
        <?php endif; ?>

        <!-- Actions -->
        <div class="actions">
            <a href="/index.php" class="btn">← Back to Home</a>
            <a href="/test_cart_fix.php" class="btn btn-secondary">Detailed Cart Test</a>
            <a href="/check_errors.php" class="btn btn-secondary">Check Errors</a>
            <a href="?refresh=1" class="btn btn-secondary">🔄 Refresh Tests</a>
        </div>
    </div>
</body>
</html>
