<?php
/**
 * Homepage
 * SUNDARI TOP STAR S.R.L.
 */
require_once 'config/config.php';

// Include SEO functions
require_once SITE_ROOT . '/includes/seo.php';

$pageType = 'home';
$pageTitle = 'Acasă';
$seoData = [];
$seoParams = [];

// Obține produsele featured
$db = db();
$stmt = $db->prepare("
    SELECT id, name, slug, description, price, image
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

<!-- Enhanced Hero Section -->
<section class="hero-section">
    <div class="hero-background"></div>
    <div class="container">
        <div class="hero-content">
            <div class="hero-badge">
                <i class="fas fa-star"></i>
                <span>Calitate Premium</span>
            </div>
            <h1>Piese și Accesorii pentru Mașini de Cusut</h1>
            <p class="hero-subtitle">Găsește tot ce ai nevoie pentru mașina ta de cusut - piese originale, accesorii și consumabile</p>
            <div class="hero-stats">
                <div class="stat-item">
                    <strong>500+</strong>
                    <span>Produse</span>
                </div>
                <div class="stat-item">
                    <strong>50+</strong>
                    <span>Mărci</span>
                </div>
                <div class="stat-item">
                    <strong>24h</strong>
                    <span>Livrare</span>
                </div>
            </div>
            <div class="hero-buttons">
                <a href="/pages/catalog.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-shopping-bag"></i> Vezi Catalog
                </a>
                <a href="/pages/contact.php" class="btn btn-outline btn-lg">
                    <i class="fas fa-headset"></i> Contact
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Brands Section -->
<?php if (!empty($brands)): ?>
<section class="brands-section">
    <div class="container">
        <h2 class="section-title">Mărci Populare</h2>
        <div class="brands-carousel-container">
            <button class="carousel-btn carousel-prev" onclick="scrollBrands(-1)" aria-label="Previous">
                <i class="fas fa-chevron-left"></i>
            </button>
            
            <div class="brands-carousel" id="brands-carousel">
                <?php foreach ($brands as $brand): ?>
                    <a href="/pages/catalog.php?brand=<?= $brand['slug'] ?>" class="brand-card">
                        <div class="brand-card-inner">
                            <div class="brand-icon">
                                <i class="fas fa-certificate"></i>
                            </div>
                            <h3 class="brand-name"><?= e($brand['name']) ?></h3>
                            <div class="brand-overlay"></div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
            
            <button class="carousel-btn carousel-next" onclick="scrollBrands(1)" aria-label="Next">
                <i class="fas fa-chevron-right"></i>
            </button>
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
                        <?php if ($product['description']): ?>
                            <p class="product-description"><?= e(truncate($product['description'], 80)) ?></p>
                        <?php endif; ?>
                        <div class="product-price"><?= formatPrice($product['price']) ?></div>
                        <div class="product-actions">
                            <button onclick="addToCart(<?= intval($product['id']) ?>)" class="btn btn-primary" style="width: 100%">
                                <i class="fas fa-shopping-cart"></i> Adaugă în Coș
                            </button>
                        </div>
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
    position: relative;
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 50%, var(--accent-color) 100%);
    color: #fff;
    padding: 60px 0;
    overflow: hidden;
}

.hero-background {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: radial-gradient(circle at 20% 50%, rgba(249, 115, 22, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 50%, rgba(15, 23, 42, 0.1) 0%, transparent 50%);
    pointer-events: none;
}

.hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(255, 255, 255, 0.2);
    padding: 8px 20px;
    border-radius: 50px;
    font-size: 0.875rem;
    font-weight: 600;
    margin-bottom: 20px;
}

.hero-content h1 {
    font-size: 36px;
    margin-bottom: 16px;
}

.hero-subtitle {
    font-size: 16px;
    margin-bottom: 30px;
    opacity: 0.9;
}

.hero-stats {
    display: flex;
    justify-content: center;
    gap: 40px;
    margin-bottom: 30px;
}

.stat-item {
    text-align: center;
}

.stat-item strong {
    display: block;
    font-size: 2rem;
    font-weight: 800;
    color: var(--accent-color);
}

.stat-item span {
    font-size: 0.875rem;
    opacity: 0.8;
}

.hero-buttons {
    display: flex;
    gap: 15px;
    justify-content: center;
}

.featured-section {
    padding: 60px 0;
}

/* Brands Carousel */
.brands-carousel-container {
    position: relative;
    padding: 0 60px;
}

.brands-carousel {
    display: flex;
    gap: 20px;
    overflow-x: auto;
    scroll-behavior: smooth;
    scrollbar-width: none;
    -ms-overflow-style: none;
    padding: 10px 0;
}

.brands-carousel::-webkit-scrollbar {
    display: none;
}

.brand-card {
    flex: 0 0 200px;
    min-width: 200px;
}

.carousel-btn {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: white;
    border: 2px solid var(--border-light);
    width: 44px;
    height: 44px;
    border-radius: 50%;
    cursor: pointer;
    z-index: 10;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--primary-color);
    transition: var(--transition-base);
    box-shadow: var(--shadow-md);
}

.carousel-btn:hover {
    background: var(--accent-color);
    color: white;
    border-color: var(--accent-color);
    transform: translateY(-50%) scale(1.1);
}

.carousel-btn:active {
    transform: translateY(-50%) scale(0.95);
}

.carousel-prev {
    left: 0;
}

.carousel-next {
    right: 0;
}

.carousel-btn i {
    font-size: 1.125rem;
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

    .brands-carousel-container {
        padding: 0 50px;
    }
    
    .carousel-btn {
        width: 36px;
        height: 36px;
    }
    
    .brand-card {
        flex: 0 0 160px;
        min-width: 160px;
    }
}
</style>';
?>

<script>
// Brands Carousel Navigation
function scrollBrands(direction) {
    const carousel = document.getElementById('brands-carousel');
    if (!carousel) return;
    
    const scrollAmount = 240; // Width of card + gap
    carousel.scrollBy({
        left: direction * scrollAmount,
        behavior: 'smooth'
    });
}

// Optional: Auto-hide buttons at scroll edges
document.addEventListener('DOMContentLoaded', function() {
    const carousel = document.getElementById('brands-carousel');
    if (!carousel) return;
    
    const prevBtn = document.querySelector('.carousel-prev');
    const nextBtn = document.querySelector('.carousel-next');
    
    function updateButtons() {
        if (!prevBtn || !nextBtn) return;
        
        const isAtStart = carousel.scrollLeft <= 0;
        const isAtEnd = carousel.scrollLeft >= carousel.scrollWidth - carousel.clientWidth - 10;
        
        prevBtn.style.opacity = isAtStart ? '0.3' : '1';
        prevBtn.style.pointerEvents = isAtStart ? 'none' : 'auto';
        
        nextBtn.style.opacity = isAtEnd ? '0.3' : '1';
        nextBtn.style.pointerEvents = isAtEnd ? 'none' : 'auto';
    }
    
    carousel.addEventListener('scroll', updateButtons);
    updateButtons(); // Initial check
});
</script>
?>
