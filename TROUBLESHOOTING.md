# Soluții Probleme Conexiune BD

## ❌ Eroare: "Eroare conectare la baza de date"

Această eroare înseamnă că site-ul nu se poate conecta la MySQL.

## 🔍 Cauze Cele Mai Comune

### 1. Nume Greșit al Bazei de Date ⚠️ FOARTE FRECEVENT

În cPanel, numele bazei de date are **prefix**.

**Exemplu**:
- Tu ai creat: `piese_m_cusut`
- Numele real în cPanel: `piesemas_piese_m_cusut`
  (unde `piesemas` este user-ul cPanel)

**Cum afli numele corect**:
1. Intră în cPanel → MySQL Databases
2. Caută secțiunea **"Current Databases"**
3. Vezi numele complet (ex: `piesemas_piese_m_cusut`)
4. Folosește numele COMPLET în `config/config.php`

### 2. Nume Greșit al User-ului

La fel ca DB-ul, user-ul are **prefix**.

**Exemplu**:
- Tu ai creat: `user`
- Numele real: `piesemas_user`

**Cum afli numele corect**:
1. În cPanel → MySQL Databases
2. Caută secțiunea **"Current Users"**
3. Vezi numele complet
4. Folosește numele COMPLET în `config/config.php`

### 3. Parolă Greșită

Verifică:
- Parola e corectă (copy/paste)
- Nu sunt spații la început/sfârșit
- Ai dat click pe "Change Password" după ce ai creat user-ul

### 4. User-ul Nu E Atașat la BD

Chiar dacă ai user și DB, trebuie să le atașezi:

1. cPanel → MySQL Databases
2. Sub **"Add User to Database"**:
   - Selectează user-ul
   - Selectează baza de date
   - Click **"Add"**
   - Bifează **"ALL PRIVILEGES"**
   - Click **"Make Changes"**

### 5. BD Nu Are Tabele

Dacă te conectezi dar paginile nu merg, probabil BD e goală.

**Soluție**:
1. Intră în phpMyAdmin
2. Selectează baza de date
3. Importă `database_cpanel.sql`
4. Importă `add_shipping_and_email_cpanel.sql`

## 📝 Script de Test

Folosește `test_db.php` pentru a depana:

1. **Completează datele în test_db.php**:
   ```php
   $db_name = 'piesemas_piese_m_cusut';  // Numele complet
   $db_user = 'piesemas_user';            // Numele complet
   $db_pass = 'parola_ta';
   ```

2. **Upload test_db.php pe server**

3. **Accesează**: `https://domeniultau.ro/test_db.php`

4. **Vezi mesajul de eroare detaliat**

5. **Corectează config.php** bazat pe ce îți spune scriptul

## ✅ Configurare Corectă

Exemplu `config/config.php`:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'piesemas_piese_m_cusut');   // NUME COMPLET cu prefix
define('DB_USER', 'piesemas_user');            // NUME COMPLET cu prefix
define('DB_PASS', 'Parola123!');               // Parola setată în cPanel
```

## 🔢 Cum Afli Numele Complet

### Metoda 1: Din cPanel

1. cPanel → **MySQL Databases**
2. Vezi secțiunile:
   - **Current Databases** → aici sunt toate bazele de date
   - **Current Users** → aici sunt toți user-ii

### Metoda 2: Din phpMyAdmin

1. cPanel → **phpMyAdmin**
2. În stânga vezi lista bazelor de date
3. Numele complete sunt acolo

### Metoda 3: Din fișierul de configurare WordPress (dacă există)

Dacă ai WordPress pe același hosting, vezi `wp-config.php`.

## 📞 Ajutor

Dacă tot nu merge:

1. Verifică **error logs**:
   - cPanel → **Errors** (sau **Raw Access Logs**)

2. Contactează Hostico:
   - Tel: 031.104.28.50
   - Email: support@hostico.ro
   - Spune-le: "Am o aplicație PHP care nu se conectează la MySQL"

3. Verifică dacă MySQL rulează:
   - cPanel → **SQL Services** → **MySQL® Databases**

## ⚠️ Important

După ce termini testele:
- **Șterge test_db.php de pe server** (conține parola în clar!)
