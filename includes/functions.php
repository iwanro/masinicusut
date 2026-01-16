<?php
/**
 * Helper Functions
 * SUNDARI TOP STAR S.R.L.
 */

/**
 * Escape HTML pentru prevenire XSS
 * @param string $string
 * @return string
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Dump variable (debugging)
 * @param mixed $var
 * @return void
 */
function dd($var) {
    echo '<pre>';
    var_dump($var);
    echo '</pre>';
    die;
}

/**
 * Format preț cu monedă
 * @param float $price
 * @param string $currency
 * @return string
 */
function formatPrice($price, $currency = 'RON') {
    return number_format($price, 2, ',', '.') . ' ' . $currency;
}

/**
 * Format dată
 * @param string $date
 * @param string $format
 * @return string
 */
function formatDate($date, $format = 'd.m.Y') {
    return date($format, strtotime($date));
}

/**
 * Trungează text
 * @param string $text
 * @param int $length
 * @param string $suffix
 * @return string
 */
function truncate($text, $length = 100, $suffix = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . $suffix;
}

/**
 * Generează slug din text
 * @param string $text
 * @return string
 */
function slugify($text) {
    $text = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    $text = trim($text, '-');
    return strtolower($text);
}

/**
 * Upload fișier imagine
 * @param array $file $_FILES array element
 * @return array ['success' => bool, 'message' => string, 'filename' => string|null]
 */
function uploadImage($file) {
    // Verifică dacă e upload error
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Eroare la upload.'];
    }

    // Verifică dimensiune
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'message' => 'Fișierul este prea mare. Max ' . (MAX_FILE_SIZE / 1024 / 1024) . 'MB.'];
    }

    // Verifică tip
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, ALLOWED_IMAGE_TYPES)) {
        return ['success' => false, 'message' => 'Tip de fișier nepermis. Doar JPG, PNG, WEBP.'];
    }

    // Generează nume unic
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('prod_', true) . '_' . time() . '.' . $extension;
    $filepath = PATH_UPLOADS . '/' . $filename;

    // Mută fișierul
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => true, 'message' => 'Upload reușit.', 'filename' => $filename];
    }

    return ['success' => false, 'message' => 'Eroare la salvarea fișierului.'];
}

/**
 * Șterge fișier imagine
 * @param string $filename
 * @return bool
 */
function deleteImage($filename) {
    if (empty($filename)) {
        return false;
    }

    $filepath = PATH_UPLOADS . '/' . $filename;
    if (file_exists($filepath)) {
        return unlink($filepath);
    }

    return false;
}

/**
 * Obține setare din DB
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function getSetting($key, $default = null) {
    static $settings = null;

    if ($settings === null) {
        $db = db();
        $stmt = $db->prepare("SELECT `key`, value FROM settings");
        $stmt->execute();
        $result = $stmt->fetchAll();

        $settings = [];
        foreach ($result as $row) {
            $settings[$row['key']] = $row['value'];
        }
    }

    return $settings[$key] ?? $default;
}

/**
 * Setează o valoare în settings
 * @param string $key
 * @param mixed $value
 * @return bool
 */
function setSetting($key, $value) {
    $db = db();
    $stmt = $db->prepare("INSERT INTO settings (`key`, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = ?");
    return $stmt->execute([$key, $value, $value]);
}

/**
 * Obține categoriile principale (brands)
 * @return array
 */
function getBrands() {
    $db = db();
    $stmt = $db->prepare("
        SELECT id, name, slug, image
        FROM categories
        WHERE type = 'brand' AND is_active = 1
        ORDER BY sort_order ASC
    ");
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Obține tipurile de produse
 * @return array
 */
function getProductTypes() {
    $db = db();
    $stmt = $db->prepare("
        SELECT id, name, slug
        FROM categories
        WHERE type = 'product_type' AND is_active = 1
        ORDER BY sort_order ASC
    ");
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Generează număr comandă unic
 * @return string
 */
function generateOrderNumber() {
    return 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid('', true), -6));
}

/**
 * Trimite email (simplificat - necesită configurare SMTP)
 * @param string $to
 * @param string $subject
 * @param string $body
 * @return bool
 */
function sendEmail($to, $subject, $body) {
    // TODO: Implementare cu PHPMailer sau altă librărie
    // Momentan doar log
    error_log("Email to: $to, Subject: $subject");
    return true;
}

/**
 * Flash message - setează
 * @param string $type
 * @param string $message
 * @return void
 */
function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Flash message - obține și șterge
 * @return array|null
 */
function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Verifică dacă are flash message
 * @return bool
 */
function hasFlash() {
    return isset($_SESSION['flash']);
}

/**
 * Obține URL curent
 * @return string
 */
function currentUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    return $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

/**
 * Verifică dacă este pagina curentă
 * @param string $page
 * @return bool
 */
function isActivePage($page) {
    $current = basename($_SERVER['PHP_SELF']);
    return $current === $page;
}

/**
 * Paginare - calculează offset
 * @param int $page
 * @param int $perPage
 * @return int
 */
function getOffset($page, $perPage) {
    return ($page - 1) * $perPage;
}

/**
 * Paginare - calculează total pagini
 * @param int $total
 * @param int $perPage
 * @return int
 */
function getTotalPages($total, $perPage) {
    return ceil($total / $perPage);
}
