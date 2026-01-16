<?php
/**
 * Admin Dashboard
 * SUNDARI TOP STAR S.R.L.
 */
require_once '../config/config.php';

requireAdmin();

$db = db();

// Get statistics
$stats = [];

// Total products
$stmt = $db->query("SELECT COUNT(*) FROM products");
$stats['total_products'] = $stmt->fetchColumn();

// Active products
$stmt = $db->query("SELECT COUNT(*) FROM products WHERE is_active = 1");
$stats['active_products'] = $stmt->fetchColumn();

// Total orders
$stmt = $db->query("SELECT COUNT(*) FROM orders");
$stats['total_orders'] = $stmt->fetchColumn();

// Pending orders
$stmt = $db->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'");
$stats['pending_orders'] = $stmt->fetchColumn();

// Total users
$stmt = $db->query("SELECT COUNT(*) FROM users");
$stats['total_users'] = $stmt->fetchColumn();

// Total revenue
$stmt = $db->query("SELECT SUM(total_amount) FROM orders WHERE status != 'cancelled'");
$stats['revenue'] = $stmt->fetchColumn() ?: 0;

// Recent orders
$stmt = $db->query("
    SELECT id, order_number, status, total_amount, created_at
    FROM orders
    ORDER BY created_at DESC
    LIMIT 10
");
$recentOrders = $stmt->fetchAll();

$pageTitle = 'Admin Dashboard';
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="<?= URL_CSS ?>/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <h2>Admin Panel</h2>
            </div>
            <nav class="admin-nav">
                <a href="index.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="products.php"><i class="fas fa-box"></i> Produse</a>
                <a href="categories.php"><i class="fas fa-folder"></i> Categorii</a>
                <a href="orders.php"><i class="fas fa-shopping-cart"></i> Comenzi</a>
                <a href="users.php"><i class="fas fa-users"></i> Utilizatori</a>
                <a href="../index.php"><i class="fas fa-external-link-alt"></i> Vezi Site</a>
                <a href="../pages/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <header class="admin-header">
                <h1><?= $pageTitle ?></h1>
                <div class="admin-user">
                    <span>Salut, <strong><?= e($_SESSION['name'] ?? 'Admin') ?></strong>!</span>
                </div>
            </header>

            <div class="admin-content">
                <!-- Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon" style="background-color: var(--accent-color);">
                            <i class="fas fa-box"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?= $stats['total_products'] ?></h3>
                            <p>Total Produse</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="background-color: var(--warning-color);">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?= $stats['total_orders'] ?></h3>
                            <p>Total Comenzi</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="background-color: var(--success-color);">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?= formatPrice($stats['revenue']) ?></h3>
                            <p>Venituri Totale</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="background-color: var(--secondary-color);">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?= $stats['total_users'] ?></h3>
                            <p>Utilizatori</p>
                        </div>
                    </div>
                </div>

                <!-- Recent Orders -->
                <div class="admin-section">
                    <h2>Comenzi Recente</h2>
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Nr. Comandă</th>
                                    <th>Status</th>
                                    <th>Total</th>
                                    <th>Data</th>
                                    <th>Acțiuni</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($recentOrders)): ?>
                                    <?php foreach ($recentOrders as $order): ?>
                                        <tr>
                                            <td><?= e($order['order_number']) ?></td>
                                            <td>
                                                <span class="status-badge status-<?= $order['status'] ?>">
                                                    <?= e(ucfirst($order['status'])) ?>
                                                </span>
                                            </td>
                                            <td><?= formatPrice($order['total_amount']) ?></td>
                                            <td><?= formatDate($order['created_at']) ?></td>
                                            <td>
                                                <a href="orders.php?action=view&id=<?= $order['id'] ?>" class="btn btn-sm btn-primary">Vezi</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center">Nu există comenzi încă.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
