<?php
/**
 * Shopping Cart Page
 * SUNDARI TOP STAR S.R.L.
 */
require_once '../config/config.php';

$pageTitle = 'Coșul Meu';

$db = db();
$userId = isLoggedIn() ? getCurrentUserId() : null;
$sessionId = session_id();

// Get cart items
$sql = "SELECT c.id as cart_id, c.quantity, p.id, p.name, p.slug, p.price, p.image, p.stock
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE " . ($userId ? "c.user_id = ?" : "c.session_id = ?");
$stmt = $db->prepare($sql);
$stmt->execute([$userId ?? $sessionId]);
$cartItems = $stmt->fetchAll();

// Calculate totals
$totalItems = 0;
$totalAmount = 0;
foreach ($cartItems as $item) {
    $totalItems += $item['quantity'];
    $totalAmount += $item['price'] * $item['quantity'];
}

include SITE_ROOT . '/includes/header.php';
?>

<div class="container">
    <h1>Coșul Meu</h1>

    <?php if (!empty($cartItems)): ?>
        <div class="cart-layout">
            <!-- Cart Items -->
            <div class="cart-items">
                <?php foreach ($cartItems as $item): ?>
                    <div class="cart-item">
                        <div class="cart-item-image">
                            <?php if ($item['image']): ?>
                                <img src="<?= URL_PRODUCTS . '/' . e($item['image']) ?>" alt="<?= e($item['name']) ?>">
                            <?php else: ?>
                                <img src="https://via.placeholder.com/100x100?text=No+Image" alt="<?= e($item['name']) ?>">
                            <?php endif; ?>
                        </div>

                        <div class="cart-item-details">
                            <h3>
                                <a href="/pages/product.php?slug=<?= e($item['slug']) ?>">
                                    <?= e($item['name']) ?>
                                </a>
                            </h3>
                            <p class="item-price"><?= formatPrice($item['price']) ?></p>
                        </div>

                        <div class="cart-item-actions">
                            <div class="quantity-controls">
                                <button onclick="updateCartItem(<?= $item['cart_id'] ?>, <?= $item['quantity'] - 1 ?>)"
                                        <?= $item['quantity'] <= 1 ? 'disabled' : '' ?>>-</button>
                                <input type="number" value="<?= $item['quantity'] ?>" readonly>
                                <button onclick="updateCartItem(<?= $item['cart_id'] ?>, <?= $item['quantity'] + 1 ?>)"
                                        <?= $item['quantity'] >= $item['stock'] ? 'disabled' : '' ?>>+</button>
                            </div>
                            <div class="item-subtotal">
                                <strong><?= formatPrice($item['price'] * $item['quantity']) ?></strong>
                            </div>
                            <button onclick="removeFromCart(<?= $item['cart_id'] ?>)" class="btn-remove">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Cart Summary -->
            <div class="cart-summary">
                <div class="summary-card">
                    <h2>Sumar Comandă</h2>
                    <div class="summary-row">
                        <span>Produse (<?= $totalItems ?>)</span>
                        <span><?= formatPrice($totalAmount) ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Transport</span>
                        <span>
                            <?php
                            $freeShipping = floatval(getSetting('free_shipping_threshold', 200));
                            $shippingCost = floatval(getSetting('shipping_cost', 15));
                            if ($totalAmount >= $freeShipping): ?>
                                Gratuit
                            <?php else: ?>
                                <?= formatPrice($shippingCost) ?>
                            <?php endif; ?>
                        </span>
                    </div>
                    <?php if ($totalAmount < $freeShipping): ?>
                        <p class="free-shipping-hint">
                            Mai adaugă produse de <?= formatPrice($freeShipping - $totalAmount) ?> pentru transport gratuit!
                        </p>
                    <?php endif; ?>
                    <hr>
                    <div class="summary-row total">
                        <span>Total</span>
                        <span>
                            <?php
                            $finalTotal = $totalAmount >= $freeShipping ? $totalAmount : $totalAmount + $shippingCost;
                            echo formatPrice($finalTotal);
                            ?>
                        </span>
                    </div>
                    <a href="/pages/checkout.php" class="btn btn-primary btn-lg" style="width: 100%; margin-top: 20px;">
                        Continuă spre Checkout
                    </a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="empty-cart">
            <i class="fas fa-shopping-cart"></i>
            <h2>Coșul tău este gol</h2>
            <p>Nu ai adăugat încă produse în coș.</p>
            <a href="/pages/catalog.php" class="btn btn-primary">Vezi Produsele</a>
        </div>
    <?php endif; ?>
</div>

<?php
include SITE_ROOT . '/includes/footer.php';

$additionalCss = '<style>
.cart-layout {
    display: grid;
    grid-template-columns: 1fr 350px;
    gap: 30px;
    margin: 40px 0;
}

.cart-items {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.cart-item {
    display: grid;
    grid-template-columns: 100px 1fr auto;
    gap: 20px;
    background-color: var(--bg-white);
    padding: 20px;
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius);
}

.cart-item-image img {
    width: 100px;
    height: 100px;
    object-fit: cover;
    border-radius: var(--border-radius);
}

.cart-item-details h3 {
    font-size: 18px;
    margin-bottom: 10px;
}

.item-price {
    color: var(--text-light);
    font-size: 14px;
}

.cart-item-actions {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 15px;
}

.quantity-controls {
    display: flex;
    align-items: center;
    gap: 5px;
}

.quantity-controls button {
    width: 30px;
    height: 30px;
    border: 1px solid var(--border-color);
    background-color: var(--bg-white);
    border-radius: var(--border-radius);
    cursor: pointer;
}

.quantity-controls button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.quantity-controls input {
    width: 50px;
    text-align: center;
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius);
    padding: 5px;
}

.item-subtotal {
    font-size: 18px;
    color: var(--secondary-color);
}

.btn-remove {
    background: none;
    border: none;
    color: var(--danger-color);
    cursor: pointer;
    font-size: 18px;
}

.summary-card {
    background-color: var(--bg-white);
    padding: 25px;
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius);
    position: sticky;
    top: 100px;
}

.summary-card h2 {
    font-size: 20px;
    margin-bottom: 20px;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 15px;
}

.summary-row.total {
    font-size: 20px;
    font-weight: 700;
    color: var(--secondary-color);
}

.free-shipping-hint {
    color: var(--success-color);
    font-size: 13px;
    margin: -10px 0 15px;
}

.empty-cart {
    text-align: center;
    padding: 80px 20px;
}

.empty-cart i {
    font-size: 80px;
    color: var(--text-lighter);
    margin-bottom: 20px;
}

.empty-cart h2 {
    font-size: 32px;
    margin-bottom: 10px;
}

@media (max-width: 768px) {
    .cart-layout {
        grid-template-columns: 1fr;
    }

    .cart-item {
        grid-template-columns: 80px 1fr;
    }

    .cart-item-actions {
        grid-column: 1 / -1;
        flex-direction: row;
        justify-content: space-between;
        width: 100%;
    }
}
</style>';
