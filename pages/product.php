<?php
/**
 * Product Detail Page
 * SUNDARI TOP STAR S.R.L.
 */
require_once '../config/config.php';

$db = db();

// Obține slug-ul din URL
$slug = $_GET['slug'] ?? '';

if (empty($slug)) {
    redirect('/pages/catalog.php');
}

// Get product
$stmt = $db->prepare("
    SELECT p.*,
           c.name as brand_name,
           c.slug as brand_slug,
           sc.name as type_name,
           sc.slug as type_slug
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    LEFT JOIN categories sc ON p.subcategory_id = sc.id
    WHERE p.slug = ? AND p.is_active = 1
");
$stmt->execute([$slug]);
$product = $stmt->fetch();

if (!$product) {
    setFlash('error', 'Produsul nu a fost găsit.');
    redirect('/pages/catalog.php');
}

$pageTitle = $product['name'];

// Parse images JSON if exists
$images = [];
if (!empty($product['images'])) {
    $images = json_decode($product['images'], true) ?? [];
}

include SITE_ROOT . '/includes/header.php';
?>

<div class="container">
    <!-- Breadcrumb -->
    <nav class="breadcrumb">
        <a href="/index.php">Acasă</a>
        <span>›</span>
        <a href="/pages/catalog.php">Catalog</a>
        <?php if ($product['brand_slug']): ?>
            <span>›</span>
            <a href="/pages/catalog.php?brand=<?= $product['brand_slug'] ?>">
                <?= e($product['brand_name']) ?>
            </a>
        <?php endif; ?>
        <span>›</span>
        <span><?= e($product['name']) ?></span>
    </nav>

    <!-- Product Detail -->
    <div class="product-detail">
        <!-- Product Images -->
        <div class="product-gallery">
            <div class="main-image">
                <?php if ($product['image']): ?>
                    <img src="<?= URL_PRODUCTS . '/' . e($product['image']) ?>"
                         alt="<?= e($product['name']) ?>"
                         id="main-product-image">
                <?php else: ?>
                    <img src="https://via.placeholder.com/500x500?text=Imagine+indisponibilă"
                         alt="<?= e($product['name']) ?>">
                <?php endif; ?>
            </div>
            <?php if (!empty($images)): ?>
                <div class="thumbnail-images">
                    <?php foreach ($images as $img): ?>
                        <img src="<?= URL_PRODUCTS . '/' . e($img) ?>"
                             alt="<?= e($product['name']) ?>"
                             onclick="changeMainImage('<?= URL_PRODUCTS . '/' . e($img) ?>')">
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Product Info -->
        <div class="product-info-detail">
            <h1><?= e($product['name']) ?></h1>

            <?php if ($product['sku']): ?>
                <p class="product-sku">Cod: <?= e($product['sku']) ?></p>
            <?php endif; ?>

            <div class="product-price-large">
                <?= formatPrice($product['price']) ?>
                <small><?= getSetting('currency', 'RON') ?></small>
            </div>

            <?php if ($product['description']): ?>
                <p class="short-description"><?= e(truncate($product['description'], 150)) ?></p>
            <?php endif; ?>

            <!-- Stock status -->
            <div class="stock-status">
                <?php if ($product['stock'] > 0): ?>
                    <span class="in-stock">
                        <i class="fas fa-check-circle"></i> În stoc (<?= $product['stock'] ?> disponibile)
                    </span>
                <?php else: ?>
                    <span class="out-stock">
                        <i class="fas fa-times-circle"></i> Stoc epuizat
                    </span>
                <?php endif; ?>
            </div>

            <!-- Add to cart form -->
            <?php if ($product['stock'] > 0): ?>
                <form id="add-to-cart-form" class="add-to-cart-form">
                    <div class="quantity-selector">
                        <label>Cantitate:</label>
                        <input type="number"
                               id="quantity"
                               name="quantity"
                               value="1"
                               min="1"
                               max="<?= $product['stock'] ?>"
                               class="form-control"
                               style="width: 80px">
                    </div>
                    <button type="button"
                            onclick="addToCartWithQuantity(<?= intval($product['id']) ?>)"
                            class="btn btn-primary btn-lg">
                        <i class="fas fa-shopping-cart"></i> Adaugă în Coș
                    </button>
                </form>
            <?php else: ?>
                <button class="btn btn-secondary btn-lg" disabled>
                    Stoc Epuizat
                </button>
            <?php endif; ?>

            <!-- Product Meta -->
            <div class="product-meta">
                <?php if ($product['brand_name']): ?>
                    <p><strong>Brand:</strong>
                        <a href="/pages/catalog.php?brand=<?= $product['brand_slug'] ?>">
                            <?= e($product['brand_name']) ?>
                        </a>
                    </p>
                <?php endif; ?>

                <?php if ($product['type_name']): ?>
                    <p><strong>Categorie:</strong>
                        <a href="/pages/catalog.php?type=<?= $product['type_slug'] ?>">
                            <?= e($product['type_name']) ?>
                        </a>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Product Description -->
    <?php if ($product['description']): ?>
        <div class="product-description-section">
            <h2>Descriere</h2>
            <div class="description-content">
                <?= nl2br(e($product['description'])) ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function changeMainImage(src) {
    document.getElementById('main-product-image').src = src;
}

function addToCartWithQuantity(productId) {
    const quantity = parseInt(document.getElementById('quantity').value) || 1;
    addToCart(productId, quantity);
}
</script>

<?php
include SITE_ROOT . '/includes/footer.php';

$additionalCss = '<style>
.breadcrumb {
    padding: 20px 0;
    display: flex;
    align-items: center;
    gap: 10px;
    color: var(--text-light);
    font-size: 14px;
}

.breadcrumb a {
    color: var(--accent-color);
}

.product-detail {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 40px;
    margin: 40px 0;
}

.product-gallery {
    position: sticky;
    top: 100px;
}

.main-image {
    background-color: #fff;
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius);
    overflow: hidden;
    margin-bottom: 15px;
}

.main-image img {
    width: 100%;
    height: auto;
    display: block;
}

.thumbnail-images {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 10px;
}

.thumbnail-images img {
    width: 100%;
    height: 80px;
    object-fit: cover;
    border: 2px solid transparent;
    border-radius: var(--border-radius);
    cursor: pointer;
    transition: var(--transition);
}

.thumbnail-images img:hover {
    border-color: var(--accent-color);
}

.product-info-detail h1 {
    font-size: 32px;
    margin-bottom: 15px;
}

.product-sku {
    color: var(--text-light);
    font-size: 14px;
    margin-bottom: 20px;
}

.product-price-large {
    font-size: 36px;
    font-weight: 700;
    color: var(--secondary-color);
    margin-bottom: 20px;
}

.product-price-large small {
    font-size: 18px;
    font-weight: 400;
}

.short-description {
    font-size: 18px;
    color: var(--text-light);
    margin-bottom: 20px;
}

.stock-status {
    margin-bottom: 30px;
}

.in-stock {
    color: var(--success-color);
    font-weight: 500;
}

.out-stock {
    color: var(--danger-color);
    font-weight: 500;
}

.add-to-cart-form {
    display: flex;
    gap: 15px;
    align-items: center;
    margin-bottom: 30px;
}

.quantity-selector {
    display: flex;
    align-items: center;
    gap: 10px;
}

.product-meta {
    padding: 20px 0;
    border-top: 1px solid var(--border-color);
}

.product-meta p {
    margin-bottom: 10px;
}

.product-description-section {
    margin: 60px 0;
}

.product-description-section h2 {
    font-size: 24px;
    margin-bottom: 20px;
}

.description-content {
    line-height: 1.8;
    color: var(--text-light);
}

@media (max-width: 768px) {
    .product-detail {
        grid-template-columns: 1fr;
    }

    .product-gallery {
        position: static;
    }

    .product-info-detail h1 {
        font-size: 24px;
    }

    .product-price-large {
        font-size: 28px;
    }

    .add-to-cart-form {
        flex-direction: column;
    }
}
</style>';
