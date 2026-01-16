<?php
/**
 * Contact Page
 * SUNDARI TOP STAR S.R.L.
 */
require_once '../config/config.php';

$pageTitle = 'Contact';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($message)) {
        setFlash('error', 'Te rugăm completează toate câmpurile.');
    } else {
        // TODO: Implement email sending
        setFlash('success', 'Mesajul a fost trimis cu succes! Te vom contacta în curând.');
        redirect('/pages/contact.php');
    }
}

include SITE_ROOT . '/includes/header.php';
?>

<div class="container">
    <h1>Contact</h1>

    <div class="contact-layout">
        <!-- Contact Form -->
        <div class="contact-form-section">
            <h2>Trimite-ne un Mesaj</h2>

            <form method="POST">
                <?= getCsrfField() ?>

                <div class="form-group">
                    <label for="name">Nume *</label>
                    <input type="text" id="name" name="name" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="message">Mesaj *</label>
                    <textarea id="message" name="message" class="form-control" rows="6" required></textarea>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> Trimite Mesajul
                </button>
            </form>
        </div>

        <!-- Contact Info -->
        <div class="contact-info-section">
            <h2>Informații Contact</h2>

            <div class="contact-info-card">
                <div class="contact-item">
                    <i class="fas fa-building"></i>
                    <div>
                        <strong>SUNDARI TOP STAR S.R.L.</strong>
                        <p>Piese, accesorii și consumabile pentru mașini de cusut</p>
                    </div>
                </div>

                <div class="contact-item">
                    <i class="fas fa-phone"></i>
                    <div>
                        <strong>Telefon:</strong>
                        <p><?= e(getSetting('contact_phone', '+40 700 000 000')) ?></p>
                    </div>
                </div>

                <div class="contact-item">
                    <i class="fas fa-envelope"></i>
                    <div>
                        <strong>Email:</strong>
                        <p><?= e(getSetting('contact_email', 'contact@sundari.ro')) ?></p>
                    </div>
                </div>

                <div class="contact-item">
                    <i class="fas fa-clock"></i>
                    <div>
                        <strong>Program:</strong>
                        <p>Luni - Vineri: 09:00 - 18:00</p>
                        <p>Sâmbătă: 10:00 - 14:00</p>
                        <p>Duminică: Închis</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include SITE_ROOT . '/includes/footer.php';

$additionalCss = '<style>
.contact-layout {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 40px;
    margin: 40px 0;
}

.contact-form-section h2,
.contact-info-section h2 {
    font-size: 24px;
    margin-bottom: 25px;
}

.contact-info-card {
    background-color: var(--bg-white);
    padding: 30px;
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius);
}

.contact-item {
    display: flex;
    gap: 20px;
    margin-bottom: 30px;
}

.contact-item:last-child {
    margin-bottom: 0;
}

.contact-item i {
    font-size: 24px;
    color: var(--accent-color);
    width: 30px;
    height: 30px;
}

.contact-item p {
    margin: 5px 0 0 0;
    color: var(--text-light);
}

@media (max-width: 768px) {
    .contact-layout {
        grid-template-columns: 1fr;
    }
}
</style>';
