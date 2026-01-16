<?php
/**
 * Register Page
 * SUNDARI TOP STAR S.R.L.
 */
require_once '../config/config.php';

// If already logged in, redirect to account
if (isLoggedIn()) {
    redirect('/pages/account.php');
}

$pageTitle = 'Înregistrare';

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

            <form method="POST">
                <?= getCsrfField() ?>

                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="name">Nume Complet *</label>
                    <input type="text" id="name" name="name" class="form-control" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Parolă *</label>
                        <input type="password" id="password" name="password" class="form-control" required minlength="<?= PASSWORD_MIN_LENGTH ?>">
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirmă Parola *</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="phone">Telefon</label>
                    <input type="tel" id="phone" name="phone" class="form-control">
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
                        <label for="county">Județ</label>
                        <input type="text" id="county" name="county" class="form-control">
                    </div>
                </div>

                <div class="form-group">
                    <label for="postal_code">Cod Poștal</label>
                    <input type="text" id="postal_code" name="postal_code" class="form-control">
                </div>

                <button type="submit" class="btn btn-primary btn-lg" style="width: 100%">
                    Creează Cont
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

$additionalCss = '<style>
.auth-page {
    max-width: 600px;
    margin: 60px auto;
}

.auth-card {
    background-color: var(--bg-white);
    padding: 40px;
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius);
}

.auth-card h1 {
    text-align: center;
    margin-bottom: 30px;
}

.auth-footer {
    text-align: center;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid var(--border-color);
}
</style>';
