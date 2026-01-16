<?php
/**
 * Dynamic XML Sitemap Generator
 */
require_once 'config/config.php';

header('Content-Type: application/xml; charset=utf-8');

$db = db();
$baseUrl = rtrim(SITE_URL, '/');

$xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
$xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

// Static pages
$staticPages = [
    ['url' => '/index.php', 'changefreq' => 'daily', 'priority' => '1.0'],
    ['url' => '/pages/catalog.php', 'changefreq' => 'daily', 'priority' => '0.9'],
    ['url' => '/pages/contact.php', 'changefreq' => 'monthly', 'priority' => '0.5']
];

foreach ($staticPages as $page) {
    $xml .= "  <url>\n";
    $xml .= "    <loc>{$baseUrl}{$page['url']}</loc>\n";
    $xml .= "    <lastmod>" . date('Y-m-d') . "</lastmod>\n";
    $xml .= "    <changefreq>{$page['changefreq']}</changefreq>\n";
    $xml .= "    <priority>{$page['priority']}</priority>\n";
    $xml .= "  </url>\n";
}

// Products
$stmt = $db->prepare("SELECT slug, updated_at FROM products WHERE is_active = 1 ORDER BY updated_at DESC");
$stmt->execute();
$products = $stmt->fetchAll();

foreach ($products as $product) {
    $xml .= "  <url>\n";
    $xml .= "    <loc>{$baseUrl}/pages/product.php?slug={$product['slug']}</loc>\n";
    $xml .= "    <lastmod>" . date('Y-m-d', strtotime($product['updated_at'])) . "</lastmod>\n";
    $xml .= "    <changefreq>weekly</changefreq>\n";
    $xml .= "    <priority>0.8</priority>\n";
    $xml .= "  </url>\n";
}

// Brands
$stmt = $db->prepare("SELECT slug FROM categories WHERE type = 'brand' AND is_active = 1 ORDER BY name ASC");
$stmt->execute();
$brands = $stmt->fetchAll();

foreach ($brands as $brand) {
    $xml .= "  <url>\n";
    $xml .= "    <loc>{$baseUrl}/pages/catalog.php?brand={$brand['slug']}</loc>\n";
    $xml .= "    <lastmod>" . date('Y-m-d') . "</lastmod>\n";
    $xml .= "    <changefreq>weekly</changefreq>\n";
    $xml .= "    <priority>0.7</priority>\n";
    $xml .= "  </url>\n";
}

$xml .= '</urlset>';
echo $xml;
