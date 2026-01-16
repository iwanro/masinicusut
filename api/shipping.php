<?php
/**
 * Shipping API - AJAX Endpoints
 * SUNDARI TOP STAR S.R.L.
 * Get shipping cost based on county/city
 */
require_once '../config/config.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

/**
 * Get shipping cost for a location
 */
if ($action === 'get_cost') {
    $county = trim($_GET['county'] ?? '');
    $city = trim($_GET['city'] ?? '');

    if (empty($county)) {
        echo json_encode([
            'success' => false,
            'message' => 'Județul este obligatoriu'
        ]);
        exit;
    }

    $cost = getShippingCost($county, $city);

    echo json_encode([
        'success' => true,
        'cost' => $cost,
        'county' => $county,
        'city' => $city
    ]);
    exit;
}

/**
 * Calculate shipping cost based on county and city
 * @param string $county
 * @param string|null $city
 * @return float
 */
function getShippingCost($county, $city = null) {
    $db = db();

    // Caută taxă specifică (județ + localitate)
    if (!empty($city)) {
        $stmt = $db->prepare("
            SELECT shipping_cost
            FROM shipping_rates
            WHERE county = ? AND city = ? AND is_active = 1
            LIMIT 1
        ");
        $stmt->execute([$county, $city]);
        $result = $stmt->fetch();
        if ($result) {
            return floatval($result['shipping_cost']);
        }
    }

    // Caută taxă pentru județ
    $stmt = $db->prepare("
        SELECT shipping_cost
        FROM shipping_rates
        WHERE county = ? AND city IS NULL AND is_active = 1
        LIMIT 1
    ");
    $stmt->execute([$county]);
    $result = $stmt->fetch();
    if ($result) {
        return floatval($result['shipping_cost']);
    }

    // Fallback la taxă default din settings
    return floatval(getSetting('shipping_cost', 15));
}

// Invalid action
echo json_encode([
    'success' => false,
    'message' => 'Acțiune invalidă'
]);
