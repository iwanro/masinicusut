# Deployment Guide - SUNDARI TOP STAR E-Commerce

## 📋 Overview

Site complet e-commerce pentru piese mașini de cusut cu:
- Catalog produse cu categorii ierarhice
- Coș de cumpărături
- Cont utilizator cu istoric comenzi
- Panou admin complet
- Taxe transport dinamice pe județ/localitate
- Notificări email automate

## 🚀 Deployment pe Hostico.ro

### Pasul 1: Upload Fișiere

Upload toate fișierele în folderul `public_html` via FTP sau File Manager:

```
/public_html/
├── admin/
├── api/
├── assets/
├── config/
├── includes/
├── pages/
├── sql/
├── index.php
└── .htaccess
```

### Pasul 2: Configurare Bază de Date

1. Intră în **cPanel** → **MySQL Databases**
2. Creează bază de date nouă (ex: `piese_masini_cusut`)
3. Creează user MySQL și atașează-l la baza de date cu **ALL PRIVILEGES**

### Pasul 3: Import Schema

1. Intră în **phpMyAdmin**
2. Selectează baza de date creată
3. Click pe **Import**
4. Upload și importează fișierul `sql/database.sql`
5. După import, importează și `sql/add_shipping_and_email.sql`

### Pasul 4: Configurare Conexiune BD

Editează `config/config.php` și modifică credențialele:

```php
define('DB_HOST', 'localhost');           // De obicei localhost pe Hostico
define('DB_NAME', 'nume_baza_date');      // Numele din cPanel
define('DB_USER', 'nume_user');           // Userul creat în cPanel
define('DB_PASS', 'parola_ta');           // Parola userului
```

### Pasul 5: Configurare SMTP Email

1. Accesează `https://domeniultau.ro/admin/email_settings.php`
2. Configurează SMTP (pentru Hostico):
   - **SMTP Host**: `smtp.hostico.ro` sau `mail.hostico.ro`
   - **SMTP Port**: `587` (TLS) sau `465` (SSL)
   - **SMTP Username**: adresa_ta@domeniultau.ro
   - **SMTP Password**: parola email-ului
   - **Encryption**: `tls`
3. Setează **Email Admin** unde primești notificările
4. Click **Salvează & Trimite Test** pentru verificare

### Pasul 6: Configurare Taxe Transport

1. Accesează `https://domeniultau.ro/admin/shipping.php`
2. Adaugă taxe de transport pentru județe:
   - Click **Adaugă Taxă Nouă**
   - Selectează județul
   - Setează taxa (ex: 15 RON)
   - Salvează
3. Poți adăuga taxe specifice pentru localități dacă e necesar

### Pasul 7: Verificare Site

1. Accesează `https://domeniultau.ro`
2. Înregistrează un utilizator de test
3. Adaugă produse în coș
4. Plasează o comandă de test
5. Verifică:
   - Comanda apare în admin
   - Primești email notificare
   - Utilizatorul primește confirmare

### Pasul 8: Setări Producție

Editează `config/config.php`:

```php
// Ajustează URL-ul site-ului
define('SITE_URL', 'https://domeniultau.ro');

// Opre error reporting pentru producție
error_reporting(0);
ini_set('display_errors', 0);

// Activează log erori în fișier
ini_set('log_errors', 1);
ini_set('error_log', SITE_ROOT . '/logs/php_errors.log');
```

## 🔐 Securitate

### 1. Protejează folder-ul admin (Opțional)

În `.htaccess` adaugă:

```apache
<FilesMatch "^(email_settings|shipping)\.php$">
    Require ip 192.168.1.1  # Înlocuiește cu IP-ul tău
    # SAU folosește protecție prin parolă din cPanel
</FilesMatch>
```

### 2. Creează folder logs

```bash
mkdir logs
chmod 755 logs
touch logs/php_errors.log
chmod 644 logs/php_errors.log
```

### 3. Permisiuni Fișiere

```bash
# Folder public_html: 755
# Fișiere PHP: 644
# Folder uploads (assets/images/products): 755
```

## 👤 Credeniale Admin

**Default:**
- Email: `admin@sundari.ro`
- Password: `admin123`

⚠️ **IMPORTANT**: Schimbă parola imediat după prima autentificare!

Accesează `/admin/users.php` și editează userul admin.

## 📧 Configurare Email Hostico

Hostico oferă SMTP pentru conturile de email create:

1. **Server SMTP**: `mail.domeniultau.ro` sau `smtp.hostico.ro`
2. **Porturi**: 587 (TLS) sau 465 (SSL)
3. **Autentificare**: Required
4. **Username**: Adresa completă de email
5. **Parola**: Parola email-ului (din cPanel → Email Accounts)

Verifică documentația Hostico pentru detalii exacte: https://hostico.ro/kb/

## 🎨 Personalizare

### Logo și Branding

Înlocuiește fișierele din `assets/images/`:
- Logo: `assets/images/logo.png`
- Favicon: `favicon.ico`

### Culori și Stil

Editează `assets/css/style.css` și modifică variabilele CSS:

```css
:root {
    --primary-color: #2c3e50;      /* Schimbă culoarea principală */
    --accent-color: #3498db;       /* Schimbă accentul */
    --success-color: #27ae60;
    --danger-color: #e74c3c;
}
```

## 🔄 Backup

Automatizează backup-ul din **cPanel**:
1. **Backup Wizard** → **Full Backup**
2. Sau **Backup** → **Download a Full Account Backup**

Recomandat zilnic sau săptămânal.

## 📊 Monitorizare

Verifică periodic:
- **Logs**: `logs/php_errors.log`
- **Admin** → **Dashboard**: statistici comenzi
- **Admin** → **Comenzi**: comenzi noi

## ⚡ Performanță

1. Activează **OPcache** în cPanel (Select PHP Version → OPcache)
2. Comprimă fișierele CSS/JS
3. Activează **Cloudflare** din DNS (disponibil gratuit)

## 🆘 Troubleshooting

### Email-uri nu se trimit

1. Verifică setările SMTP în `/admin/email_settings.php`
2. Testează cu **Trimite Test**
3. Verifică logs pentru erori
4. Verifică dacă portul 587/465 nu e blocat

### Coșul nu se salvează

1. Verifică dacă sesiunile funcționează
2. Verifică permisiuni folder `sessions` (dacă există)
3. Activează cookies în browser

### Imagini nu se încarcă

1. Verifică permisiuni folder `assets/images/products` (755)
2. Verifică dacă `upload_max_filesize` în PHP e suficient (5MB)
3. Verifică spațiu pe disc

### Taxe transport nu se calculează

1. Verifică dacă tabela `shipping_rates` are date
2. Verifică console browser pentru erori JavaScript
3. Verifică dacă `/api/shipping.php` e accesibil

## 📞 Suport

Pentru probleme specifice Hostico:
- Email: support@hostico.ro
- Tel: 031.104.28.50
- Documentație: https://hostico.ro/kb/

## ✅ Checklist Final

- [ ] Upload fișiere pe server
- [ ] Bază de date creată și importată
- [ ] Configurat `config.php` cu credențele corecte
- [ ] SMTP configurat și testat
- [ ] Taxe transport configurate
- [ ] Parolă admin schimbată
- [ ] Test comenzi efectuat
- [ ] Error logging activat
- [ ] Backup automat configurat

Site-ul e gata de producție! 🎉
