<?php
/**
 * Header Include
 * SUNDARI TOP STAR S.R.L.
 */
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= e(getSetting('site_description', 'Piese, accesorii și consumabile pentru mașini de cusut')) ?>">
    <title><?= isset($pageTitle) ? e($pageTitle) . ' - ' : '' ?><?= e(getSetting('site_name', SITE_NAME)) ?></title>

    <!-- CSS -->
    <link rel="stylesheet" href="<?= URL_CSS ?>/style.css">

    <!-- Font Awesome (optional - CDN) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <?php if (isset($additionalCss)): ?>
        <?= $additionalCss ?>
    <?php endif; ?>
</head>
<body>
    <!-- Header -->
    <header class="site-header">
        <div class="container">
            <!-- Top bar -->
            <div class="top-bar">
                <div class="contact-info">
                    <span><i class="fas fa-phone"></i> <?= e(getSetting('contact_phone', '+40 700 000 000')) ?></span>
                    <span><i class="fas fa-envelope"></i> <?= e(getSetting('contact_email', 'contact@sundari.ro')) ?></span>
                </div>
                <div class="top-links">
                    <?php if (isLoggedIn()): ?>
                        <span>Salut, <?= e($_SESSION['name']) ?>!</span>
                        <a href="/pages/account.php">Contul meu</a>
                        <?php if (isAdmin()): ?>
                            <a href="/admin/index.php" class="admin-link">Admin</a>
                        <?php endif; ?>
                        <a href="/pages/logout.php">Logout</a>
                    <?php else: ?>
                        <a href="/pages/login.php">Login</a>
                        <a href="/pages/register.php">Înregistrare</a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Main header -->
            <div class="main-header">
                <div class="logo">
                    <a href="/index.php">
                        <h1><?= e(getSetting('site_name', SITE_NAME)) ?></h1>
                        <p class="tagline">Piese Mașini de Cusut</p>
                    </a>
                </div>

                <!-- Search -->
                <div class="search-bar">
                    <form action="/pages/catalog.php" method="GET">
                        <input type="text" name="q" placeholder="Caută piese..." value="<?= e($_GET['q'] ?? '') ?>">
                        <button type="submit"><i class="fas fa-search"></i></button>
                    </form>
                </div>

                <!-- Cart -->
                <div class="cart-link">
                    <a href="/pages/cart.php">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-count" id="cart-count">0</span>
                    </a>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="main-nav">
                <ul class="nav-menu">
                    <li><a href="/index.php" class="<?= isActivePage('index.php') ? 'active' : '' ?>">Acasă</a></li>
                    <li><a href="/pages/catalog.php" class="<?= isActivePage('catalog.php') || isActivePage('product.php') ? 'active' : '' ?>">Catalog</a></li>
                    <li><a href="/pages/contact.php" class="<?= isActivePage('contact.php') ? 'active' : '' ?>">Contact</a></li>
                </ul>

                <!-- Mobile menu toggle -->
                <button class="mobile-menu-toggle" id="mobile-menu-toggle">
                    <i class="fas fa-bars"></i>
                </button>
            </nav>
        </div>
    </header>

    <!-- Flash Messages -->
    <?php if (hasFlash()): ?>
        <?php $flash = getFlash(); ?>
        <div class="flash-message flash-<?= $flash['type'] ?>">
            <div class="container">
                <?= e($flash['message']) ?>
                <button class="flash-close" onclick="this.parentElement.remove()">&times;</button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Main Content -->
    <main class="main-content">
