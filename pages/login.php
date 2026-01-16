<?php
/**
 * Login Page
 * SUNDARI TOP STAR S.R.L.
 */
require_once '../config/config.php';

// If already logged in, redirect to account
if (isLoggedIn()) {
    redirect('/pages/account.php');
}

$pageTitle = 'Login';

// Define styles before including header
$additionalCss = '<style>
.auth-page {
    max-width: 480px;
    margin: 80px auto;
}

.auth-card {
    background: var(--bg-primary);
    padding: 48px;
    border-radius: var(--border-radius-lg);
    box-shadow: var(--shadow-lg);
}

.auth-card h1 {
    font-family: "Outfit", sans-serif;
    font-size: 1.75rem;
    font-weight: 700;
    margin-bottom: 8px;
    color: var(--primary-color);
    text-align: center;
}

.subtitle {
    text-align: center;
    color: var(--text-secondary);
    margin-bottom: 32px;
}

.auth-footer {
    text-align: center;
    margin-top: 24px;
    padding-top: 24px;
    border-top: 1px solid var(--border-light);
}

.auth-footer p {
    color: var(--text-secondary);
}

.auth-footer a {
    color: var(--accent-color);
    font-weight: 600;
    text-decoration: none;
}

.auth-footer a:hover {
    text-decoration: underline;
}
</style>';

// Process form submission BEFORE including header
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        setFlash('error', 'Te rugăm completează toate câmpurile.');
    } else {
        $result = login($email, $password);
        if ($result['success']) {
            $redirect = $_SESSION['redirect_after_login'] ?? '/pages/account.php';
            unset($_SESSION['redirect_after_login']);
            setFlash('success', $result['message']);
            redirect($redirect);
        } else {
            setFlash('error', $result['message']);
        }
    }
}

include SITE_ROOT . '/includes/header.php';
?>

<div class="container">
    <div class="auth-page">
        <div class="auth-card">
            <h1>Autentificare</h1>
            <p class="subtitle">Bine ai revenit!</p>

            <form method="POST">
                <?= getCsrfField() ?>

                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="password">Parolă *</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary btn-lg" style="width: 100%">
                    Autentificare
                </button>
            </form>

            <div class="auth-footer">
                <p>Nu ai cont? <a href="/pages/register.php">Înregistrează-te</a></p>
            </div>
        </div>
    </div>
</div>

<?php
include SITE_ROOT . '/includes/footer.php';
?>
