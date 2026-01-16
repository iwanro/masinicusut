<?php
/**
 * Cart API
 * SUNDARI TOP STAR S.R.L.
 * Handles AJAX requests for cart operations
 */
require_once '../config/config.php';

// Enable error logging for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display to browser
ini_set('log_errors', 1);
ini_set('error_log', SITE_ROOT . '/logs/cart_debug.log');

header('Content-Type: application/json');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$db = db();

// Log all cart actions
$logMessage = date('Y-m-d H:i:s') . " - Action: $action, Session: " . session_id() . ", POST: " . print_r($_POST, true);
error_log($logMessage);

// Get or create session ID
$sessionId = session_id();
$userId = isLoggedIn() ? intval(getCurrentUserId()) : null;

/**
 * Get cart count
 */
if ($action === 'count') {
    $sql = "SELECT SUM(quantity) as total FROM cart WHERE "
         . ($userId ? "user_id = ?" : "session_id = ?");
    $stmt = $db->prepare($sql);
    $stmt->execute([$userId ?? $sessionId]);
    $count = $stmt->fetchColumn();

    echo json_encode([
        'success' => true,
        'count' => intval($count) ?: 0
    ]);
    exit;
}

/**
 * Add item to cart
 */
if ($action === 'add') {
    $productId = intval($_POST['product_id'] ?? 0);
    $quantity = intval($_POST['quantity'] ?? 1);

    if ($productId <= 0 || $quantity <= 0) {
        echo json_encode(['success' => false, 'message' => 'Date invalide.']);
        exit;
    }

    // Check if product exists and is in stock
    $stmt = $db->prepare("SELECT id, stock FROM products WHERE id = ? AND is_active = 1");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Produsul nu există.']);
        exit;
    }

    if ($product['stock'] < $quantity) {
        echo json_encode(['success' => false, 'message' => 'Stoc insuficient.']);
        exit;
    }

    // Check if item already in cart
    $sql = "SELECT id, quantity FROM cart WHERE product_id = ? AND "
         . ($userId ? "user_id = ?" : "session_id = ?");
    $stmt = $db->prepare($sql);
    $stmt->execute([$productId, $userId ?? $sessionId]);
    $cartItem = $stmt->fetch();

    if ($cartItem) {
        // Update quantity
        $newQuantity = $cartItem['quantity'] + $quantity;
        if ($newQuantity > $product['stock']) {
            echo json_encode(['success' => false, 'message' => 'Stoc insuficient.']);
            exit;
        }

        $stmt = $db->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
        $stmt->execute([$newQuantity, $cartItem['id']]);
    } else {
        // Add new item
        $sql = "INSERT INTO cart (user_id, session_id, product_id, quantity) VALUES (?, ?, ?, ?)"
             . " ON DUPLICATE KEY UPDATE quantity = quantity + ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            $userId,
            $userId ? null : $sessionId,
            $productId,
            $quantity,
            $quantity
        ]);
    }

    // Get cart count
    $sql = "SELECT SUM(quantity) as total FROM cart WHERE "
         . ($userId ? "user_id = ?" : "session_id = ?");
    $stmt = $db->prepare($sql);
    $stmt->execute([$userId ?? $sessionId]);
    $count = $stmt->fetchColumn();

    echo json_encode([
        'success' => true,
        'message' => 'Produs adăugat în coș!',
        'count' => intval($count) ?: 0
    ]);
    exit;
}

/**
 * Update cart item quantity
 */
if ($action === 'update') {
    $itemId = intval($_POST['item_id'] ?? 0);
    $quantity = intval($_POST['quantity'] ?? 1);

    if ($itemId <= 0 || $quantity < 0) {
        echo json_encode(['success' => false, 'message' => 'Date invalide.']);
        exit;
    }

    // Get cart item with product stock
    $stmt = $db->prepare("
        SELECT c.id, c.product_id, c.quantity, p.stock
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.id = ? AND " . ($userId ? "c.user_id = ?" : "c.session_id = ?")
    );
    $stmt->execute([$itemId, $userId ?? $sessionId]);
    $cartItem = $stmt->fetch();

    if (!$cartItem) {
        echo json_encode(['success' => false, 'message' => 'Produsul nu există în coș.']);
        exit;
    }

    if ($quantity > $cartItem['stock']) {
        echo json_encode(['success' => false, 'message' => 'Stoc insuficient.']);
        exit;
    }

    if ($quantity == 0) {
        // Remove item
        $stmt = $db->prepare("DELETE FROM cart WHERE id = ?");
        $stmt->execute([$itemId]);
    } else {
        // Update quantity
        $stmt = $db->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
        $stmt->execute([$quantity, $itemId]);
    }

    echo json_encode(['success' => true, 'message' => 'Coș actualizat!']);
    exit;
}

/**
 * Remove item from cart
 */
if ($action === 'remove') {
    $itemId = intval($_POST['item_id'] ?? 0);

    if ($itemId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Date invalide.']);
        exit;
    }

    $stmt = $db->prepare("DELETE FROM cart WHERE id = ? AND " . ($userId ? "user_id = ?" : "session_id = ?"));
    $stmt->execute([$itemId, $userId ?? $sessionId]);

    echo json_encode(['success' => true, 'message' => 'Produs eliminat din coș.']);
    exit;
}

/**
 * Clear entire cart
 */
if ($action === 'clear') {
    $stmt = $db->prepare("DELETE FROM cart WHERE " . ($userId ? "user_id = ?" : "session_id = ?"));
    $stmt->execute([$userId ?? $sessionId]);

    echo json_encode(['success' => true, 'message' => 'Coș golit.']);
    exit;
}

// Invalid action
echo json_encode(['success' => false, 'message' => 'Acțiune invalidă.']);
