# SUNDARI TOP STAR S.R.L. - E-Commerce Site

**Site e-commerce pentru piese, accesorii și consumabile mașini de cusut**

## ✅ Funcționalități Implementate

### Frontend (Public)
- ✅ Homepage cu produse featured
- ✅ Catalog produse cu filtre (marcă + tip produs)
- ✅ Pagină produs cu detalii complete
- ✅ Coș de cumpărături (AJAX)
- ✅ Sistem comenzi (fără plată online)
- ✅ Înregistrare utilizatori
- ✅ Login cu sesiuni securizate
- ✅ Cont utilizator cu istoric comenzi
- ✅ Pagină contact

### Admin Panel
- ✅ Dashboard cu statistici
- ✅ CRUD complet Produse (add/edit/delete)
- ✅ Management Categorii (brand-uri + tipuri produse)
- ✅ Management Comenzi (status, detalii)
- ✅ Management Utilizatori (vizualizare, roluri)

---

## 📋 Cerințe Sistem

### Server
- **Web Server**: Apache (mod_rewrite recomandat) sau Nginx
- **PHP**: 7.4 sau 8.x
- **MySQL**: 5.7+ sau 8.x

### Extensii PHP
- PDO_mysql
- GD (pentru upload imagini)
- Mbstring

---

## 🚀 Instalare

### 1. Copiere fișiere
Copiază toate fișierele pe server în folderul public (de obicei `public_html` sau `www`).

### 2. Creare Bază de Date
- Intră în cPanel/phpMyAdmin
- Creează o bază de date nouă (ex: `piese_masini_cusut`)
- Creează un utilizator pentru baza de date
- Importează fișierul `sql/database.sql`

```bash
# Din terminal (dacă ai acces):
mysql -u utilizator -p nume_db < sql/database.sql
```

### 3. Configurare Conexiune BD
Editează `config/config.php` și modifică datele de conectare:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'numele_bazei_de_date_tale');
define('DB_USER', 'utilizatorul_tau');
define('DB_PASS', 'parola_ta');
```

### 4. Configurare Site URL
În același fișier `config/config.php`, modifică URL-ul site-ului:

```php
define('SITE_URL', 'https://numele-siteului-tau.ro');
```

### 5. Permisiuni Directoare
Asigură-te că următoarele directoare au permisiuni de scriere:

```bash
chmod 755 assets/images/products
```

---

## 👤 Utilizatori

### Admin Default
După importul bazei de date, ai deja un utilizator admin creat:

- **Email**: `admin@sundari.ro`
- **Parola**: `admin123`

⚠️ **IMPORTANT**: Schimbă parola adminului imediat după primul login!

---

## 📂 Structură Proiect

```
PieseMasiniCusut/
├── index.php                 # Homepage
├── config/
│   ├── database.php         # MySQL connection (PDO)
│   └── config.php           # Global settings & constants
├── includes/
│   ├── header.php           # HTML head, nav
│   ├── footer.php           # Footer, scripts
│   ├── functions.php        # Helper functions
│   └── auth.php             # Auth system
├── assets/
│   ├── css/
│   │   ├── style.css        # Frontend styles
│   │   └── admin.css        # Admin panel styles
│   ├── js/
│   │   └── main.js          # Frontend JavaScript
│   └── images/
│       └── products/        # Product images upload folder
├── pages/
│   ├── catalog.php          # Product catalog with filters
│   ├── product.php          # Single product page
│   ├── cart.php             # Shopping cart
│   ├── checkout.php         # Order placement
│   ├── account.php          # User dashboard
│   ├── register.php         # User registration
│   ├── login.php            # User login
│   ├── logout.php           # User logout
│   └── contact.php          # Contact page
├── admin/
│   ├── index.php            # Admin dashboard
│   ├── products.php         # Products CRUD
│   ├── categories.php       # Categories management
│   ├── orders.php           # Orders management
│   └── users.php            # Users management
├── api/
│   └── cart.php             # Cart API endpoints
└── sql/
    └── database.sql         # Database schema
```

---

## 🔐 Securitate

### Password Hashing
Toate parolele sunt hashuite folosind `password_hash()` (bcrypt).

### CSRF Protection
Toate formularele POST sunt protejate cu token-uri CSRF.

### SQL Injection
Toate interogările folosesc prepared statements (PDO).

### XSS Prevention
Output-ul este escaped cu `htmlspecialchars()`.

### Session Security
- Sesiuni cu httponly și secure flags
- Regenerare session ID la login
- Durată sesiune: 24 ore

---

## 🎨 Personalizare

### Culori
Modifică variabilele CSS în `assets/css/style.css`:

```css
:root {
    --primary-color: #2c3e50;
    --secondary-color: #e74c3c;
    --accent-color: #3498db;
}
```

### Setări Site
Setările sunt stocate în baza de date (tabelul `settings`). Poți modifica:
- Nume site
- Email contact
- Telefon
- Cost transport
- Prag transport gratuit

### Brand-uri
Produsele sunt organizate după:
- **Brand** (Singer, Brother, etc.)
- **Tip Produs** (Ace, Cărlige, etc.)

---

## 📝 Utilizare

### Pentru Cumpărători
1. Navighează în catalog
2. Folosește filtrele după marcă/tip
3. Adaugă produse în coș
4. Finalizează comanda (necesită cont)
5. Vezi istoric comenzi în contul tău

### Pentru Admin
1. Login cu contul de admin
2. Accesează `/admin/index.php`
3. Adaugă produse în **Produse**
4. Organizează categoriile în **Categorii**
5. Procesează comenzi în **Comenzi**
6. Gestionează utilizatori în **Utilizatori**

---

## 🛠️ Menținere

### Backup Bază de Date
```bash
mysqldump -u utilizator -p nume_db > backup_$(date +%Y%m%d).sql
```

### Backup Fișiere
```bash
tar -czf backup_files_$(date +%Y%m%d).tar.gz .
```

---

## 🐛 Troubleshooting

### Eroare conectare BD
Verifică datele din `config/config.php` și asigură-te că:
- BD există
- Utilizatorul are permisiuni
- Extensia PDO_mysql este încărcată

### Nu se încarcă imaginile
Verifică permisiunile folderului `assets/images/products`:
```bash
chmod 755 assets/images/products
```

### Sesiuane expiră
Modifică durata în `config/config.php`:
```php
define('SESSION_LIFETIME', 86400); // în secunde
```

---

## 📞 Support

Pentru întrebări sau probleme:
- **Email**: contact@sundari.ro
- **Telefon**: +40 700 000 000

---

## 📄 Licență

Proprietate privată - SUNDARI TOP STAR S.R.L.
Toate drepturile rezervate © <?= date('Y') ?>

---

**Dezvoltat pentru**: SUNDARI TOP STAR S.R.L.
**Tehnologii**: PHP 8, MySQL 8, HTML5, CSS3, JavaScript (Vanilla)
**Compatibilitate**: Hostico.ro
