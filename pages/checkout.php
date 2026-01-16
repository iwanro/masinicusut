<?php
/**
 * Checkout Page
 * SUNDARI TOP STAR S.R.L.
 */
require_once '../config/config.php';

requireAuth();

$pageTitle = 'Checkout';

$db = db();
$userId = getCurrentUserId();
$user = getCurrentUser();

// Get cart items
$stmt = $db->prepare("
    SELECT c.id as cart_id, c.quantity, p.id, p.name, p.slug, p.price, p.stock
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = ?
");
$stmt->execute([$userId]);
$cartItems = $stmt->fetchAll();

if (empty($cartItems)) {
    setFlash('error', 'Coșul este gol.');
    redirect('/pages/catalog.php');
}

// Helper function to get shipping cost
function getShippingCostFromDB($county, $city = null) {
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

// Calculate totals
$totalAmount = 0;
foreach ($cartItems as $item) {
    $totalAmount += $item['price'] * $item['quantity'];
}

$freeShipping = floatval(getSetting('free_shipping_threshold', 200));

// Get shipping cost based on user's county (if saved) or default
$selectedCounty = $_GET['county'] ?? $user['county'] ?? '';
$selectedCity = $_GET['city'] ?? $user['city'] ?? '';
$shippingCost = 0;

if (!empty($selectedCounty)) {
    $calculatedCost = getShippingCostFromDB($selectedCounty, $selectedCity);
    $shippingCost = $totalAmount >= $freeShipping ? 0 : $calculatedCost;
} else {
    $shippingCost = $totalAmount >= $freeShipping ? 0 : floatval(getSetting('shipping_cost', 15));
}

$finalTotal = $totalAmount + $shippingCost;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        setFlash('error', 'Eroare de securitate. Încearcă din nou.');
        redirect('/pages/checkout.php');
    }

    $shippingName = trim($_POST['shipping_name'] ?? '');
    $shippingPhone = trim($_POST['shipping_phone'] ?? '');
    $shippingAddress = trim($_POST['shipping_address'] ?? '');
    $shippingCity = trim($_POST['shipping_city'] ?? '');
    $shippingCounty = trim($_POST['shipping_county'] ?? '');
    $shippingPostalCode = trim($_POST['shipping_postal_code'] ?? '');
    $notes = trim($_POST['notes'] ?? '');

    // Basic validation
    if (empty($shippingName) || empty($shippingPhone) || empty($shippingAddress) || empty($shippingCity)) {
        setFlash('error', 'Te rugăm completează toate câmpurile obligatorii.');
        redirect('/pages/checkout.php');
    }

    try {
        $db->beginTransaction();

        // Create order
        $orderNumber = generateOrderNumber();
        $stmt = $db->prepare("
            INSERT INTO orders (user_id, order_number, status, total_amount,
                              shipping_name, shipping_phone, shipping_address,
                              shipping_city, shipping_county, shipping_postal_code, notes)
            VALUES (?, ?, 'pending', ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $userId,
            $orderNumber,
            $finalTotal,
            $shippingName,
            $shippingPhone,
            $shippingAddress,
            $shippingCity,
            $shippingCounty,
            $shippingPostalCode,
            $notes
        ]);
        $orderId = $db->lastInsertId();

        // Add order items
        $stmt = $db->prepare("
            INSERT INTO order_items (order_id, product_id, product_name, quantity, price, subtotal)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        foreach ($cartItems as $item) {
            $subtotal = $item['price'] * $item['quantity'];
            $stmt->execute([
                $orderId,
                $item['id'],
                $item['name'],
                $item['quantity'],
                $item['price'],
                $subtotal
            ]);
        }

        // Clear cart
        $stmt = $db->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->execute([$userId]);

        $db->commit();

        // Trimite email notificări
        require_once INCLUDES_PATH . '/email_service.php';
        sendOrderEmails($orderId, $orderNumber);

        setFlash('success', 'Comanda a fost plasată cu succes! Număr comandă: ' . $orderNumber);
        redirect('/pages/account.php?order=' . $orderId);

    } catch (PDOException $e) {
        $db->rollBack();
        error_log("Checkout error: " . $e->getMessage());
        setFlash('error', 'Eroare la plasarea comenzii. Încearcă din nou.');
        redirect('/pages/checkout.php');
    }
}

include SITE_ROOT . '/includes/header.php';
?>

<div class="container">
    <h1>Finalizare Comandă</h1>

    <div class="checkout-layout">
        <!-- Checkout Form -->
        <div class="checkout-form">
            <form method="POST">
                <?= getCsrfField() ?>

                <h2>Date Livrare</h2>

                <div class="form-group">
                    <label for="shipping_name">Nume Complet *</label>
                    <input type="text" id="shipping_name" name="shipping_name"
                           class="form-control" value="<?= e($user['name'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label for="shipping_phone">Telefon *</label>
                    <input type="tel" id="shipping_phone" name="shipping_phone"
                           class="form-control" value="<?= e($user['phone'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label for="shipping_address">Adresă *</label>
                    <input type="text" id="shipping_address" name="shipping_address"
                           class="form-control" value="<?= e($user['address'] ?? '') ?>" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="shipping_city">Oraș *</label>
                        <input type="text" id="shipping_city" name="shipping_city"
                               class="form-control" value="<?= e($user['city'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="shipping_county">Județ *</label>
                        <select id="shipping_county" name="shipping_county" class="form-control" required onchange="updateShippingCost()">
                            <option value="">Selectează județul...</option>
                            <?php
                            $judete = [
                                'Alba', 'Arad', 'Argeș', 'Bacău', 'Bihor', 'Bistrița-Năsăud',
                                'Botoșani', 'Brașov', 'Brăila', 'Buzău', 'Caraș-Severin',
                                'Călărași', 'Cluj', 'Constanța', 'Covasna', 'Dâmbovița',
                                'Dolj', 'Galați', 'Gorj', 'Harghita', 'Hunedoara', 'Ialomița',
                                'Iași', 'Ilfov', 'Maramureș', 'Mehedinți', 'Mureș', 'Neamț',
                                'Olt', 'Prahova', 'Sălaj', 'Satu Mare', 'Sibiu', 'Suceava',
                                'Teleorman', 'Timiș', 'Tulcea', 'Vaslui', 'Vâlcea', 'Vrancea'
                            ];
                            foreach ($judete as $judet):
                            ?>
                                <option value="<?= $judet ?>" <?= ($selectedCounty === $judet) ? 'selected' : '' ?>>
                                    <?= $judet ?>
                                </option>
                            <?php endforeach; ?>
                            <option value="București" <?= ($selectedCounty === 'București') ? 'selected' : '' ?>>
                                București
                            </option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="shipping_postal_code">Cod Poștal</label>
                    <input type="text" id="shipping_postal_code" name="shipping_postal_code"
                           class="form-control" value="<?= e($user['postal_code'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="notes">Observații</label>
                    <textarea id="notes" name="notes" class="form-control" rows="3"><?= e($_POST['notes'] ?? '') ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-check"></i> Plasează Comanda
                </button>
            </form>
        </div>

        <!-- Order Summary -->
        <div class="checkout-summary">
            <div class="summary-card">
                <h2>Sumar Comandă</h2>

                <div class="order-items">
                    <?php foreach ($cartItems as $item): ?>
                        <div class="order-item">
                            <span><?= e($item['name']) ?> x<?= $item['quantity'] ?></span>
                            <span><?= formatPrice($item['price'] * $item['quantity']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <hr>

                <div class="summary-row">
                    <span>Subtotal</span>
                    <span><?= formatPrice($totalAmount) ?></span>
                </div>

                <div class="summary-row">
                    <span>Transport</span>
                    <span id="shipping-cost-display"><?= $shippingCost > 0 ? formatPrice($shippingCost) : 'Gratuit' ?></span>
                </div>

                <hr>

                <div class="summary-row total">
                    <span>Total</span>
                    <span id="total-display"><?= formatPrice($finalTotal) ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Update shipping cost when county changes
function updateShippingCost() {
    const county = document.getElementById('shipping_county').value;
    const city = document.getElementById('shipping_city').value;
    const subtotal = <?= $totalAmount ?>;
    const freeShippingThreshold = <?= $freeShipping ?>;

    if (!county) {
        return; // Don't update if no county selected
    }

    fetch('/api/shipping.php?action=get_cost&county=' + encodeURIComponent(county) + '&city=' + encodeURIComponent(city))
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                let cost = data.cost;
                let displayCost = '';

                // Check if free shipping applies
                if (subtotal >= freeShippingThreshold) {
                    cost = 0;
                    displayCost = 'Gratuit';
                } else {
                    displayCost = cost.toFixed(2) + ' RON';
                }

                // Update shipping cost display
                document.getElementById('shipping-cost-display').textContent = displayCost;

                // Update total
                const total = subtotal + cost;
                document.getElementById('total-display').textContent = total.toFixed(2) + ' RON';
            }
        })
        .catch(error => console.error('Error:', error));
}

// Also update when city changes (optional)
document.getElementById('shipping_city').addEventListener('change', updateShippingCost);
</script>

<?php
include SITE_ROOT . '/includes/footer.php';

$additionalCss = '<style>
.checkout-layout {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 40px;
    margin: 40px 0;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.order-items {
    margin-bottom: 20px;
}

.order-item {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid var(--border-color);
    font-size: 14px;
}

@media (max-width: 768px) {
    .checkout-layout {
        grid-template-columns: 1fr;
    }

    .form-row {
        grid-template-columns: 1fr;
    }
}
</style>';
