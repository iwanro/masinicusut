<?php
/**
 * Admin Shipping Rates Management
 * SUNDARI TOP STAR S.R.L.
 */
require_once '../config/config.php';
requireAdmin();

$db = db();
$action = $_GET['action'] ?? 'list';
$rateId = intval($_GET['id'] ?? 0);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        die('Eroare de securitate.');
    }

    if (isset($_POST['delete_rate'])) {
        $stmt = $db->prepare("DELETE FROM shipping_rates WHERE id = ?");
        $stmt->execute([$rateId]);
        setFlash('success', 'Taxă transport ștearsă!');
        redirect('shipping.php');
    }

    // Add/Edit shipping rate
    $county = trim($_POST['county'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $shippingCost = floatval($_POST['shipping_cost'] ?? 0);
    $isActive = isset($_POST['is_active']) ? 1 : 0;

    if ($shippingCost <= 0) {
        setFlash('error', 'Taxa de transport trebuie să fie mai mare de 0.');
        redirect('shipping.php');
    }

    if (empty($county)) {
        setFlash('error', 'Județul este obligatoriu.');
        redirect('shipping.php');
    }

    if ($rateId > 0) {
        // Update
        $stmt = $db->prepare("
            UPDATE shipping_rates
            SET county = ?, city = ?, shipping_cost = ?, is_active = ?
            WHERE id = ?
        ");
        $stmt->execute([$county, $city ?: null, $shippingCost, $isActive, $rateId]);
        setFlash('success', 'Taxă transport actualizată!');
    } else {
        // Insert
        try {
            $stmt = $db->prepare("
                INSERT INTO shipping_rates (county, city, shipping_cost, is_active)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$county, $city ?: null, $shippingCost, $isActive]);
            setFlash('success', 'Taxă transport adăugată!');
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Duplicate entry
                setFlash('error', 'Există deja o taxă pentru această zonă (' . e($county) . ($city ? ' - ' . e($city) : '') . ').');
            } else {
                setFlash('error', 'Eroare la salvare.');
            }
        }
    }

    redirect('shipping.php');
}

// Get rate for edit
$rate = null;
if ($action === 'edit' && $rateId > 0) {
    $stmt = $db->prepare("SELECT * FROM shipping_rates WHERE id = ?");
    $stmt->execute([$rateId]);
    $rate = $stmt->fetch();
}

// Get all rates
$stmt = $db->query("
    SELECT sr.*
    FROM shipping_rates sr
    ORDER BY sr.is_active DESC, sr.county, sr.city
");
$rates = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Taxe Transport - <?= SITE_NAME ?></title>
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
                <a href="index.php"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a>
                <a href="products.php"><i class="fas fa-box"></i> <span>Produse</span></a>
                <a href="categories.php"><i class="fas fa-folder"></i> <span>Categorii</span></a>
                <a href="orders.php"><i class="fas fa-shopping-cart"></i> <span>Comenzi</span></a>
                <a href="users.php"><i class="fas fa-users"></i> <span>Utilizatori</span></a>
                <a href="shipping.php" class="active"><i class="fas fa-truck"></i> <span>Transport</span></a>
                <a href="email_settings.php"><i class="fas fa-envelope"></i> <span>Email</span></a>
                <a href="../index.php"><i class="fas fa-external-link-alt"></i> <span>Vezi Site</span></a>
                <a href="../pages/logout.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <header class="admin-header">
                <h1><?php
                    if ($action === 'add'): echo 'Adaugă Taxă Transport';
                    elseif ($action === 'edit'): echo 'Editează Taxă Transport';
                    else: echo 'Administrare Taxe Transport';
                    endif;
                ?></h1>
                <div class="admin-user">
                    <span>Salut, <strong><?= e($_SESSION['name'] ?? 'Admin') ?></strong>!</span>
                </div>
            </header>

            <div class="admin-content">
                <?php if ($action === 'add' || $action === 'edit'): ?>
                    <!-- Add/Edit Form -->
                    <div class="admin-section">
                        <form method="POST">
                            <?= getCsrfField() ?>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="county">Județ *</label>
                                    <select id="county" name="county" class="form-control" required>
                                        <option value="">Selectează...</option>
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
                                            <option value="<?= e($judet) ?>" <?= ($rate['county'] ?? '') === $judet ? 'selected' : '' ?>>
                                                <?= e($judet) ?>
                                            </option>
                                        <?php endforeach; ?>
                                        <option value="București" <?= ($rate['county'] ?? '') === 'București' ? 'selected' : '' ?>>
                                            București
                                        </option>
                                    </select>
                                    <small>Lăsă localitatea goală pentru taxă pe întreg județul</small>
                                </div>

                                <div class="form-group">
                                    <label for="city">Localitate (opțional)</label>
                                    <input type="text" id="city" name="city" class="form-control"
                                           value="<?= e($rate['city'] ?? '') ?>"
                                           placeholder="Ex: Cluj-Napoca (lăsă gol pentru tot județul)">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="shipping_cost">Taxă Transport (RON) *</label>
                                <input type="number" id="shipping_cost" name="shipping_cost" class="form-control"
                                       value="<?= $rate['shipping_cost'] ?? '' ?>" step="0.01" min="0" required>
                            </div>

                            <div class="form-group">
                                <label style="display: flex; align-items: center; gap: 10px;">
                                    <input type="checkbox" name="is_active" <?= ($rate['is_active'] ?? 1) ? 'checked' : '' ?>>
                                    Taxă Activă
                                </label>
                            </div>

                            <div style="margin-top: 20px;">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-save"></i> <?= $action === 'edit' ? 'Actualizează' : 'Adaugă' ?> Taxă
                                </button>
                                <a href="shipping.php" class="btn btn-outline">Anulează</a>
                            </div>
                        </form>
                    </div>

                <?php else: ?>
                    <!-- Rates List -->
                    <div class="admin-section">
                        <div class="action-bar">
                            <h2>Toate Taxele de Transport</h2>
                            <a href="shipping.php?action=add" class="btn btn-success">
                                <i class="fas fa-plus"></i> Adaugă Taxă Nouă
                            </a>
                        </div>

                        <div class="table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Județ</th>
                                        <th>Localitate</th>
                                        <th>Taxă (RON)</th>
                                        <th>Status</th>
                                        <th>Acțiuni</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($rates)): ?>
                                        <?php foreach ($rates as $r): ?>
                                            <tr>
                                                <td><strong><?= e($r['county']) ?></strong></td>
                                                <td><?= $r['city'] ? e($r['city']) : '<em>Todo județul</em>' ?></td>
                                                <td><strong><?= number_format($r['shipping_cost'], 2) ?> RON</strong></td>
                                                <td>
                                                    <?php if ($r['is_active']): ?>
                                                        <span class="status-badge status-completed">Activă</span>
                                                    <?php else: ?>
                                                        <span class="status-badge status-cancelled">Inactivă</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <a href="shipping.php?action=edit&id=<?= $r['id'] ?>"
                                                       class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
                                                    <button onclick="deleteRate(<?= intval($r['id']) ?>)"
                                                            class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center">Nu există taxe de transport configurate.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <div style="margin-top: 30px; padding: 20px; background-color: var(--bg-light); border-radius: var(--border-radius);">
                            <h3>ℹ️ Informații Importante</h3>
                            <ul style="margin-top: 15px; padding-left: 20px;">
                                <li>Taxele se aplică în ordinea: Județ + Localitate → Județ → Taxă Default</li>
                                <li>Dacă un client selectează "Cluj" și "Cluj-Napoca", sistemul caută mai întâi o taxă specifică pentru Cluj-Napoca</li>
                                <li>Dacă nu găsește, caută taxă pentru întreg județul Cluj</li>
                                <li>Dacă nu găsește nici așa, folosește taxa default din settings</li>
                                <li>Transport gratuit peste pragul configurat în settings se aplică indiferent de taxă</li>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <?php if ($action === 'list'): ?>
    <script>
    function deleteRate(id) {
        if (confirm('Ești sigur că vrei să ștergi această taxă de transport?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = '<input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= generateCsrfToken() ?>">' +
                           '<input type="hidden" name="delete_rate" value="1">';
            document.body.appendChild(form);
            form.action = 'shipping.php?action=edit&id=' + id;
            form.submit();
        }
    }
    </script>
    <?php endif; ?>
</body>
</html>
