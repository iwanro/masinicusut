<?php
/**
 * Admin Email Settings
 * SUNDARI TOP STAR S.R.L.
 */
require_once '../config/config.php';
requireAdmin();

$db = db();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        die('Eroare de securitate.');
    }

    // Save settings
    $settings = [
        'smtp_host' => trim($_POST['smtp_host'] ?? ''),
        'smtp_port' => intval($_POST['smtp_port'] ?? 587),
        'smtp_username' => trim($_POST['smtp_username'] ?? ''),
        'smtp_password' => trim($_POST['smtp_password'] ?? ''),
        'smtp_encryption' => trim($_POST['smtp_encryption'] ?? 'tls'),
        'admin_email' => trim($_POST['admin_email'] ?? ''),
        'email_from_name' => trim($_POST['email_from_name'] ?? ''),
        'email_from_address' => trim($_POST['email_from_address'] ?? ''),
        'email_orders_enabled' => isset($_POST['email_orders_enabled']) ? '1' : '0'
    ];

    foreach ($settings as $key => $value) {
        setSetting($key, $value);
    }

    // Test email if requested
    if (isset($_POST['test_email'])) {
        $testEmail = trim($_POST['test_email'] ?? '');
        if (!empty($testEmail)) {
            require_once INCLUDES_PATH . '/email_service.php';
            $result = sendTestEmail($testEmail);
            if ($result) {
                setFlash('success', 'Setări salvate și email de test trimis cu succes!');
            } else {
                setFlash('warning', 'Setări salvate, dar email-ul de test NU a putut fi trimis. Verifică configurația SMTP.');
            }
            redirect('email_settings.php');
        }
    }

    setFlash('success', 'Setări email salvate cu succes!');
    redirect('email_settings.php');
}

// Get current settings
$emailSettings = [
    'smtp_host' => getSetting('smtp_host', 'localhost'),
    'smtp_port' => getSetting('smtp_port', '587'),
    'smtp_username' => getSetting('smtp_username', ''),
    'smtp_encryption' => getSetting('smtp_encryption', 'tls'),
    'admin_email' => getSetting('admin_email', 'admin@sundari.ro'),
    'email_from_name' => getSetting('email_from_name', 'SUNDARI TOP STAR'),
    'email_from_address' => getSetting('email_from_address', 'noreply@sundari.ro'),
    'email_orders_enabled' => getSetting('email_orders_enabled', '0')
];
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setări Email - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="<?= URL_CSS ?>/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .settings-section {
            margin-bottom: 40px;
            padding: 20px;
            background-color: #fff;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
        }
        .settings-section h3 {
            font-size: 18px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--accent-color);
        }
        .info-box {
            padding: 15px;
            background-color: var(--bg-light);
            border-left: 4px solid var(--accent-color);
            margin-bottom: 20px;
            border-radius: var(--border-radius);
        }
    </style>
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <h2>Admin Panel</h2>
            </div>
            <nav class="admin-nav">
                <a href="index.php"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a>
                <a href="products.php"><i class="fas fa-box"></i> <span>Produse</span></a>
                <a href="categories.php"><i class="fas fa-folder"></i> <span>Categorii</span></a>
                <a href="orders.php"><i class="fas fa-shopping-cart"></i> <span>Comenzi</span></a>
                <a href="users.php"><i class="fas fa-users"></i> <span>Utilizatori</span></a>
                <a href="shipping.php"><i class="fas fa-truck"></i> <span>Transport</span></a>
                <a href="email_settings.php" class="active"><i class="fas fa-envelope"></i> <span>Email</span></a>
                <a href="../index.php"><i class="fas fa-external-link-alt"></i> <span>Vezi Site</span></a>
                <a href="../pages/logout.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <header class="admin-header">
                <h1>Configurare Email & Notificări</h1>
                <div class="admin-user">
                    <span>Salut, <strong><?= e($_SESSION['name'] ?? 'Admin') ?></strong>!</span>
                </div>
            </header>

            <div class="admin-content">
                <!-- SMTP Settings -->
                <div class="settings-section">
                    <h3>Configurare SMTP</h3>

                    <form method="POST">
                        <?= getCsrfField() ?>

                        <div class="info-box">
                            <i class="fas fa-info-circle"></i>
                            <strong>Info:</strong> Pentru Hostico, serverul SMTP este de obicei <code>smtp.hostico.ro</code> sau <code>mail.hostico.ro</code> cu portul <code>587</code> sau <code>465</code>.
                            Verifică documentația Hostico pentru detaliile exacte.
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="smtp_host">SMTP Host *</label>
                                <input type="text" id="smtp_host" name="smtp_host" class="form-control"
                                       value="<?= e($emailSettings['smtp_host']) ?>" required>
                                <small>Ex: smtp.hostico.ro</small>
                            </div>

                            <div class="form-group">
                                <label for="smtp_port">SMTP Port *</label>
                                <input type="number" id="smtp_port" name="smtp_port" class="form-control"
                                       value="<?= $emailSettings['smtp_port'] ?>" required>
                                <small>De obicei 587 (TLS) sau 465 (SSL)</small>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="smtp_username">Utilizator SMTP</label>
                                <input type="text" id="smtp_username" name="smtp_username" class="form-control"
                                       value="<?= e($emailSettings['smtp_username']) ?>">
                                <small>Adresa de email completă</small>
                            </div>

                            <div class="form-group">
                                <label for="smtp_password">Parolă SMTP</label>
                                <input type="password" id="smtp_password" name="smtp_password" class="form-control"
                                       value="<?= e($emailSettings['smtp_password']) ?>">
                                <small>Lăsă gol pentru a păstra parola curentă</small>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="smtp_encryption">Criptare *</label>
                            <select id="smtp_encryption" name="smtp_encryption" class="form-control" required>
                                <option value="tls" <?= $emailSettings['smtp_encryption'] === 'tls' ? 'selected' : '' ?>>TLS</option>
                                <option value="ssl" <?= $emailSettings['smtp_encryption'] === 'ssl' ? 'selected' : '' ?>>SSL</option>
                                <option value="none" <?= $emailSettings['smtp_encryption'] === 'none' ? 'selected' : '' ?>>Fără criptare</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Email Settings -->
                <div class="settings-section">
                    <h3>Setări Email</h3>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="admin_email">Email Admin (pentru comenzi) *</label>
                            <input type="email" id="admin_email" name="admin_email" class="form-control"
                                   value="<?= e($emailSettings['admin_email']) ?>" required>
                            <small>Pe acest email vei primi notificările de comenzi noi</small>
                        </div>

                        <div class="form-group">
                            <label for="email_from_address">Adresă Expeditor *</label>
                            <input type="email" id="email_from_address" name="email_from_address" class="form-control"
                                   value="<?= e($emailSettings['email_from_address']) ?>" required>
                            <small>Adresa din care se trimit email-urile (noreply@domeniu.ro)</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email_from_name">Nume Expeditor *</label>
                        <input type="text" id="email_from_name" name="email_from_name" class="form-control"
                               value="<?= e($emailSettings['email_from_name']) ?>" required>
                        <small>SUNDARI TOP STAR sau numele site-ului tău</small>
                    </div>

                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: 10px;">
                            <input type="checkbox" name="email_orders_enabled" <?= $emailSettings['email_orders_enabled'] === '1' ? 'checked' : '' ?>>
                            Activează notificări email pentru comenzi noi
                        </label>
                    </div>
                </div>

                <!-- Test Email -->
                <div class="settings-section">
                    <h3>Test Trimitere Email</h3>

                    <div class="form-group">
                        <label for="test_email">Trimite email de test la:</label>
                        <input type="email" id="test_email" name="test_email" class="form-control"
                               placeholder="adresa_ta@email.com" required>
                        <small>Vei primi un email de test pentru a verifica configurația SMTP</small>
                    </div>

                    <div style="margin-top: 20px;">
                        <button type="submit" name="save" class="btn btn-primary">
                            <i class="fas fa-save"></i> Salvează Setări
                        </button>
                        <button type="submit" name="test_email" class="btn btn-success" style="margin-left: 10px;">
                            <i class="fas fa-paper-plane"></i> Salvează & Trimite Test
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
