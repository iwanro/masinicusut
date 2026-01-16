<?php
/**
 * Catalog Page
 * SUNDARI TOP STAR S.R.L.
 */
require_once '../config/config.php';

$pageTitle = 'Catalog Produse';

$db = db();

// Obține filtrele din URL
$search = $_GET['q'] ?? '';
$brandSlug = $_GET['brand'] ?? '';
$typeSlug = $_GET['type'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));

// Build query
$where = ['p.is_active = 1'];
$params = [];

// Search
if (!empty($search)) {
    $where[] = '(p.name LIKE ? OR p.description LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// Filter by brand
if (!empty($brandSlug)) {
    $where[] = 'c.slug = ?';
    $params[] = $brandSlug;
}

// Filter by type
if (!empty($typeSlug)) {
    $where[] = 'sc.slug = ?';
    $params[] = $typeSlug;
}

$whereClause = implode(' AND ', $where);

// Count total products
$countSql = "
    SELECT COUNT(DISTINCT p.id)
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    LEFT JOIN categories sc ON p.subcategory_id = sc.id
    WHERE $whereClause
";
$stmt = $db->prepare($countSql);
$stmt->execute($params);
$totalProducts = $stmt->fetchColumn();

// Get products with pagination
$offset = getOffset($page, PRODUCTS_PER_PAGE);
$sql = "
    SELECT p.id, p.name, p.slug, p.description, p.price, p.image, p.is_featured,
           c.name as brand_name, c.slug as brand_slug
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    LEFT JOIN categories sc ON p.subcategory_id = sc.id
    WHERE $whereClause
    ORDER BY p.created_at DESC
    LIMIT ? OFFSET ?
";
$stmt = $db->prepare($sql);
$stmt->execute(array_merge($params, [PRODUCTS_PER_PAGE, $offset]));
$products = $stmt->fetchAll();

// Get filters
$brands = getBrands();
$types = getProductTypes();

include SITE_ROOT . '/includes/header.php';
?>

<div class="container">
    <div class="catalog-layout">
        <!-- Sidebar Filters -->
        <aside class="sidebar">
            <div class="sidebar-section">
                <h3>Filtre</h3>

                <!-- Brands -->
                <div class="filter-group">
                    <h4>Mărci</h4>
                    <form id="filter-form" action="" method="GET">
                        <?php foreach ($brands as $brand): ?>
                            <label>
                                <input type="radio"
                                       name="brand"
                                       value="<?= $brand['slug'] ?>"
                                       <?= $brandSlug === $brand['slug'] ? 'checked' : '' ?>
                                       onchange="filterProducts()">
                                <?= e($brand['name']) ?>
                            </label>
                        <?php endforeach; ?>
                        <label>
                            <input type="radio"
                                   name="brand"
                                   value=""
                                   <?= empty($brandSlug) ? 'checked' : '' ?>
                                   onchange="filterProducts()">
                            Toate
                        </label>
                    </form>
                </div>

                <!-- Types -->
                <div class="filter-group">
                    <h4>Tip Produse</h4>
                    <form id="filter-form" action="" method="GET">
                        <?php foreach ($types as $type): ?>
                            <label>
                                <input type="radio"
                                       name="type"
                                       value="<?= $type['slug'] ?>"
                                       <?= $typeSlug === $type['slug'] ? 'checked' : '' ?>
                                       onchange="filterProducts()">
                                <?= e($type['name']) ?>
                            </label>
                        <?php endforeach; ?>
                        <label>
                            <input type="radio"
                                   name="type"
                                   value=""
                                   <?= empty($typeSlug) ? 'checked' : '' ?>
                                   onchange="filterProducts()">
                            Toate
                        </label>
                    </form>
                </div>

                <!-- Clear filters -->
                <?php if (!empty($brandSlug) || !empty($typeSlug) || !empty($search)): ?>
                    <a href="/pages/catalog.php" class="btn btn-outline" style="width: 100%">
                        <i class="fas fa-times"></i> Resetare Filtre
                    </a>
                <?php endif; ?>
            </div>
        </aside>

        <!-- Products Grid -->
        <div class="catalog-content">
            <!-- Page Title -->
            <div class="catalog-header">
                <h1>
                    <?php if (!empty($search)): ?>
                        Rezultate pentru "<?= e($search) ?>"
                    <?php elseif (!empty($brandSlug)): ?>
                        Produse <?= e($products[0]['brand_name'] ?? '') ?>
                    <?php elseif (!empty($typeSlug)): ?>
                        <?= e($typeSlug) ?>
                    <?php else: ?>
                        Toate Produsele
                    <?php endif; ?>
                </h1>
                <p class="results-count"><?= $totalProducts ?> produse</p>
            </div>

            <!-- Products -->
            <?php if (!empty($products)): ?>
                <div class="products-grid">
                    <?php foreach ($products as $product): ?>
                        <div class="product-card">
                            <div class="product-image">
                                <?php if ($product['image']): ?>
                                    <img src="<?= URL_PRODUCTS . '/' . e($product['image']) ?>"
                                         alt="<?= e($product['name']) ?>">
                                <?php else: ?>
                                    <img src="https://via.placeholder.com/300x200?text=Imagine+indisponibilă"
                                         alt="<?= e($product['name']) ?>">
                                <?php endif; ?>
                                <?php if ($product['is_featured']): ?>
                                    <span class="product-badge">Recomandat</span>
                                <?php endif; ?>
                            </div>
                            <div class="product-info">
                                <h3 class="product-title">
                                    <a href="/pages/product.php?slug=<?= e($product['slug']) ?>">
                                        <?= e($product['name']) ?>
                                    </a>
                                </h3>
                                <?php if ($product['description']): ?>
                                    <p class="product-description">
                                        <?= e(truncate($product['description'], 80)) ?>
                                    </p>
                                <?php endif; ?>
                                <div class="product-price"><?= formatPrice($product['price']) ?></div>
                                <button onclick="addToCart(<?= intval($product['id']) ?>)"
                                        class="btn btn-primary"
                                        style="width: 100%">
                                    <i class="fas fa-shopping-cart"></i> Adaugă în Coș
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php
                $totalPages = getTotalPages($totalProducts, PRODUCTS_PER_PAGE);
                if ($totalPages > 1):
                ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?= $page - 1 ?>&q=<?= e($search) ?>&brand=<?= e($brandSlug) ?>&type=<?= e($typeSlug) ?>"
                               class="btn btn-outline">« Previous</a>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <?php if ($i == $page): ?>
                                <span class="btn btn-primary"><?= $i ?></span>
                            <?php else: ?>
                                <a href="?page=<?= $i ?>&q=<?= e($search) ?>&brand=<?= e($brandSlug) ?>&type=<?= e($typeSlug) ?>"
                                   class="btn btn-outline"><?= $i ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?= $page + 1 ?>&q=<?= e($search) ?>&brand=<?= e($brandSlug) ?>&type=<?= e($typeSlug) ?>"
                               class="btn btn-outline">Next »</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="no-products">
                    <i class="fas fa-search"></i>
                    <h3>Nu am găsit produse</h3>
                    <p>Încearcă să modifici filtrele sau să cauți altceva.</p>
                    <a href="/pages/catalog.php" class="btn btn-primary">Vezi Toate Produsele</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
include SITE_ROOT . '/includes/footer.php';

$additionalCss = '<style>
.catalog-layout {
    display: grid;
    grid-template-columns: 250px 1fr;
    gap: 30px;
}

.catalog-header {
    margin-bottom: 30px;
}

.catalog-header h1 {
    font-size: 32px;
    margin-bottom: 10px;
}

.results-count {
    color: var(--text-light);
}

.no-products {
    text-align: center;
    padding: 60px 20px;
}

.no-products i {
    font-size: 64px;
    color: var(--text-lighter);
    margin-bottom: 20px;
}

.no-products h3 {
    font-size: 24px;
    margin-bottom: 10px;
}

.pagination {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin-top: 40px;
}

.pagination .btn {
    min-width: 40px;
}

@media (max-width: 768px) {
    .catalog-layout {
        grid-template-columns: 1fr;
    }

    .sidebar {
        margin-bottom: 30px;
    }
}
</style>';
