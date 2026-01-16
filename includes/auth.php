<?php
/**
 * Authentication System
 * SUNDARI TOP STAR S.R.L.
 * Simplu și secure cu PHP native sessions
 */

/**
 * Verifică dacă utilizatorul este logat
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Verifică dacă utilizatorul este admin
 * @return bool
 */
function isAdmin() {
    return isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Obține ID-ul utilizatorului curent
 * @return int|null
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Obține utilizatorul curent
 * @return array|null
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }

    static $user = null;
    if ($user === null) {
        $db = db();
        $stmt = $db->prepare("SELECT id, email, name, phone, address, city, county, postal_code, role FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
    }

    return $user;
}

/**
 * Login utilizator
 * @param string $email
 * @param string $password
 * @return array ['success' => bool, 'message' => string]
 */
function login($email, $password) {
    $db = db();

    $stmt = $db->prepare("SELECT id, email, password_hash, name, role FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        return ['success' => false, 'message' => 'Email sau parolă incorectă.'];
    }

    if (!password_verify($password, $user['password_hash'])) {
        return ['success' => false, 'message' => 'Email sau parolă incorectă.'];
    }

    // Set session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['name'] = $user['name'];
    $_SESSION['role'] = $user['role'];

    // Regenerează session ID pentru security
    session_regenerate_id(true);

    return ['success' => true, 'message' => 'Login reușit!'];
}

/**
 * Înregistrare utilizator nou
 * @param array $data ['email', 'password', 'name', 'phone', 'address', 'city', 'county', 'postal_code']
 * @return array ['success' => bool, 'message' => string]
 */
function register($data) {
    $db = db();

    // Validare
    if (strlen($data['password']) < PASSWORD_MIN_LENGTH) {
        return ['success' => false, 'message' => 'Parola trebuie să aibă cel puțin ' . PASSWORD_MIN_LENGTH . ' caractere.'];
    }

    // Verifică dacă emailul există deja
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$data['email']]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Acest email este deja înregistrat.'];
    }

    // Hash password
    $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);

    // Insert user
    try {
        $stmt = $db->prepare("
            INSERT INTO users (email, password_hash, name, phone, address, city, county, postal_code)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $data['email'],
            $passwordHash,
            $data['name'],
            $data['phone'] ?? null,
            $data['address'] ?? null,
            $data['city'] ?? null,
            $data['county'] ?? null,
            $data['postal_code'] ?? null
        ]);

        return ['success' => true, 'message' => 'Cont creat cu succes!'];
    } catch (PDOException $e) {
        error_log("Registration error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Eroare la creare cont. Vă rugăm încercați din nou.'];
    }
}

/**
 * Logout utilizator
 * @return void
 */
function logout() {
    // Distruge session data
    $_SESSION = [];

    // Șterge cookie de session
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    // Distruge session
    session_destroy();
}

/**
 * Middleware: Protejează paginile - necesită autentificare
 * @return void
 */
function requireAuth() {
    if (!isLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        redirect('/pages/login.php');
    }
}

/**
 * Middleware: Protejează paginile de admin
 * @return void
 */
function requireAdmin() {
    if (!isAdmin()) {
        redirect('/index.php');
    }
}

/**
 * Redirect helper
 * @param string $url
 * @return void
 */
function redirect($url) {
    header("Location: " . $url);
    exit;
}

/**
 * Generează CSRF token
 * @return string
 */
function generateCsrfToken() {
    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

/**
 * Verifică CSRF token
 * @param string $token
 * @return bool
 */
function verifyCsrfToken($token) {
    return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

/**
 * Returnează CSRF token pentru formular
 * @return string
 */
function getCsrfField() {
    $token = generateCsrfToken();
    return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . htmlspecialchars($token) . '">';
}
