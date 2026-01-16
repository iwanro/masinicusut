<?php
/**
 * Global Configuration
 * SUNDARI TOP STAR S.R.L.
 */

// Define SITE_ROOT (only if not already defined)
if (!defined('SITE_ROOT')) {
    define('SITE_ROOT', realpath(dirname(__FILE__) . '/..'));
}

// =====================================================
// Database Configuration
// =====================================================
// Load environment variables from .env file if it exists
$envFile = SITE_ROOT . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue; // Skip comments
        }
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $_ENV[trim($name)] = trim($value);
        }
    }
}

// Define database constants - prioritize environment variables, fall back to hardcoded values
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'fovyarnx_cusut');
define('DB_USER', getenv('DB_USER') ?: 'fovyarnx_usercusut');
define('DB_PASS', getenv('DB_PASS') ?: 'SiteSundari22!');
define('DB_CHARSET', getenv('DB_CHARSET') ?: 'utf8mb4');

// =====================================================
// Site Configuration
// =====================================================
define('SITE_NAME', 'SUNDARI TOP STAR');
define('SITE_URL', 'https://www.piesemasinicusut.ro/');
define('ADMIN_EMAIL', 'ffffdv@gmail.com');

// =====================================================
// Paths
// =====================================================
define('PATH_CONFIG', SITE_ROOT . '/config');
define('PATH_INCLUDES', SITE_ROOT . '/includes');
define('PATH_ASSETS', SITE_ROOT . '/assets');
define('PATH_PAGES', SITE_ROOT . '/pages');
define('PATH_ADMIN', SITE_ROOT . '/admin');
define('PATH_API', SITE_ROOT . '/api');
define('PATH_UPLOADS', SITE_ROOT . '/assets/images/products');

// =====================================================
// URLs
// =====================================================
define('URL_ASSETS', SITE_URL . '/assets');
define('URL_CSS', URL_ASSETS . '/css');
define('URL_JS', URL_ASSETS . '/js');
define('URL_IMAGES', URL_ASSETS . '/images');
define('URL_PRODUCTS', URL_IMAGES . '/products');

// =====================================================
// Session Configuration
// =====================================================
define('SESSION_NAME', 'SUNDARI_SESSION');
define('SESSION_LIFETIME', 86400); // 24 hours

// =====================================================
// Security
// =====================================================
define('CSRF_TOKEN_NAME', 'csrf_token');
define('PASSWORD_MIN_LENGTH', 8); // Increased from 6 for better security

// =====================================================
// Pagination
// =====================================================
define('PRODUCTS_PER_PAGE', 12);
define('ADMIN_ITEMS_PER_PAGE', 20);

// =====================================================
// File Upload
// =====================================================
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp']);

// =====================================================
// Start Session
// =====================================================
if (session_status() === PHP_SESSION_NONE) {
    // Set session save path to tmp (fix for cPanel hosting)
    if (!is_dir('/opt/alt/php80/var/lib/php/session')) {
        session_save_path('/tmp');
    }

    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_samesite', 'Lax'); // Allow same-site requests
    ini_set('session.cookie_secure', 1); // HTTPS-only cookies
    session_name(SESSION_NAME);
    session_start();
}

// Load database
// For local testing with SQLite, uncomment the next line:
// require_once PATH_CONFIG . '/database_sqlite.php';

// For production with MySQL, use this:
require_once PATH_CONFIG . '/database.php';

// Load functions
require_once PATH_INCLUDES . '/functions.php';

// Load auth
require_once PATH_INCLUDES . '/auth.php';

// =====================================================
// Error Reporting (Production)
// =====================================================
error_reporting(E_ALL);
ini_set('display_errors', 0); // Disable error display in production
ini_set('log_errors', 1);     // Log errors instead
ini_set('error_log', SITE_ROOT . '/logs/php_errors.log'); // Log file location

// =====================================================
// Timezone
// =====================================================
date_default_timezone_set('Europe/Bucharest');

