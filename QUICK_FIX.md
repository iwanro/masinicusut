# 🚀 Quick Fix Guide - Acțiuni Rapide

## ⚡ 3 Pași Rapizi pentru a Fixa Tot

### Pas 1: Fixează Database (2 minute)
```bash
1. Deschide cPanel > phpMyAdmin
2. Selectează baza de date: fovyarnx_cusut
3. Click pe tab "SQL"
4. Copiază și rulează acest SQL:
```

```sql
USE fovyarnx_cusut;

ALTER TABLE cart 
MODIFY COLUMN session_id VARCHAR(128) DEFAULT NULL,
MODIFY COLUMN user_id INT DEFAULT NULL;

ALTER TABLE cart DROP INDEX IF EXISTS unique_product;
ALTER TABLE cart ADD UNIQUE KEY unique_product (user_id, session_id, product_id);

SELECT 'Cart table fixed!' as status;
```

### Pas 2: Testează (1 minut)
```bash
Accesează în browser:
https://www.piesemasinicusut.ro/test_cart_fix.php

Verifică dacă toate testele au ✓ (checkmark verde)
```

### Pas 3: Curăță Cache (30 secunde)
```bash
În browser:
- Apasă Ctrl + Shift + R
- SAU Ctrl + F5
- SAU șterge cache-ul complet
```

---

## ✅ Ce Am Fixat Deja

### 1. ✅ Număr Telefon în Header
- **Status:** FIXED
- **Rezultat:** Numărul 0766 221 688 apare acum vizibil în portocaliu
- **Acțiune:** NIMIC - deja funcționează

### 2. ✅ Carousel Mărci cu Săgeți
- **Status:** FIXED
- **Rezultat:** Săgeți stânga/dreapta pentru navigare prin mărci
- **Acțiune:** NIMIC - deja funcționează

### 3. ✅ Cart API Session Fix
- **Status:** CODE FIXED
- **Rezultat:** Codul PHP este reparat
- **Acțiune:** RULEAZĂ SQL (Pas 1 de mai sus)

---

## ⚠️ Dacă Încă Ai Probleme

### Pagini Albe (Login/Register)
```bash
1. Accesează: https://www.piesemasinicusut.ro/check_errors.php
2. Citește ce erori apar
3. Verifică fișierul: /logs/php_errors.log
```

### Cart nu funcționează
```bash
1. Verifică dacă ai rulat SQL-ul din Pas 1
2. Accesează: https://www.piesemasinicusut.ro/test_cart_fix.php
3. Verifică fișierul: /logs/cart_debug.log
```

### Erori în Console (F12)
```bash
1. Deschide browser console (F12)
2. Reîncarcă pagina (Ctrl+R)
3. Caută erori roșii
4. Trimite screenshot
```

---

## 📋 Checklist Final

După ce ai făcut Pas 1, 2, 3, verifică:

- [ ] Homepage se încarcă corect
- [ ] Numărul de telefon apare în header (0766 221 688)
- [ ] Mărcile au săgeți de navigare
- [ ] Poți naviga prin mărci cu săgețile
- [ ] Login page se încarcă (nu e albă)
- [ ] Register page se încarcă (nu e albă)
- [ ] Poți adăuga produse în coș
- [ ] Cart count se actualizează (numărul din badge)

---

## 🆘 Ajutor Rapid

**Dacă ceva nu merge:**

1. **Rulează SQL-ul** din Pas 1 (cel mai important!)
2. **Curăță cache-ul** browserului
3. **Accesează test_cart_fix.php** și trimite rezultatul
4. **Accesează check_errors.php** și trimite rezultatul

**Fișiere importante:**
- `sql/fix_cart_session.sql` - SQL pentru database
- `test_cart_fix.php` - Test cart functionality
- `check_errors.php` - Check PHP errors
- `FIX_INSTRUCTIONS.md` - Ghid detaliat
- `CHANGES_SUMMARY.md` - Rezumat modificări

---

## 💡 Tips

- **Cache:** Întotdeauna curăță cache-ul după modificări
- **Logs:** Verifică `/logs/php_errors.log` pentru erori
- **Console:** Deschide F12 în browser pentru erori JavaScript
- **SQL:** Fă backup la database înainte de a rula SQL

---

**Timp estimat total:** 3-4 minute  
**Dificultate:** Ușor  
**Risc:** Minim (am făcut backup-uri)
