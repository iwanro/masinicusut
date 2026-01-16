<?php
/**
 * Cart Cleanup Script
 * SUNDARI TOP STAR S.R.L.
 * Rulează periodic pentru a șterge coșurile vechi (sesiuni expirate)
 *
 * Utilizare manuală: php cleanup_cart.php
 * Utilizare cron: 0 2 * * * php /calea/catre/proiect/cleanup_cart.php
 */

require_once 'config/config.php';

echo "=== Cart Cleanup Script ===\n";
echo "Start time: " . date('Y-m-d H:i:s') . "\n";

try {
    $daysOld = 30; // Șterge coșurile mai vechi de 30 de zile

    $deleted = cleanupOldCartEntries($daysOld);

    echo "Deleted $deleted old cart entries (older than $daysOld days)\n";

    // Log cleanup
    $logFile = SITE_ROOT . '/logs/cart_cleanup.log';
    $logMessage = date('Y-m-d H:i:s') . " - Deleted $deleted old cart entries (older than $daysOld days)\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);

    echo "Cleanup completed successfully!\n";

} catch (Exception $e) {
    echo "Error during cleanup: " . $e->getMessage() . "\n";
    error_log("Cart cleanup error: " . $e->getMessage());
    exit(1);
}

echo "End time: " . date('Y-m-d H:i:s') . "\n";
echo "==========================\n";