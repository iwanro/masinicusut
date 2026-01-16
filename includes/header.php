<?php
/**
 * Modern Header Include
 * SUNDARI TOP STAR S.R.L.
 * Premium Industrial Design
 */
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Basic Meta Tags -->
    <title><?= getMetaTitle($pageTitle ?? '', $pageType ?? 'home') ?></title>
    <meta name="description" content="<?= getMetaDescription($pageType ?? 'home', $seoData ?? []) ?>">
    <meta name="keywords" content="<?= getMetaKeywords($pageType ?? 'home', $seoData ?? []) ?>">
    <meta name="author" content="<?= e(getSetting('site_name', SITE_NAME)) ?>">
    <meta name="robots" content="index, follow">
    <meta name="language" content="ro">

    <!-- Canonical URL -->
    <link rel="canonical" href="<?= getCanonicalUrl($pageType ?? 'home', $seoParams ?? []) ?>">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="<?= getOGType($pageType ?? 'home') ?>">
    <meta property="og:url" content="<?= getCanonicalUrl($pageType ?? 'home', $seoParams ?? []) ?>">
    <meta property="og:title" content="<?= getOGTitle(getMetaTitle($pageTitle ?? '', $pageType ?? 'home')) ?>">
    <meta property="og:description" content="<?= getOGDescription(getMetaDescription($pageType ?? 'home', $seoData ?? [])) ?>">
    <meta property="og:image" content="<?= getOGImage($ogImage ?? null) ?>">
    <meta property="og:locale" content="ro_RO">
    <meta property="og:site_name" content="<?= e(getSetting('site_name', SITE_NAME)) ?>">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="<?= getTwitterCard() ?>">
    <meta name="twitter:url" content="<?= getCanonicalUrl($pageType ?? 'home', $seoParams ?? []) ?>">
    <meta name="twitter:title" content="<?= getOGTitle(getMetaTitle($pageTitle ?? '', $pageType ?? 'home')) ?>">
    <meta name="twitter:description" content="<?= getOGDescription(getMetaDescription($pageType ?? 'home', $seoData ?? [])) ?>">
    <meta name="twitter:image" content="<?= getOGImage($ogImage ?? null) ?>">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= URL_ASSETS ?>/favicon.ico">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- CSS -->
    <link rel="stylesheet" href="<?= URL_CSS ?>/style.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <?php if (isset($additionalCss)): ?>
        <?= $additionalCss ?>
    <?php endif; ?>
</head>
<body>
    <!-- Modern Header -->
    <header class="modern-header" id="main-header">
        <!-- Announcement Bar -->
        <div class="announcement-bar">
            <div class="container">
                <div class="announcement-text">
                    <i class="fas fa-truck-fast"></i>
                    <span>Livrare rapidă în toată România</span>
                </div>
                <div class="announcement-actions">
                    <?php if (isLoggedIn()): ?>
                        <span class="user-greeting">
                            <i class="fas fa-user-circle"></i>
                            <?= e($_SESSION['name']) ?>
                        </span>
                        <a href="/pages/account.php" class="header-link">Cont</a>
                        <?php if (isAdmin()): ?>
                            <a href="/admin/index.php" class="admin-badge">
                                <i class="fas fa-cog"></i> Admin
                            </a>
                        <?php endif; ?>
                        <a href="/pages/logout.php" class="header-link">
                            <i class="fas fa-sign-out-alt"></i>
                        </a>
                    <?php else: ?>
                        <a href="/pages/login.php" class="header-link">
                            <i class="fas fa-user"></i> Login
                        </a>
                        <a href="/pages/register.php" class="header-link header-link-highlight">
                            <i class="fas fa-user-plus"></i> Înregistrare
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Main Header Area -->
        <div class="main-header-area">
            <div class="container">
                <div class="header-content">
                    <!-- Logo -->
                    <div class="brand-logo">
                        <a href="/index.php">
                            <div class="logo-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                                    <path d="M2 17l10 5 10-5"/>
                                    <path d="M2 12l10 5 10-5"/>
                                </svg>
                            </div>
                            <div class="logo-text">
                                <h1><?= e(getSetting('site_name', SITE_NAME)) ?></h1>
                                <span class="logo-tagline">Piese Mașini de Cusut</span>
                            </div>
                        </a>
                    </div>

                    <!-- Search Bar -->
                    <div class="search-container">
                        <form action="/pages/catalog.php" method="GET" class="search-form">
                            <div class="search-input-wrapper">
                                <i class="fas fa-search search-icon"></i>
                                <input
                                    type="text"
                                    name="q"
                                    class="search-input"
                                    placeholder="Caută piese, accesorii..."
                                    value="<?= e($_GET['q'] ?? '') ?>"
                                >
                                <button type="submit" class="search-button">
                                    <span>Caută</span>
                                    <i class="fas fa-arrow-right"></i>
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Header Actions -->
                    <div class="header-actions">
                        <!-- Contact -->
                        <a href="tel:<?= e(getSetting('contact_phone', '0766221688')) ?>" class="action-item">
                            <div class="action-icon">
                                <i class="fas fa-phone-alt"></i>
                            </div>
                            <div class="action-text">
                                <span class="action-label">Sună-ne</span>
                                <span class="action-value"><?= e(getSetting('contact_phone', '0766221688')) ?></span>
                            </div>
                        </a>

                        <!-- Cart -->
                        <a href="/pages/cart.php" class="action-item cart-action">
                            <div class="action-icon">
                                <i class="fas fa-shopping-cart"></i>
                                <span class="cart-badge" id="cart-count">0</span>
                            </div>
                            <div class="action-text">
                                <span class="action-label">Coș</span>
                                <span class="action-value" id="cart-total">0 RON</span>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="main-navigation" id="main-nav">
            <div class="container">
                <div class="nav-wrapper">
                    <ul class="nav-menu">
                        <!-- Navigation links removed as requested -->
                    </ul>

                    <!-- Mobile Menu Toggle -->
                    <button class="mobile-menu-toggle" id="mobile-toggle" aria-label="Toggle menu">
                        <span class="hamburger"></span>
                        <span class="hamburger"></span>
                        <span class="hamburger"></span>
                    </button>
                </div>
            </div>
        </nav>
    </header>

    <!-- Mobile Menu Overlay -->
    <div class="mobile-menu-overlay" id="mobile-overlay"></div>

    <!-- Flash Messages -->
    <?php if (hasFlash()): ?>
        <?php $flash = getFlash(); ?>
        <div class="flash-message flash-<?= $flash['type'] ?>">
            <div class="container">
                <div class="flash-content">
                    <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : ($flash['type'] === 'error' ? 'exclamation-circle' : 'info-circle') ?>"></i>
                    <span><?= e($flash['message']) ?></span>
                </div>
                <button class="flash-close" onclick="this.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Main Content -->
    <main class="main-content">
