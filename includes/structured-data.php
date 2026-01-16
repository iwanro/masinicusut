<?php
/**
 * Structured Data (Schema.org)
 * SUNDARI TOP STAR S.R.L.
 */

function getOrganizationSchema() {
    $data = [
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => getSetting('site_name', SITE_NAME),
        'url' => SITE_URL,
        'description' => getSetting('site_description', 'Piese, accesorii și consumabile pentru mașini de cusut'),
        'address' => [
            '@type' => 'PostalAddress',
            'addressCountry' => 'RO'
        ],
        'contactPoint' => [
            '@type' => 'ContactPoint',
            'telephone' => '+40' . getSetting('contact_phone', '0766221688'),
            'contactType' => 'customer service',
            'email' => getSetting('contact_email', 'contact@sundari.ro')
        ]
    ];

    return '<script type="application/ld+json">' . json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
}

function getLocalBusinessSchema() {
    $data = [
        '@context' => 'https://schema.org',
        '@type' => 'LocalBusiness',
        'name' => getSetting('site_name', SITE_NAME),
        'telephone' => '+40' . getSetting('contact_phone', '0766221688'),
        'email' => getSetting('contact_email', 'contact@sundari.ro'),
        'openingHoursSpecification' => [
            [
                '@type' => 'OpeningHoursSpecification',
                'dayOfWeek' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
                'opens' => '09:00',
                'closes' => '18:00'
            ],
            [
                '@type' => 'OpeningHoursSpecification',
                'dayOfWeek' => 'Saturday',
                'opens' => '10:00',
                'closes' => '14:00'
            ]
        ],
        'priceRange' => '$$'
    ];

    return '<script type="application/ld+json">' . json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
}

function getProductSchema($product) {
    $data = [
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => $product['name'],
        'description' => strip_tags($product['description'] ?? ''),
        'sku' => $product['sku'] ?? '',
        'image' => [SITE_URL . '/assets/images/products/' . $product['image']],
        'offers' => [
            '@type' => 'Offer',
            'url' => SITE_URL . '/pages/product.php?slug=' . $product['slug'],
            'priceCurrency' => 'RON',
            'price' => (string) $product['price'],
            'availability' => $product['stock'] > 0
                ? 'https://schema.org/InStock'
                : 'https://schema.org/OutOfStock',
            'seller' => [
                '@type' => 'Organization',
                'name' => getSetting('site_name', SITE_NAME)
            ]
        ],
        'brand' => [
            '@type' => 'Brand',
            'name' => $product['brand_name'] ?? ''
        ]
    ];

    return '<script type="application/ld+json">' . json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
}

function getBreadcrumbSchema($breadcrumbs) {
    $items = [];
    $position = 1;

    foreach ($breadcrumbs as $breadcrumb) {
        $items[] = [
            '@type' => 'ListItem',
            'position' => $position++,
            'name' => $breadcrumb['name'],
            'item' => $breadcrumb['url']
        ];
    }

    $data = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => $items
    ];

    return '<script type="application/ld+json">' . json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
}

function getWebSiteSchema() {
    $data = [
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'url' => SITE_URL,
        'name' => getSetting('site_name', SITE_NAME),
        'potentialAction' => [
            '@type' => 'SearchAction',
            'target' => SITE_URL . '/pages/catalog.php?q={search_term_string}',
            'query-input' => 'required name=search_term_string'
        ]
    ];

    return '<script type="application/ld+json">' . json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
}
