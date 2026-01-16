<?php
/**
 * User Account Page
 * SUNDARI TOP STAR S.R.L.
 */
require_once '../config/config.php';

requireAuth();

$pageTitle = 'Contul Meu';

$db = db();
$userId = getCurrentUserId();
$user = getCurrentUser();

// Get user's orders
$stmt = $db->prepare("
    SELECT id, order_number, status, total_amount, created_at
    FROM orders
    WHERE user_id = ?
    ORDER BY created_at DESC
    LIMIT 20
");
$stmt->execute([$userId]);
$orders = $stmt->fetchAll();

include SITE_ROOT . '/includes/header.php';
?>

<div class="container">
    <h1>Contul Meu</h1>

    <div class="account-layout">
        <!-- User Info -->
        <div class="account-section">
            <h2>Date Personale</h2>
            <div class="user-info">
                <p><strong>Nume:</strong> <?= e($user['name']) ?></p>
                <p><strong>Email:</strong> <?= e($user['email']) ?></p>
                <p><strong>Telefon:</strong> <?= e($user['phone'] ?? 'Nu este specificat') ?></p>
                <p><strong>Adresă:</strong> <?= e($user['address'] ?? 'Nu este specificată') ?></p>
                <p><strong>Oraș:</strong> <?= e($user['city'] ?? 'Nu este specificat') ?></p>
            </div>
            <a href="/pages/catalog.php" class="btn btn-primary">Continuă Cumpărăturile</a>
        </div>

        <!-- Order History -->
        <div class="account-section">
            <h2>Comenzile Mele</h2>

            <?php if (!empty($orders)): ?>
                <div class="orders-list">
                    <?php foreach ($orders as $order): ?>
                        <div class="order-card">
                            <div class="order-header">
                                <div>
                                    <strong><?= e($order['order_number']) ?></strong>
                                    <span class="order-date"><?= formatDate($order['created_at']) ?></span>
                                </div>
                                <span class="order-status order-<?= $order['status'] ?>">
                                    <?= e(ucfirst($order['status'])) ?>
                                </span>
                            </div>
                            <div class="order-total">
                                Total: <?= formatPrice($order['total_amount']) ?>
                            </div>
                            <a href="javascript:void(0)" class="btn btn-outline btn-sm">Vezi Detalii</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-orders">
                    <i class="fas fa-shopping-bag"></i>
                    <h3>Nu ai încă nicio comandă</h3>
                    <p>Începe să adaugi produse în coș!</p>
                    <a href="/pages/catalog.php" class="btn btn-primary">Vezi Produsele</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
include SITE_ROOT . '/includes/footer.php';

$additionalCss = '<style>
.account-layout {
    display: grid;
    grid-template-columns: 1fr 2fr;
    gap: 40px;
    margin: 40px 0;
}

.account-section {
    background-color: var(--bg-white);
    padding: 30px;
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius);
}

.account-section h2 {
    font-size: 20px;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid var(--accent-color);
}

.user-info p {
    margin-bottom: 10px;
    line-height: 1.8;
}

.orders-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.order-card {
    padding: 20px;
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius);
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.order-date {
    display: block;
    font-size: 13px;
    color: var(--text-light);
    margin-top: 5px;
}

.order-status {
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.order-pending { background-color: var(--warning-color); color: #fff; }
.order-processing { background-color: var(--accent-color); color: #fff; }
.order-shipped { background-color: var(--primary-color); color: #fff; }
.order-completed { background-color: var(--success-color); color: #fff; }
.order-cancelled { background-color: var(--danger-color); color: #fff; }

.order-total {
    font-size: 18px;
    font-weight: 600;
    color: var(--secondary-color);
    margin-bottom: 15px;
}

.btn-sm {
    padding: 5px 15px;
    font-size: 13px;
}

.no-orders {
    text-align: center;
    padding: 60px 20px;
}

.no-orders i {
    font-size: 64px;
    color: var(--text-lighter);
    margin-bottom: 20px;
}

@media (max-width: 768px) {
    .account-layout {
        grid-template-columns: 1fr;
    }
}
</style>';
