<?php
/**
 * Admin Products Management
 * SUNDARI TOP STAR S.R.L.
 */
require_once '../config/config.php';
requireAdmin();

$db = db();
$action = $_GET['action'] ?? 'list';
$productId = intval($_GET['id'] ?? 0);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        die('Eroare de securitate.');
    }

    if (isset($_POST['delete_product'])) {
        // Delete product
        $stmt = $db->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        setFlash('success', 'Produs șters cu succes!');
        redirect('products.php');
    }

    // Add/Edit product
    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? slugify($_POST['name'] ?? ''));
    $description = trim($_POST['description'] ?? '');
    $shortDescription = trim($_POST['short_description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);
    $sku = trim($_POST['sku'] ?? '');
    $categoryId = intval($_POST['category_id'] ?? 0) ?: null;
    $subcategoryId = intval($_POST['subcategory_id'] ?? 0) ?: null;
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;

    // Handle image upload
    $image = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = uploadImage($_FILES['image']);
        if ($uploadResult['success']) {
            $image = $uploadResult['filename'];
        }
    }

    if ($productId > 0) {
        // Update
        $sql = "UPDATE products SET name = ?, slug = ?, description = ?, short_description = ?,
                price = ?, stock = ?, sku = ?, category_id = ?, subcategory_id = ?,
                is_active = ?, is_featured = ?";
        $params = [$name, $slug, $description, $shortDescription, $price, $stock, $sku, $categoryId, $subcategoryId, $isActive, $isFeatured];

        if ($image) {
            $sql .= ", image = ?";
            $params[] = $image;
        }

        $sql .= " WHERE id = ?";
        $params[] = $productId;

        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        setFlash('success', 'Produs actualizat cu succes!');
    } else {
        // Insert
        $stmt = $db->prepare("
            INSERT INTO products (name, slug, description, short_description, price, stock, sku,
                                 category_id, subcategory_id, image, is_active, is_featured)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$name, $slug, $description, $shortDescription, $price, $stock,
                       $sku, $categoryId, $subcategoryId, $image, $isActive, $isFeatured]);

        setFlash('success', 'Produs adăugat cu succes!');
    }

    redirect('products.php');
}

// Get product for edit
$product = null;
if ($action === 'edit' && $productId > 0) {
    $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
}

// Get categories
$brands = getBrands();
$types = getProductTypes();

// Get all products for list
$page = max(1, intval($_GET['page'] ?? 1));
$search = $_GET['search'] ?? '';
$where = ['1=1'];
$params = [];

if (!empty($search)) {
    $where[] = '(name LIKE ? OR sku LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$whereClause = implode(' AND ', $where);

// Count
$countSql = "SELECT COUNT(*) FROM products WHERE $whereClause";
$stmt = $db->prepare($countSql);
$stmt->execute($params);
$totalProducts = $stmt->fetchColumn();

// Get products
$offset = getOffset($page, ADMIN_ITEMS_PER_PAGE);
$sql = "SELECT p.*, c.name as brand_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE $whereClause
        ORDER BY p.created_at DESC
        LIMIT " . ADMIN_ITEMS_PER_PAGE . " OFFSET $offset";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrare Produse - <?= SITE_NAME ?></title>
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
                <a href="products.php" class="active"><i class="fas fa-box"></i> <span>Produse</span></a>
                <a href="categories.php"><i class="fas fa-folder"></i> <span>Categorii</span></a>
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
                    if ($action === 'add'): echo 'Adaugă Produs Nou';
                    elseif ($action === 'edit'): echo 'Editează Produs';
                    else: echo 'Administrare Produse';
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
                        <form method="POST" enctype="multipart/form-data">
                            <?= getCsrfField() ?>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="name">Nume Produs *</label>
                                    <input type="text" id="name" name="name" class="form-control"
                                           value="<?= e($product['name'] ?? '') ?>" required
                                           oninput="document.getElementById('slug').value = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-')">
                                </div>

                                <div class="form-group">
                                    <label for="slug">Slug</label>
                                    <input type="text" id="slug" name="slug" class="form-control"
                                           value="<?= e($product['slug'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="price">Preț (RON) *</label>
                                    <input type="number" id="price" name="price" class="form-control"
                                           value="<?= $product['price'] ?? '' ?>" step="0.01" min="0" required>
                                </div>

                                <div class="form-group">
                                    <label for="stock">Stoc *</label>
                                    <input type="number" id="stock" name="stock" class="form-control"
                                           value="<?= $product['stock'] ?? '' ?>" min="0" required>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="sku">SKU (Cod Produs)</label>
                                    <input type="text" id="sku" name="sku" class="form-control"
                                           value="<?= e($product['sku'] ?? '') ?>">
                                </div>

                                <div class="form-group">
                                    <label for="category_id">Brand (Marcă)</label>
                                    <select id="category_id" name="category_id" class="form-control">
                                        <option value="">Selectează...</option>
                                        <?php foreach ($brands as $brand): ?>
                                            <option value="<?= $brand['id'] ?>"
                                                <?= ($product['category_id'] ?? '') == $brand['id'] ? 'selected' : '' ?>>
                                                <?= e($brand['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="subcategory_id">Tip Produs</label>
                                    <select id="subcategory_id" name="subcategory_id" class="form-control">
                                        <option value="">Selectează...</option>
                                        <?php foreach ($types as $type): ?>
                                            <option value="<?= $type['id'] ?>"
                                                <?= ($product['subcategory_id'] ?? '') == $type['id'] ? 'selected' : '' ?>>
                                                <?= e($type['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="short_description">Scurtă Descriere</label>
                                <input type="text" id="short_description" name="short_description" class="form-control"
                                       value="<?= e($product['short_description'] ?? '') ?>"
                                       placeholder="Max 500 caractere, apare în listă">
                            </div>

                            <div class="form-group">
                                <label for="description">Descriere Completă</label>
                                <textarea id="description" name="description" class="form-control" rows="6"><?= e($product['description'] ?? '') ?></textarea>
                            </div>

                            <div class="form-group">
                                <label for="image">Imagine Principală</label>
                                <input type="file" id="image" name="image" class="form-control" accept="image/*">
                                <?php if (!empty($product['image'])): ?>
                                    <small>Imagine curentă: <img src="<?= URL_PRODUCTS . '/' . e($product['image']) ?>" style="max-height: 50px; vertical-align: middle;"></small>
                                <?php endif; ?>
                            </div>

                            <div class="form-row">
                                <label style="display: flex; align-items: center; gap: 10px;">
                                    <input type="checkbox" name="is_active" <?= ($product['is_active'] ?? 1) ? 'checked' : '' ?>>
                                    Produs Activ
                                </label>

                                <label style="display: flex; align-items: center; gap: 10px;">
                                    <input type="checkbox" name="is_featured" <?= ($product['is_featured'] ?? 0) ? 'checked' : '' ?>>
                                    Produs Recomandat
                                </label>
                            </div>

                            <div style="margin-top: 20px;">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-save"></i> <?= $action === 'edit' ? 'Actualizează' : 'Adaugă' ?> Produs
                                </button>
                                <a href="products.php" class="btn btn-outline">Anulează</a>
                            </div>
                        </form>
                    </div>

                <?php else: ?>
                    <!-- Products List -->
                    <div class="admin-section">
                        <div class="action-bar">
                            <div class="search-box">
                                <form method="GET">
                                    <input type="text" name="search" placeholder="Caută produse..." value="<?= e($search) ?>">
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                                </form>
                            </div>
                            <a href="products.php?action=add" class="btn btn-success">
                                <i class="fas fa-plus"></i> Adaugă Produs
                            </a>
                        </div>

                        <div class="table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Imagine</th>
                                        <th>Nume</th>
                                        <th>Preț</th>
                                        <th>Stoc</th>
                                        <th>Brand</th>
                                        <th>Status</th>
                                        <th>Acțiuni</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($products)): ?>
                                        <?php foreach ($products as $prod): ?>
                                            <tr>
                                                <td><?= $prod['id'] ?></td>
                                                <td>
                                                    <?php if ($prod['image']): ?>
                                                        <img src="<?= URL_PRODUCTS . '/' . e($prod['image']) ?>"
                                                             style="width: 50px; height: 50px; object-fit: cover;">
                                                    <?php else: ?>
                                                        -
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <strong><?= e($prod['name']) ?></strong><br>
                                                    <small class="text-muted">SKU: <?= e($prod['sku'] ?? '-') ?></small>
                                                </td>
                                                <td><?= formatPrice($prod['price']) ?></td>
                                                <td>
                                                    <span style="color: <?= $prod['stock'] > 0 ? 'green' : 'red' ?>">
                                                        <?= $prod['stock'] ?>
                                                    </span>
                                                </td>
                                                <td><?= e($prod['brand_name'] ?? '-') ?></td>
                                                <td>
                                                    <?php if ($prod['is_active']): ?>
                                                        <span class="status-badge status-completed">Activ</span>
                                                    <?php else: ?>
                                                        <span class="status-badge status-cancelled">Inactiv</span>
                                                    <?php endif; ?>
                                                    <?php if ($prod['is_featured']): ?>
                                                        <span class="status-badge status-shipped" style="margin-left: 5px;">★</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <a href="products.php?action=edit&id=<?= $prod['id'] ?>"
                                                       class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
                                                    <a href="../pages/product.php?slug=<?= e($prod['slug']) ?>"
                                                       class="btn btn-sm btn-outline" target="_blank"><i class="fas fa-eye"></i></a>
                                                    <button onclick="deleteProduct(<?= $prod['id'] ?>)"
                                                            class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center">Nu există produse.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php
                        $totalPages = getTotalPages($totalProducts, ADMIN_ITEMS_PER_PAGE);
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

    <?php if ($action === 'list'): ?>
    <script>
    function deleteProduct(id) {
        if (confirm('Ești sigur că vrei să ștergi acest produs?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = '<input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= generateCsrfToken() ?>">' +
                           '<input type="hidden" name="delete_product" value="1">';
            document.body.appendChild(form);
            form.action = 'products.php?action=edit&id=' + id;
            form.submit();
        }
    }
    </script>
    <?php endif; ?>
</body>
</html>
