<?php
/**
 * Cart Debugging Script
 */
require_once 'config/config.php';

?><!DOCTYPE html>
<html>
<head>
    <title>Cart Debug</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #f5f5f5; }
        .section { margin: 20px 0; padding: 15px; background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .error { color: red; }
        .success { color: green; }
        .info { color: blue; }
        h1 { color: #333; }
        h2 { color: #666; border-bottom: 2px solid #ddd; padding-bottom: 10px; }
        pre { background: #f9f9f9; padding: 10px; border-radius: 4px; overflow-x: auto; }
        button { padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #0056b3; }
        input { padding: 8px; border: 1px solid #ddd; border-radius: 4px; margin-right: 10px; }
    </style>
</head>
<body>
    <h1>🔍 Cart Debugging Tool</h1>

    <div class="section">
        <h2>1. Session Status</h2>
        <?php
        session_start();
        echo '<p class="success">✓ Session ID: ' . session_id() . '</p>';
        echo '<p class="info">Session Status: ' . session_status() . '</p>';
        echo '<p class="info">Session Name: ' . session_name() . '</p>';
        echo '<pre>Session Data: ' . print_r($_SESSION, true) . '</pre>';
        ?>
    </div>

    <div class="section">
        <h2>2. Session Configuration</h2>
        <?php
        echo '<p><strong>Cookie Path:</strong> ' . ini_get('session.cookie_path') . '</p>';
        echo '<p><strong>Cookie Domain:</strong> ' . ini_get('session.cookie_domain') . '</p>';
        echo '<p><strong>Cookie Secure:</strong> ' . ini_get('session.cookie_secure') . '</p>';
        echo '<p><strong>Cookie HttpOnly:</strong> ' . ini_get('session.cookie_httponly') . '</p>';
        echo '<p><strong>Cookie SameSite:</strong> ' . ini_get('session.cookie_samesite') . '</p>';
        echo '<p><strong>Save Path:</strong> ' . ini_get('session.save_path') . '</p>';
        echo '<p><strong>Use Cookies:</strong> ' . ini_get('session.use_cookies') . '</p>';
        echo '<p><strong>Use Only Cookies:</strong> ' . ini_get('session.use_only_cookies') . '</p>';
        ?>
    </div>

    <div class="section">
        <h2>3. Database Connection</h2>
        <?php
        try {
            $db = db();
            echo '<p class="success">✓ Database connection successful</p>';

            // Check cart table
            $stmt = $db->query("SELECT COUNT(*) as count FROM cart WHERE session_id = '" . session_id() . "'");
            $result = $stmt->fetch();
            echo '<p class="info">Items in cart for current session: ' . $result['count'] . '</p>';

            // List cart items
            $stmt = $db->prepare("
                SELECT c.*, p.name, p.price
                FROM cart c
                LEFT JOIN products p ON c.product_id = p.id
                WHERE c.session_id = ?
            ");
            $stmt->execute([session_id()]);
            $cartItems = $stmt->fetchAll();
            echo '<pre>Cart Items: ' . print_r($cartItems, true) . '</pre>';

        } catch (Exception $e) {
            echo '<p class="error">✗ Database error: ' . $e->getMessage() . '</p>';
        }
        ?>
    </div>

    <div class="section">
        <h2>4. Test Cart API</h2>
        <form method="POST" action="/api/cart.php">
            <input type="hidden" name="action" value="count">
            <button type="submit">Test Count Endpoint</button>
        </form>

        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'count') {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, SITE_URL . '/api/cart.php?action=count');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());
            $response = curl_exec($ch);
            curl_close($ch);

            echo '<p><strong>API Response:</strong></p>';
            echo '<pre>' . htmlspecialchars($response) . '</pre>';
        }
        ?>
    </div>

    <div class="section">
        <h2>5. PHP Error Log</h2>
        <?php
        $logFile = ini_get('error_log');
        echo '<p><strong>Log file:</strong> ' . $logFile . '</p>';
        if (file_exists($logFile)) {
            $lines = file($logFile);
            echo '<pre><strong>Recent errors (last 20):</strong>';
            foreach (array_slice($lines, -20) as $line) {
                echo htmlspecialchars($line);
            }
            echo '</pre>';
        } else {
            echo '<p class="error">Log file not found</p>';
        }
        ?>
    </div>

    <div class="section">
        <h2>6. Test Add to Cart</h2>
        <form method="POST" action="/api/cart.php" id="cartTest">
            <input type="hidden" name="action" value="add">
            <label>Product ID: <input type="number" name="product_id" value="1" required></label><br><br>
            <label>Quantity: <input type="number" name="quantity" value="1" required></label><br><br>
            <button type="submit">Add to Cart</button>
        </form>
        <p id="result"></p>

        <script>
        document.getElementById('cartTest').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);

            try {
                const response = await fetch('/api/cart.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                document.getElementById('result').innerHTML =
                    '<strong>Success:</strong> ' + data.success + ', <strong>Message:</strong> ' + data.message + ', <strong>Count:</strong> ' + data.count;
            } catch (error) {
                document.getElementById('result').innerHTML = '<strong>Error:</strong> ' + error.message;
            }
        });
        </script>
    </div>

    <div class="section">
        <h2>7. Browser Console Check</h2>
        <p>Open browser console (F12) and check for JavaScript errors.</p>
        <button onclick="checkCookies()">Check Cookies</button>
        <div id="cookieResult"></div>

        <script>
        function checkCookies() {
            const result = document.getElementById('cookieResult');
            result.innerHTML = '<strong>Cookies:</strong><br>';
            document.cookie.split(';').forEach(cookie => {
                result.innerHTML += cookie + '<br>';
            });
        }
        </script>
    </div>
</body>
</html>
