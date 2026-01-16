<?php
/**
 * Database Migration - Fix Cart Table
 * Adds session_id column if missing
 */
require_once 'config/config.php';

echo "<h1>Migration - Fix Cart Table</h1>";

try {
    $db = db();

    // Check current table structure
    echo "<h2>1. Checking current table structure...</h2>";
    $stmt = $db->query("SHOW COLUMNS FROM cart");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<pre>";
    echo "Current columns:\n";
    foreach ($columns as $col) {
        echo "- {$col['Field']} ({$col['Type']})\n";
    }
    echo "</pre>";

    // Check if session_id exists
    $hasSessionId = false;
    $hasUserId = false;

    foreach ($columns as $col) {
        if ($col['Field'] === 'session_id') $hasSessionId = true;
        if ($col['Field'] === 'user_id') $hasUserId = true;
    }

    // Add session_id column if missing
    if (!$hasSessionId) {
        echo "<h2>2. Adding session_id column...</h2>";
        $sql = "ALTER TABLE cart ADD COLUMN session_id VARCHAR(128) DEFAULT NULL";
        $db->exec($sql);
        echo "<p style='color: green;'>✓ session_id column added</p>";
    } else {
        echo "<h2>2. session_id column already exists</h2>";
    }

    // Make user_id nullable if it's NOT NULL
    if ($hasUserId) {
        echo "<h2>3. Checking user_id constraints...</h2>";
        $stmt = $db->query("SHOW CREATE TABLE cart");
        $tableDef = $stmt->fetch();
        $createSql = $tableDef['Create Table'];

        if (strpos($createSql, 'user_id') !== false && strpos($createSql, 'NOT NULL') !== false) {
            echo "<p>Making user_id nullable...</p>";
            $sql = "ALTER TABLE cart MODIFY COLUMN user_id INT DEFAULT NULL";
            $db->exec($sql);
            echo "<p style='color: green;'>✓ user_id is now nullable</p>";
        } else {
            echo "<p>user_id is already nullable or doesn't exist</p>";
        }
    } else {
        echo "<h2>3. Adding user_id column...</h2>";
        $sql = "ALTER TABLE cart ADD COLUMN user_id INT DEFAULT NULL";
        $db->exec($sql);
        echo "<p style='color: green;'>✓ user_id column added</p>";
    }

    // Add index on session_id for performance
    echo "<h2>4. Adding indexes...</h2>";
    try {
        $sql = "CREATE INDEX idx_session_id ON cart(session_id)";
        $db->exec($sql);
        echo "<p style='color: green;'>✓ Index on session_id created</p>";
    } catch (Exception $e) {
        echo "<p>Index on session_id already exists or error: " . $e->getMessage() . "</p>";
    }

    // Show final structure
    echo "<h2>5. Final table structure:</h2>";
    $stmt = $db->query("SHOW COLUMNS FROM cart");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<pre>";
    echo "Final columns:\n";
    foreach ($columns as $col) {
        $null = $col['Null'] === 'NO' ? 'NOT NULL' : 'NULL';
        $key = $col['Key'] ? ", {$col['Key']}" : '';
        echo "- {$col['Field']} ({$col['Type']}) {$null}{$key}\n";
    }
    echo "</pre>";

    echo "<h2 style='color: green;'>✓ Migration complete!</h2>";
    echo "<p><a href='/index.php'>Back to site</a></p>";

} catch (Exception $e) {
    echo "<h2 style='color: red;'>✗ Error: " . $e->getMessage() . "</h2>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
