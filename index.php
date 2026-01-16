<?php
/**
 * Homepage
 * SUNDARI TOP STAR S.R.L.
 */
require_once 'config/config.php';

$pageTitle = 'Acasă';

// Obține produsele featured
$db = db();
$stmt = $db->prepare("
    SELECT id, name, slug, short_description, price, image
    FROM products
    WHERE is_featured = 1 AND is_active = 1
    ORDER BY created_at DESC
    LIMIT 8
");
$stmt->execute();
$featuredProducts = $stmt->fetchAll();

// Obține mărcile pentru hero section
$brands = getBrands();

include SITE_ROOT . '/includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="hero-content">
            <h1>Piese și Accesorii pentru Mașini de Cusut</h1>
            <p>Găsește tot ce ai nevoie pentru mașina ta de cusut - piese originale, accesorii și consumabile</p>
            <div class="hero-buttons">
                <a href="/pages/catalog.php" class="btn btn-primary btn-lg">Vezi Catalog</a>
                <a href="/pages/contact.php" class="btn btn-outline btn-lg">Contact</a>
            </div>
        </div>
    </div>
</section>

<!-- Brands Section -->
<?php if (!empty($brands)): ?>
<section class="brands-section">
    <div class="container">
        <h2 class="section-title">Mărci Populare</h2>
        <div class="brands-grid">
            <?php foreach ($brands as $brand): ?>
                <a href="/pages/catalog.php?brand=<?= $brand['slug'] ?>" class="brand-card">
                    <?= e($brand['name']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Featured Products -->
<?php if (!empty($featuredProducts)): ?>
<section class="featured-section">
    <div class="container">
        <h2 class="section-title">Produse Recomandate</h2>
        <div class="products-grid">
            <?php foreach ($featuredProducts as $product): ?>
                <div class="product-card">
                    <div class="product-image">
                        <?php if ($product['image']): ?>
                            <img src="<?= URL_PRODUCTS . '/' . e($product['image']) ?>" alt="<?= e($product['name']) ?>">
                        <?php else: ?>
                            <img src="https://via.placeholder.com/300x200?text=Imagine+indisponibilă" alt="<?= e($product['name']) ?>">
                        <?php endif; ?>
                        <span class="product-badge">Recomandat</span>
                    </div>
                    <div class="product-info">
                        <h3 class="product-title">
                            <a href="/pages/product.php?slug=<?= e($product['slug']) ?>">
                                <?= e($product['name']) ?>
                            </a>
                        </h3>
                        <?php if ($product['short_description']): ?>
                            <p class="product-description"><?= e(truncate($product['short_description'], 80)) ?></p>
                        <?php endif; ?>
                        <div class="product-price"><?= formatPrice($product['price']) ?></div>
                        <button onclick="addToCart(<?= $product['id'] ?>)" class="btn btn-primary" style="width: 100%">
                            <i class="fas fa-shopping-cart"></i> Adaugă în Coș
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center" style="margin-top: 40px;">
            <a href="/pages/catalog.php" class="btn btn-secondary btn-lg">Vezi Toate Produsele</a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Features Section -->
<section class="features-section">
    <div class="container">
        <h2 class="section-title">De Ce Noi?</h2>
        <div class="features-grid">
            <div class="feature-card">
                <i class="fas fa-certificate"></i>
                <h3>Piese Originale</h3>
                <p>Comercializăm doar piese originale și accesorii de calitate.</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-shipping-fast"></i>
                <h3>Livrare Rapidă</h3>
                <p>Expediem produsele în cel mai scurt timp posibil.</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-headset"></i>
                <h3>Suport Tehnic</h3>
                <p>Te ajutăm să găsești piesa potrivită pentru mașina ta.</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-tags"></i>
                <h3>Prețuri Competitive</h3>
                <p>Cele mai bune prețuri la piese de mașini de cusut.</p>
            </div>
        </div>
    </div>
</section>

<?php
include SITE_ROOT . '/includes/footer.php';

// Add homepage specific styles
$additionalCss = '<style>
.hero-section {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
    color: #fff;
    padding: 80px 0;
    text-align: center;
}

.hero-content h1 {
    font-size: 48px;
    margin-bottom: 20px;
}

.hero-content p {
    font-size: 20px;
    margin-bottom: 30px;
    opacity: 0.9;
}

.hero-buttons {
    display: flex;
    gap: 15px;
    justify-content: center;
}

.brands-section,
.featured-section,
.features-section {
    padding: 60px 0;
}

.section-title {
    text-align: center;
    font-size: 32px;
    margin-bottom: 40px;
    color: var(--primary-color);
}

.brands-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 20px;
}

.brand-card {
    background-color: var(--bg-white);
    padding: 20px;
    text-align: center;
    border: 2px solid var(--border-color);
    border-radius: var(--border-radius);
    font-weight: 600;
    transition: var(--transition);
}

.brand-card:hover {
    border-color: var(--accent-color);
    color: var(--accent-color);
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 30px;
}

.feature-card {
    text-align: center;
    padding: 30px;
}

.feature-card i {
    font-size: 48px;
    color: var(--accent-color);
    margin-bottom: 20px;
}

.feature-card h3 {
    font-size: 20px;
    margin-bottom: 15px;
}

.text-center {
    text-align: center;
}

@media (max-width: 768px) {
    .hero-content h1 {
        font-size: 32px;
    }

    .hero-content p {
        font-size: 16px;
    }

    .hero-buttons {
        flex-direction: column;
    }

    .brands-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>';
