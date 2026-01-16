<?php
/**
 * Admin Categories Management
 * SUNDARI TOP STAR S.R.L.
 */
require_once '../config/config.php';
requireAdmin();

$db = db();
$action = $_GET['action'] ?? 'list';
$categoryId = intval($_GET['id'] ?? 0);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        die('Eroare de securitate.');
    }

    if (isset($_POST['delete_category'])) {
        // Check if category has products
        $stmt = $db->prepare("SELECT COUNT(*) FROM products WHERE category_id = ? OR subcategory_id = ?");
        $stmt->execute([$categoryId, $categoryId]);
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            setFlash('error', 'Nu poți șterge această categorie deoarece are produse asociate.');
            redirect('categories.php');
        }

        $stmt = $db->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$categoryId]);
        setFlash('success', 'Categorie ștearsă cu succes!');
        redirect('categories.php');
    }

    // Add/Edit category
    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? slugify($_POST['name'] ?? ''));
    $description = trim($_POST['description'] ?? '');
    $type = $_POST['type'] ?? 'brand';
    $sortOrder = intval($_POST['sort_order'] ?? 0);
    $isActive = isset($_POST['is_active']) ? 1 : 0;

    if ($categoryId > 0) {
        // Update
        $stmt = $db->prepare("
            UPDATE categories
            SET name = ?, slug = ?, description = ?, type = ?, sort_order = ?, is_active = ?
            WHERE id = ?
        ");
        $stmt->execute([$name, $slug, $description, $type, $sortOrder, $isActive, $categoryId]);
        setFlash('success', 'Categorie actualizată cu succes!');
    } else {
        // Insert
        $stmt = $db->prepare("
            INSERT INTO categories (name, slug, description, type, sort_order, is_active)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$name, $slug, $description, $type, $sortOrder, $isActive]);
        setFlash('success', 'Categorie adăugată cu succes!');
    }

    redirect('categories.php');
}

// Get category for edit
$category = null;
if ($action === 'edit' && $categoryId > 0) {
    $stmt = $db->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$categoryId]);
    $category = $stmt->fetch();
}

// Get all categories
$stmt = $db->query("
    SELECT c.*,
           (SELECT COUNT(*) FROM products WHERE category_id = c.id OR subcategory_id = c.id) as products_count
    FROM categories c
    ORDER BY c.type, c.sort_order, c.name
");
$categories = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrare Categorii - <?= SITE_NAME ?></title>
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
                <a href="categories.php" class="active"><i class="fas fa-folder"></i> <span>Categorii</span></a>
                <a href="orders.php"><i class="fas fa-shopping-cart"></i> <span>Comenzi</span></a>
                <a href="users.php"><i class="fas fa-users"></i> <span>Utilizatori</span></a>
                <a href="../index.php"><i class="fas fa-external-link-alt"></i> <span>Vezi Site</span></a>
                <a href="../pages/logout.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <header class="admin-header">
                <h1><?php
                    if ($action === 'add'): echo 'Adaugă Categorie Nouă';
                    elseif ($action === 'edit'): echo 'Editează Categorie';
                    else: echo 'Administrare Categorii';
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
                                    <label for="name">Nume Categorie *</label>
                                    <input type="text" id="name" name="name" class="form-control"
                                           value="<?= e($category['name'] ?? '') ?>" required
                                           oninput="document.getElementById('slug').value = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-')">
                                </div>

                                <div class="form-group">
                                    <label for="slug">Slug</label>
                                    <input type="text" id="slug" name="slug" class="form-control"
                                           value="<?= e($category['slug'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="type">Tip Categorie *</label>
                                    <select id="type" name="type" class="form-control" required>
                                        <option value="brand" <?= ($category['type'] ?? '') === 'brand' ? 'selected' : '' ?>>
                                            Brand (Marcă)
                                        </option>
                                        <option value="product_type" <?= ($category['type'] ?? '') === 'product_type' ? 'selected' : '' ?>>
                                            Tip Produs
                                        </option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="sort_order">Ordine Sortare</label>
                                    <input type="number" id="sort_order" name="sort_order" class="form-control"
                                           value="<?= $category['sort_order'] ?? 0 ?>" min="0">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="description">Descriere</label>
                                <textarea id="description" name="description" class="form-control" rows="3"><?= e($category['description'] ?? '') ?></textarea>
                            </div>

                            <div class="form-group">
                                <label style="display: flex; align-items: center; gap: 10px;">
                                    <input type="checkbox" name="is_active" <?= ($category['is_active'] ?? 1) ? 'checked' : '' ?>>
                                    Categorie Activă
                                </label>
                            </div>

                            <div style="margin-top: 20px;">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-save"></i> <?= $action === 'edit' ? 'Actualizează' : 'Adaugă' ?> Categorie
                                </button>
                                <a href="categories.php" class="btn btn-outline">Anulează</a>
                            </div>
                        </form>
                    </div>

                <?php else: ?>
                    <!-- Categories List -->
                    <div class="admin-section">
                        <div class="action-bar">
                            <h2>Mărci (Brands)</h2>
                            <a href="categories.php?action=add&type=brand" class="btn btn-success">
                                <i class="fas fa-plus"></i> Adaugă Brand
                            </a>
                        </div>

                        <div class="table-responsive" style="margin-bottom: 40px;">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nume</th>
                                        <th>Slug</th>
                                        <th>Produse</th>
                                        <th>Ordine</th>
                                        <th>Status</th>
                                        <th>Acțiuni</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $brands = array_filter($categories, fn($c) => $c['type'] === 'brand');
                                    if (!empty($brands)):
                                    ?>
                                        <?php foreach ($brands as $cat): ?>
                                            <tr>
                                                <td><?= $cat['id'] ?></td>
                                                <td><strong><?= e($cat['name']) ?></strong></td>
                                                <td><code><?= e($cat['slug']) ?></code></td>
                                                <td><?= $cat['products_count'] ?></td>
                                                <td><?= $cat['sort_order'] ?></td>
                                                <td>
                                                    <?php if ($cat['is_active']): ?>
                                                        <span class="status-badge status-completed">Activ</span>
                                                    <?php else: ?>
                                                        <span class="status-badge status-cancelled">Inactiv</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <a href="categories.php?action=edit&id=<?= $cat['id'] ?>"
                                                       class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
                                                    <button onclick="deleteCategory(<?= $cat['id'] ?>, <?= $cat['products_count'] ?>)"
                                                            class="btn btn-sm btn-danger" <?= $cat['products_count'] > 0 ? 'disabled' : '' ?>>
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="7" class="text-center">Nu există branduri.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="action-bar">
                            <h2>Tipuri Produse</h2>
                            <a href="categories.php?action=add&type=product_type" class="btn btn-success">
                                <i class="fas fa-plus"></i> Adaugă Tip
                            </a>
                        </div>

                        <div class="table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nume</th>
                                        <th>Slug</th>
                                        <th>Produse</th>
                                        <th>Ordine</th>
                                        <th>Status</th>
                                        <th>Acțiuni</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $types = array_filter($categories, fn($c) => $c['type'] === 'product_type');
                                    if (!empty($types)):
                                    ?>
                                        <?php foreach ($types as $cat): ?>
                                            <tr>
                                                <td><?= $cat['id'] ?></td>
                                                <td><strong><?= e($cat['name']) ?></strong></td>
                                                <td><code><?= e($cat['slug']) ?></code></td>
                                                <td><?= $cat['products_count'] ?></td>
                                                <td><?= $cat['sort_order'] ?></td>
                                                <td>
                                                    <?php if ($cat['is_active']): ?>
                                                        <span class="status-badge status-completed">Activ</span>
                                                    <?php else: ?>
                                                        <span class="status-badge status-cancelled">Inactiv</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <a href="categories.php?action=edit&id=<?= $cat['id'] ?>"
                                                       class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
                                                    <button onclick="deleteCategory(<?= $cat['id'] ?>, <?= $cat['products_count'] ?>)"
                                                            class="btn btn-sm btn-danger" <?= $cat['products_count'] > 0 ? 'disabled' : '' ?>>
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="7" class="text-center">Nu există tipuri de produse.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <?php if ($action === 'list'): ?>
    <script>
    function deleteCategory(id, productsCount) {
        if (productsCount > 0) {
            alert('Nu poți șterge această categorie deoarece are produse asociate.');
            return;
        }
        if (confirm('Ești sigur că vrei să ștergi această categorie?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = '<input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= generateCsrfToken() ?>">' +
                           '<input type="hidden" name="delete_category" value="1">';
            document.body.appendChild(form);
            form.action = 'categories.php?action=edit&id=' + id;
            form.submit();
        }
    }
    </script>
    <?php endif; ?>
</body>
</html>
