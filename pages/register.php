<?php
/**
 * Register Page
 * SUNDARI TOP STAR S.R.L.
 */
require_once '../config/config.php';
require_once SITE_ROOT . '/includes/auth.php';
require_once SITE_ROOT . '/includes/flash.php';

// If already logged in, redirect to account
if (isLoggedIn()) {
    redirect('/pages/account.php');
}

$pageTitle = 'Înregistrare';

// Define styles before including header
$additionalCss = '<style>
.auth-page {
    max-width: 600px;
    margin: 60px auto;
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

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

@media (max-width: 600px) {
    .form-row {
        grid-template-columns: 1fr;
    }
}
</style>';

// Process form submission BEFORE including header
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        setFlash('error', 'Eroare de securitate. Încearcă din nou.');
        redirect('/pages/register.php');
    }

    $data = [
        'email' => trim($_POST['email'] ?? ''),
        'password' => $_POST['password'] ?? '',
        'confirm_password' => $_POST['confirm_password'] ?? '',
        'name' => trim($_POST['name'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'address' => trim($_POST['address'] ?? ''),
        'city' => trim($_POST['city'] ?? ''),
        'county' => trim($_POST['county'] ?? ''),
        'postal_code' => trim($_POST['postal_code'] ?? '')
    ];

    // Basic validation
    if (empty($data['email']) || empty($data['password']) || empty($data['name'])) {
        setFlash('error', 'Te rugăm completează toate câmpurile obligatorii.');
    } elseif ($data['password'] !== $data['confirm_password']) {
        setFlash('error', 'Parolele nu coincid.');
    } elseif (strlen($data['password']) < PASSWORD_MIN_LENGTH) {
        setFlash('error', 'Parola trebuie să aibă cel puțin ' . PASSWORD_MIN_LENGTH . ' caractere.');
    } else {
        $result = register($data);
        if ($result['success']) {
            setFlash('success', 'Cont creat cu succes! Te poți autentifica acum.');
            redirect('/pages/login.php');
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
            <h1>Înregistrare</h1>
            <p class="subtitle">Creează-ți cont nou</p>

            <form method="POST">
                <?= getCsrfField() ?>

                <div class="form-group">
                    <label for="name">Nume Complet *</label>
                    <input type="text" id="name" name="name" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="phone">Telefon</label>
                    <input type="tel" id="phone" name="phone" class="form-control">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Parolă *</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirmă Parola *</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="address">Adresă</label>
                    <input type="text" id="address" name="address" class="form-control">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="city">Oraș</label>
                        <input type="text" id="city" name="city" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="postal_code">Cod Poștal</label>
                        <input type="text" id="postal_code" name="postal_code" class="form-control">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg" style="width: 100%">
                    Înregistrare
                </button>
            </form>

            <div class="auth-footer">
                <p>Ai deja cont? <a href="/pages/login.php">Autentifică-te</a></p>
            </div>
        </div>
    </div>
</div>

<?php
include SITE_ROOT . '/includes/footer.php';
?>
