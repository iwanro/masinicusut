<?php
/**
 * Admin Users Management
 * SUNDARI TOP STAR S.R.L.
 */
require_once '../config/config.php';
requireAdmin();

$db = db();
$action = $_GET['action'] ?? 'list';
$userId = intval($_GET['id'] ?? 0);

// Prevent editing own account role
if ($userId === getCurrentUserId()) {
    setFlash('error', 'Nu îți poți edita propriul cont din admin.');
    redirect('users.php');
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        die('Eroare de securitate.');
    }

    if (isset($_POST['update_role'])) {
        $newRole = $_POST['role'] ?? 'user';
        if (in_array($newRole, ['user', 'admin'])) {
            $stmt = $db->prepare("UPDATE users SET role = ? WHERE id = ?");
            $stmt->execute([$newRole, $userId]);
            setFlash('success', 'Rol utilizator actualizat!');
        }
    }

    redirect('users.php');
}

// Get user for view
$user = null;
if ($action === 'view' && $userId > 0) {
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if ($user) {
        // Get user's orders
        $stmt = $db->prepare("
            SELECT id, order_number, status, total_amount, created_at
            FROM orders
            WHERE user_id = ?
            ORDER BY created_at DESC
            LIMIT 10
        ");
        $stmt->execute([$userId]);
        $userOrders = $stmt->fetchAll();
    }
}

// Get all users
$page = max(1, intval($_GET['page'] ?? 1));
$search = $_GET['search'] ?? '';
$where = ['1=1'];
$params = [];

if (!empty($search)) {
    $where[] = '(name LIKE ? OR email LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$whereClause = implode(' AND ', $where);

// Count
$countSql = "SELECT COUNT(*) FROM users WHERE $whereClause";
$stmt = $db->prepare($countSql);
$stmt->execute($params);
$totalUsers = $stmt->fetchColumn();

// Get users
$offset = getOffset($page, ADMIN_ITEMS_PER_PAGE);
$sql = "SELECT *,
           (SELECT COUNT(*) FROM orders WHERE user_id = users.id) as orders_count,
           (SELECT SUM(total_amount) FROM orders WHERE user_id = users.id AND status != 'cancelled') as total_spent
        FROM users
        WHERE $whereClause
        ORDER BY created_at DESC
        LIMIT ? OFFSET ?";
$stmt = $db->prepare($sql);
$stmt->execute(array_merge($params, [ADMIN_ITEMS_PER_PAGE, $offset]));
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrare Utilizatori - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="<?= URL_CSS ?>/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .user-info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin: 20px 0;
        }
        .user-info-item label {
            font-weight: 600;
            color: var(--text-light);
            display: block;
            margin-bottom: 5px;
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
                <a href="orders.php"><i class="fas fa-shopping-cart"></i> <span>Comenzi</span></a>
                <a href="users.php" class="active"><i class="fas fa-users"></i> <span>Utilizatori</span></a>
                <a href="../index.php"><i class="fas fa-external-link-alt"></i> <span>Vezi Site</span></a>
                <a href="../pages/logout.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <header class="admin-header">
                <h1><?= $action === 'view' && $user ? 'Detalii Utilizator' : 'Administrare Utilizatori' ?></h1>
                <div class="admin-user">
                    <span>Salut, <strong><?= e($_SESSION['name'] ?? 'Admin') ?></strong>!</span>
                </div>
            </header>

            <div class="admin-content">
                <?php if ($action === 'view' && $user): ?>
                    <!-- User Details -->
                    <div class="admin-section">
                        <div class="action-bar" style="margin-bottom: 20px;">
                            <a href="users.php" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Înapoi la Listă</a>
                        </div>

                        <h2>Informații Utilizator</h2>
                        <div class="user-info-grid">
                            <div class="user-info-item">
                                <label>Nume</label>
                                <div><strong><?= e($user['name']) ?></strong></div>
                            </div>
                            <div class="user-info-item">
                                <label>Email</label>
                                <div><?= e($user['email']) ?></div>
                            </div>
                            <div class="user-info-item">
                                <label>Telefon</label>
                                <div><?= e($user['phone'] ?? 'Nu este specificat') ?></div>
                            </div>
                            <div class="user-info-item">
                                <label>Rol</label>
                                <div>
                                    <span class="status-badge <?= $user['role'] === 'admin' ? 'status-completed' : 'status-pending' ?>">
                                        <?= e(ucfirst($user['role'])) ?>
                                    </span>
                                </div>
                            </div>
                            <div class="user-info-item">
                                <label>Adresă</label>
                                <div><?= e($user['address'] ?? 'Nu este specificată') ?></div>
                            </div>
                            <div class="user-info-item">
                                <label>Oraș</label>
                                <div><?= e($user['city'] ?? 'Nu este specificat') ?></div>
                            </div>
                            <div class="user-info-item">
                                <label>Județ</label>
                                <div><?= e($user['county'] ?? 'Nu este specificat') ?></div>
                            </div>
                            <div class="user-info-item">
                                <label>Cod Poștal</label>
                                <div><?= e($user['postal_code'] ?? 'Nu este specificat') ?></div>
                            </div>
                            <div class="user-info-item">
                                <label>Data Înregistrare</label>
                                <div><?= formatDate($user['created_at'], 'd.m.Y H:i') ?></div>
                            </div>
                            <div class="user-info-item">
                                <label>Total Comenzi</label>
                                <div><strong><?= $user['orders_count'] ?></strong></div>
                            </div>
                            <div class="user-info-item">
                                <label>Total Cheltuit</label>
                                <div><strong><?= formatPrice($user['total_spent'] ?: 0) ?></strong></div>
                            </div>
                        </div>

                        <?php if ($userId !== getCurrentUserId()): ?>
                            <hr style="margin: 30px 0;">

                            <h3>Schimbare Rol</h3>
                            <form method="POST" style="margin-top: 15px;">
                                <?= getCsrfField() ?>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="role">Rol Utilizator</label>
                                        <select id="role" name="role" class="form-control">
                                            <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>Utilizator</option>
                                            <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                        </select>
                                    </div>
                                    <div class="form-group" style="display: flex; align-items: flex-end;">
                                        <button type="submit" name="update_role" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Actualizează Rol
                                        </button>
                                    </div>
                                </div>
                            </form>
                        <?php endif; ?>

                        <?php if (!empty($userOrders)): ?>
                            <hr style="margin: 30px 0;">

                            <h3>Comenzile Utilizatorului</h3>
                            <div class="table-responsive">
                                <table class="admin-table">
                                    <thead>
                                        <tr>
                                            <th>Nr. Comandă</th>
                                            <th>Status</th>
                                            <th>Total</th>
                                            <th>Data</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($userOrders as $order): ?>
                                            <tr>
                                                <td><a href="orders.php?action=view&id=<?= $order['id'] ?>"><?= e($order['order_number']) ?></a></td>
                                                <td>
                                                    <span class="status-badge status-<?= $order['status'] ?>">
                                                        <?= e(ucfirst($order['status'])) ?>
                                                    </span>
                                                </td>
                                                <td><?= formatPrice($order['total_amount']) ?></td>
                                                <td><?= formatDate($order['created_at']) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>

                <?php else: ?>
                    <!-- Users List -->
                    <div class="admin-section">
                        <div class="action-bar">
                            <div class="search-box">
                                <form method="GET">
                                    <input type="text" name="search" placeholder="Caută utilizatori..." value="<?= e($search) ?>">
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                                </form>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nume</th>
                                        <th>Email</th>
                                        <th>Rol</th>
                                        <th>Comenzi</th>
                                        <th>Total Cheltuit</th>
                                        <th>Înregistrat</th>
                                        <th>Acțiuni</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($users)): ?>
                                        <?php foreach ($users as $u): ?>
                                            <tr>
                                                <td><?= $u['id'] ?></td>
                                                <td><strong><?= e($u['name']) ?></strong></td>
                                                <td><?= e($u['email']) ?></td>
                                                <td>
                                                    <?php if ($u['role'] === 'admin'): ?>
                                                        <span class="status-badge status-completed">Admin</span>
                                                    <?php else: ?>
                                                        <span class="status-badge status-pending">User</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= $u['orders_count'] ?></td>
                                                <td><?= formatPrice($u['total_spent'] ?: 0) ?></td>
                                                <td><?= formatDate($u['created_at']) ?></td>
                                                <td>
                                                    <a href="users.php?action=view&id=<?= $u['id'] ?>"
                                                       class="btn btn-sm btn-primary">
                                                        <i class="fas fa-eye"></i> Vezi
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center">Nu există utilizatori.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php
                        $totalPages = getTotalPages($totalUsers, ADMIN_ITEMS_PER_PAGE);
                        if ($totalPages > 1):
                        ?>
                            <div class="pagination">
                                <?php if ($page > 1): ?>
                                    <a href="?page=<?= $page - 1 ?>&search=<?= e($search) ?>" class="btn btn-outline">« Previous</a>
                                <?php endif; ?>

                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <?php if ($i == $page): ?>
                                        <span class="btn btn-primary"><?= $i ?></span>
                                    <?php else: ?>
                                        <a href="?page=<?= $i ?>&search=<?= e($search) ?>" class="btn btn-outline"><?= $i ?></a>
                                    <?php endif; ?>
                                <?php endfor; ?>

                                <?php if ($page < $totalPages): ?>
                                    <a href="?page=<?= $page + 1 ?>&search=<?= e($search) ?>" class="btn btn-outline">Next »</a>
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
