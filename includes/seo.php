<?php
/**
 * SEO Helper Functions
 * SUNDARI TOP STAR S.R.L.
 */

function getMetaTitle($pageTitle, $pageType = 'default') {
    $siteName = getSetting('site_name', SITE_NAME);
    $separator = ' | ';

    switch($pageType) {
        case 'home':
            return "Piese Mașini de Cusut {$separator}{$siteName} - Piese Originale & Accesorii";

        case 'catalog':
            $brand = $_GET['brand'] ?? '';
            $search = $_GET['q'] ?? '';
            if (!empty($search)) {
                return "Rezultate căutare: {$search} {$separator}{$siteName}";
            } elseif (!empty($brand)) {
                return "Produse {$brand} {$separator}{$siteName}";
            }
            return "Catalog Produse {$separator}{$siteName}";

        case 'product':
            global $product;
            $brand = $product['brand_name'] ?? '';
            return "{$pageTitle} - {$brand} {$separator}{$siteName}";

        case 'contact':
            return "Contact {$separator}{$siteName} - Telefon, Email, Program";

        default:
            return $pageTitle . $separator . $siteName;
    }
}

function getMetaDescription($pageType = 'default', $data = []) {
    $baseDesc = 'Piese, accesorii și consumabile pentru mașini de cusut. Calitate premium, livrare rapidă în toată România.';

    switch($pageType) {
        case 'home':
            return "Descoperă piese originale și accesorii pentru mașini de cusut la prețuri competitive. {$baseDesc}";

        case 'catalog':
            $brand = $data['brand'] ?? '';
            $count = $data['count'] ?? '';
            if ($brand && $count) {
                return "Vezi {$count} produse {$brand} disponibile. Piese originale, stoc real, livrare rapidă.";
            }
            return "Catalog complet piese mașini de cusut. Filtrează după marcă și tip. Stoc real, prețuri competitive.";

        case 'product':
            $name = $data['name'] ?? '';
            $brand = $data['brand'] ?? '';
            $shortDesc = truncate($data['description'] ?? '', 150);
            return "{$name} {$brand} - {$shortDesc}. Comandă online, livrare rapidă în România.";

        case 'contact':
            return "Contactează-ne pentru piese mașini de cusut. Telefon: 0766221688. Program: Luni-Vineri 09-18, Sâmbătă 10-14.";

        default:
            return $baseDesc;
    }
}

function getMetaKeywords($pageType = 'default', $data = []) {
    $baseKeywords = 'piese masini cusut, accesorii cusut, consumabile cusut, piese originale,';

    switch($pageType) {
        case 'home':
            return $baseKeywords . ' piese singer, piese brother, accesorii cusut industrial, livrare romania';

        case 'catalog':
            $brand = $data['brand'] ?? '';
            $keywords = $baseKeywords;
            if ($brand) $keywords .= " piese {$brand}, {$brand},";
            return rtrim($keywords, ',');

        case 'product':
            $name = $data['name'] ?? '';
            $brand = $data['brand'] ?? '';
            return "{$name}, {$brand}, piese {$brand}, cusut, industrial";

        default:
            return $baseKeywords;
    }
}

function getCanonicalUrl($pageType = 'default', $params = []) {
    $baseUrl = rtrim(SITE_URL, '/');

    switch($pageType) {
        case 'home':
            return $baseUrl . '/';
        case 'catalog':
            $url = $baseUrl . '/pages/catalog.php';
            if (!empty($params)) {
                $query = http_build_query($params);
                $url .= '?' . $query;
            }
            return $url;
        case 'product':
            return $baseUrl . '/pages/product.php?slug=' . ($params['slug'] ?? '');
        case 'contact':
            return $baseUrl . '/pages/contact.php';
        default:
            return currentUrl();
    }
}

function getOGTitle($title) {
    return $title;
}

function getOGDescription($description) {
    return truncate($description, 200);
}

function getOGImage($image = null) {
    if ($image) {
        return URL_PRODUCTS . '/' . $image;
    }
    return SITE_URL . '/assets/hero.webp'; // Folosește hero.webp ca default
}

function getOGType($pageType = 'default') {
    $types = [
        'home' => 'website',
        'product' => 'product',
        'catalog' => 'website',
        'contact' => 'website'
    ];
    return $types[$pageType] ?? 'website';
}

function getTwitterCard() {
    return 'summary_large_image';
}
