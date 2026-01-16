<?php
/**
 * Footer Include
 * SUNDARI TOP STAR S.R.L.
 */
?>
    </main>

    <!-- Footer -->
    <footer class="site-footer">
        <div class="container">
            <div class="footer-grid">
                <!-- Despre -->
                <div class="footer-col">
                    <h3>Despre Noi</h3>
                    <p><?= e(getSetting('site_description', 'Piese, accesorii și consumabile pentru mașini de cusut')) ?></p>
                    <p><strong>SUNDARI TOP STAR S.R.L.</strong></p>
                    <div class="legal-info">
                        <p><strong>Reg. Com.:</strong> J16/1268/25.05.2022</p>
                        <p><strong>CUI:</strong> 46190930</p>
                    </div>
                </div>

                <!-- Link-uri rapide -->
                <div class="footer-col">
                    <h3>Link-uri Rapide</h3>
                    <ul class="footer-links">
                        <li><a href="/index.php">Acasă</a></li>
                        <li><a href="/pages/catalog.php">Catalog Produse</a></li>
                        <li><a href="/pages/contact.php">Contact</a></li>
                        <?php if (isLoggedIn()): ?>
                            <li><a href="/pages/account.php">Contul Meu</a></li>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- Categorii -->
                <div class="footer-col">
                    <h3>Categorii</h3>
                    <ul class="footer-links">
                        <?php
                        $brands = getBrands();
                        foreach (array_slice($brands, 0, 5) as $brand):
                        ?>
                            <li><a href="/pages/catalog.php?brand=<?= $brand['slug'] ?>"><?= e($brand['name']) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Contact -->
                <div class="footer-col">
                    <h3>Contact</h3>
                    <ul class="contact-info">
                        <li><i class="fas fa-phone"></i> <?= e(getSetting('contact_phone', '0766221688')) ?></li>
                        <li><i class="fas fa-envelope"></i> <?= e(getSetting('contact_email', '')) ?></li>
                        <li><i class="fas fa-map-marker-alt"></i> România</li>
                    </ul>
                    <div class="social-links">
                        <a href="#" class="social-link"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>

            <!-- Copyright -->
            <div class="footer-bottom">
                <div class="footer-bottom-content">
                    <p>&copy; <?= date('Y') ?> <?= e(getSetting('site_name', SITE_NAME)) ?>. Toate drepturile rezervate.</p>
                    <p class="footer-credit">
                        Created by: <a href="https://iwan.ro" target="_blank" rel="noopener">IWAN</a>
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Structured Data -->
    <?= getWebSiteSchema() ?>
    <?= getOrganizationSchema() ?>
    <?= getLocalBusinessSchema() ?>
    <?php if (isset($productSchema)): ?>
        <?= $productSchema ?>
    <?php endif; ?>
    <?php if (isset($breadcrumbSchema)): ?>
        <?= $breadcrumbSchema ?>
    <?php endif; ?>

    <!-- JavaScript -->
    <script src="<?= URL_JS ?>/main.js"></script>
    <?php if (isset($additionalJs)): ?>
        <?= $additionalJs ?>
    <?php endif; ?>

    <!-- Cart update script -->
    <script>
        // Update cart count on page load
        fetch('/api/cart.php?action=count')
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('cart-count').textContent = data.count;
                }
            })
            .catch(err => console.error('Cart count error:', err));
    </script>
</body>
</html>
