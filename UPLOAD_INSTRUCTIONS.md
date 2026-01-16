# Instrucțiuni Upload Manual FTP

## 📦 Fișier ZIP

**Locație**: `/media/iwan/New Volume1/Iulian/GeminiCLI/Siteuri pentru portofoliu/PieseMasiniCusut/masinicusut.zip`
**Dimensiune**: 75 KB

## 📤 Procedură Upload

### Pasul 1: Dezarhivează ZIP

1. **Descarcă** sau **copiază** fișierul `masinicusut.zip`
2. **Dezarhivează-l** pe calculatorul tău (Click dreapta → Extract Here)

Vei obține structura:
```
masinicusut/
├── admin/
├── api/
├── assets/
├── config/
├── includes/
├── pages/
├── sql/
├── .cpanel.yml
├── index.php
├── README.md
└── DEPLOYMENT.md
```

### Pasul 2: Conectare FTP

**Credentiale FTP Hostico**:
- **Host**: ftp.hostico.ro (sau serverul hostico)
- **Port**: 21
- **Username**: utilizatorul cPanel
- **Password**: parola cPanel

**Poți folosi**:
- FileZilla (Windows/Linux/Mac)
- WinSCP (Windows)
- Cyberduck (Mac)
- File Manager din cPanel

### Pasul 3: Upload Fișiere

⚠️ **IMPORTANT**: Upload doar **conținutul** folderului `masinicusut/`, NU folderul în sine!

**Corect** ✅:
```
public_html/
├── admin/
├── api/
├── assets/
├── config/
├── includes/
├── pages/
├── sql/
├── .cpanel.yml
├── index.php
└── ...
```

**Greșit** ❌:
```
public_html/
└── masinicusut/
    └── admin/
    └── ...
```

### Pasul 4: Permisiuni Fișiere

După upload, setează permisiuni:

```bash
# Folder public_html: 755
# Fișiere PHP: 644
# Folder assets/images/products: 755
```

### Pasul 5: Bază de Date

1. **Intră în cPanel** → **MySQL Databases**
2. **Creează baza de date**:
   - Nume: `piese_m_cusut` (cPanel adaugă prefixul)
   - Click **Create Database**
   - Vei vedea ceva de genul: `numeletau_piese_m_cusut`

3. **Creează user MySQL**:
   - Username: (alege un nume)
   - Password: (generează una puternică)
   - Click **Create User**

4. **Atașează user-ul la baza de date**:
   - Selectează user-ul și baza de date
   - Bifează **ALL PRIVILEGES**
   - Click **Make Changes**

5. **Import schema SQL** (FOARTE IMPORTANT - Folosește fișierele cpanel!):
   - Intră în **phpMyAdmin**
   - Selectează baza de date creată din stânga
   - Click pe tab-ul **Import**
   - Upload **PRIMUL** fișier: `sql/database_cpanel.sql`
   - Click **Go**
   - **Apoi** upload **AL DOILEA** fișier: `sql/add_shipping_and_email_cpanel.sql`
   - Click **Go**

⚠️ **IMPORTANT**: Folosește fișierele `_cpanel.sql`, nu cele normale! Fișierele normale conțin `CREATE DATABASE` care nu este permis în cPanel.

### Pasul 6: Configurează Site-ul

**Editează** `config/config.php`:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'numele_db_exact_din_cpanel');
define('DB_USER', 'userul_exact_din_cpanel');
define('DB_PASS', 'parola_setata_in_cpanel');
define('SITE_URL', 'https://domeniultau.ro');  // SCHIMBĂ
```

### Pasul 7: Configurează Email

1. Accesează: `https://domeniultau.ro/admin/email_settings.php`
2. Setează SMTP Hostico:
   - SMTP Host: `mail.domeniultau.ro` sau `smtp.hostico.ro`
   - SMTP Port: `587`
   - Encryption: `tls`
   - Username: adresa_ta@domeniultau.ro
   - Password: parola email-ului
3. Click **Salvează & Trimite Test**

### Pasul 8: Taxe Transport

1. Accesează: `https://domeniultau.ro/admin/shipping.php`
2. Adaugă taxe pentru județe
3. Testează în checkout

## ✅ Verificare

1. **Accesează** site-ul: `https://domeniultau.ro`
2. **Testează** login admin: `admin@sundari.ro` / `admin123`
3. **Schimbă** parola admin imediat!
4. **Plasează** o comandă de test
5. **Verifică** email-urile

## 📞 Suport Hostico

- **Tel**: 031.104.28.50
- **Email**: support@hostico.ro
- **Doc**: https://hostico.ro/kb/

---

**Succes! 🚀**
