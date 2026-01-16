<?php
/**
 * Admin Orders Management
 * SUNDARI TOP STAR S.R.L.
 */
require_once '../config/config.php';
requireAdmin();

$db = db();
$action = $_GET['action'] ?? 'list';
$orderId = intval($_GET['id'] ?? 0);

// Handle status change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        die('Eroare de securitate.');
    }

    $newStatus = $_POST['status'] ?? '';
    if (in_array($newStatus, ['pending', 'processing', 'shipped', 'completed', 'cancelled'])) {
        $stmt = $db->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $orderId]);
        setFlash('success', 'Status comandă actualizat!');
    }
    redirect('orders.php');
}

// Get order for view
$order = null;
$orderItems = [];
if ($action === 'view' && $orderId > 0) {
    $stmt = $db->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();

    if ($order) {
        $stmt = $db->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $stmt->execute([$orderId]);
        $orderItems = $stmt->fetchAll();
    }
}

// Get all orders
$page = max(1, intval($_GET['page'] ?? 1));
$statusFilter = $_GET['status'] ?? '';
$where = ['1=1'];
$params = [];

if (!empty($statusFilter)) {
    $where[] = 'status = ?';
    $params[] = $statusFilter;
}

$whereClause = implode(' AND ', $where);

// Count
$countSql = "SELECT COUNT(*) FROM orders WHERE $whereClause";
$stmt = $db->prepare($countSql);
$stmt->execute($params);
$totalOrders = $stmt->fetchColumn();

// Get orders
$offset = getOffset($page, ADMIN_ITEMS_PER_PAGE);
$sql = "SELECT o.*, u.name as user_name, u.email as user_email
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        WHERE $whereClause
        ORDER BY o.created_at DESC
        LIMIT " . ADMIN_ITEMS_PER_PAGE . " OFFSET $offset";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrare Comenzi - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="<?= URL_CSS ?>/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .order-detail-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 15px;
        }
        .order-detail-label {
            font-weight: 600;
            color: var(--text-light);
        }
        .order-items-table {
            width: 100%;
            margin: 20px 0;
        }
        .order-items-table th,
        .order-items-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }
        .order-total {
            text-align: right;
            font-size: 20px;
            font-weight: 700;
            color: var(--secondary-color);
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <h2>Admin Panel</h2>
            </div>
            <nav class="admin-nav">
                <a href="index.php"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a>
                <a href="products.php"><i class="fas fa-box"></i> <span>Produse</span></a>
                <a href="categories.php"><i class="fas fa-folder"></i> <span>Categorii</span></a>
                <a href="orders.php" class="active"><i class="fas fa-shopping-cart"></i> <span>Comenzi</span></a>
                <a href="users.php"><i class="fas fa-users"></i> <span>Utilizatori</span></a>
                <a href="../index.php"><i class="fas fa-external-link-alt"></i> <span>Vezi Site</span></a>
                <a href="../pages/logout.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <header class="admin-header">
                <h1><?= $action === 'view' && $order ? 'Detalii Comandă #' . e($order['order_number']) : 'Administrare Comenzi' ?></h1>
                <div class="admin-user">
                    <span>Salut, <strong><?= e($_SESSION['name'] ?? 'Admin') ?></strong>!</span>
                </div>
            </header>

            <div class="admin-content">
                <?php if ($action === 'view' && $order): ?>
                    <!-- Order Details -->
                    <div class="admin-section">
                        <div class="action-bar" style="margin-bottom: 20px;">
                            <a href="orders.php" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Înapoi la Listă</a>
                        </div>

                        <div class="order-detail-row">
                            <div>
                                <div class="order-detail-label">Număr Comandă</div>
                                <div><strong><?= e($order['order_number']) ?></strong></div>
                            </div>
                            <div>
                                <div class="order-detail-label">Status</div>
                                <div>
                                    <span class="status-badge status-<?= $order['status'] ?>">
                                        <?= e(ucfirst($order['status'])) ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="order-detail-row">
                            <div>
                                <div class="order-detail-label">Client</div>
                                <div>
                                    <strong><?= e($order['shipping_name']) ?></strong><br>
                                    <small>
                                        <i class="fas fa-envelope"></i> <?= e($order['user_email'] ?? 'Guest') ?>
                                    </small>
                                </div>
                            </div>
                            <div>
                                <div class="order-detail-label">Data Comandă</div>
                                <div><?= formatDate($order['created_at'], 'd.m.Y H:i') ?></div>
                            </div>
                        </div>

                        <div class="order-detail-row">
                            <div>
                                <div class="order-detail-label">Telefon</div>
                                <div><?= e($order['shipping_phone']) ?></div>
                            </div>
                            <div>
                                <div class="order-detail-label">Email Client</div>
                                <div><?= e($order['user_email'] ?? '-') ?></div>
                            </div>
                        </div>

                        <div style="margin: 20px 0;">
                            <div class="order-detail-label">Adresă Livrare</div>
                            <div>
                                <?= e($order['shipping_address']) ?><br>
                                <?= e($order['shipping_city']) ?>
                                <?= e($order['shipping_county'] ? ', ' . $order['shipping_county'] : '') ?>
                                <?= e($order['shipping_postal_code']) ?>
                            </div>
                        </div>

                        <?php if ($order['notes']): ?>
                            <div style="margin: 20px 0;">
                                <div class="order-detail-label">Observații</div>
                                <div><?= e($order['notes']) ?></div>
                            </div>
                        <?php endif; ?>

                        <h3>Produse Comandate</h3>
                        <table class="order-items-table">
                            <thead>
                                <tr>
                                    <th>Produs</th>
                                    <th>Cantitate</th>
                                    <th>Preț Unitar</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orderItems as $item): ?>
                                    <tr>
                                        <td><strong><?= e($item['product_name']) ?></strong></td>
                                        <td><?= $item['quantity'] ?></td>
                                        <td><?= formatPrice($item['price']) ?></td>
                                        <td><?= formatPrice($item['subtotal']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <div class="order-total">
                            Total: <?= formatPrice($order['total_amount']) ?>
                        </div>

                        <hr style="margin: 30px 0;">

                        <h3>Schimbare Status</h3>
                        <form method="POST">
                            <?= getCsrfField() ?>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="status">Status Comandă</label>
                                    <select id="status" name="status" class="form-control">
                                        <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>În Așteptare</option>
                                        <option value="processing" <?= $order['status'] === 'processing' ? 'selected' : '' ?>>În Procesare</option>
                                        <option value="shipped" <?= $order['status'] === 'shipped' ? 'selected' : '' ?>>Expediată</option>
                                        <option value="completed" <?= $order['status'] === 'completed' ? 'selected' : '' ?>>Finalizată</option>
                                        <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Anulată</option>
                                    </select>
                                </div>
                                <div class="form-group" style="display: flex; align-items: flex-end;">
                                    <button type="submit" name="update_status" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Actualizează Status
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                <?php else: ?>
                    <!-- Orders List -->
                    <div class="admin-section">
                        <div class="action-bar">
                            <div>
                                <a href="orders.php" class="btn btn-outline <?= empty($statusFilter) ? 'active' : '' ?>">Toate</a>
                                <a href="orders.php?status=pending" class="btn btn-outline <?= $statusFilter === 'pending' ? 'active' : '' ?>">În Așteptare</a>
                                <a href="orders.php?status=processing" class="btn btn-outline <?= $statusFilter === 'processing' ? 'active' : '' ?>">În Procesare</a>
                                <a href="orders.php?status=shipped" class="btn btn-outline <?= $statusFilter === 'shipped' ? 'active' : '' ?>">Expediate</a>
                                <a href="orders.php?status=completed" class="btn btn-outline <?= $statusFilter === 'completed' ? 'active' : '' ?>">Finalizate</a>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Nr. Comandă</th>
                                        <th>Client</th>
                                        <th>Status</th>
                                        <th>Total</th>
                                        <th>Data</th>
                                        <th>Acțiuni</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($orders)): ?>
                                        <?php foreach ($orders as $ord): ?>
                                            <tr>
                                                <td><strong><?= e($ord['order_number']) ?></strong></td>
                                                <td>
                                                    <?= e($ord['shipping_name']) ?><br>
                                                    <small class="text-muted"><?= e($ord['user_email'] ?? 'Guest') ?></small>
                                                </td>
                                                <td>
                                                    <span class="status-badge status-<?= $ord['status'] ?>">
                                                        <?= e(ucfirst($ord['status'])) ?>
                                                    </span>
                                                </td>
                                                <td><strong><?= formatPrice($ord['total_amount']) ?></strong></td>
                                                <td><?= formatDate($ord['created_at']) ?></td>
                                                <td>
                                                    <a href="orders.php?action=view&id=<?= $ord['id'] ?>" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-eye"></i> Vezi
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center">Nu există comenzi.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php
                        $totalPages = getTotalPages($totalOrders, ADMIN_ITEMS_PER_PAGE);
                        if ($totalPages > 1):
                        ?>
                            <div class="pagination">
                                <?php if ($page > 1): ?>
                                    <a href="?page=<?= $page - 1 ?>&status=<?= e($statusFilter) ?>" class="btn btn-outline">« Previous</a>
                                <?php endif; ?>

                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <?php if ($i == $page): ?>
                                        <span class="btn btn-primary"><?= $i ?></span>
                                    <?php else: ?>
                                        <a href="?page=<?= $i ?>&status=<?= e($statusFilter) ?>" class="btn btn-outline"><?= $i ?></a>
                                    <?php endif; ?>
                                <?php endfor; ?>

                                <?php if ($page < $totalPages): ?>
                                    <a href="?page=<?= $page + 1 ?>&status=<?= e($statusFilter) ?>" class="btn btn-outline">Next »</a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
