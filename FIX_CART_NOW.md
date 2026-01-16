# 🚨 FIX CART TABLE - Pași Simpli

## ⚡ Problema
Cart API returnează HTTP 0 pentru că tabelul `cart` nu are coloana `session_id` configurată corect.

## ✅ Soluție (2 minute)

### Pas 1: Deschide phpMyAdmin
1. Intră în **cPanel**
2. Click pe **phpMyAdmin**
3. Selectează baza de date: **fovyarnx_cusut** (din stânga)

### Pas 2: Deschide SQL Tab
1. Click pe tab-ul **SQL** (sus, în meniu)
2. Vei vedea o casetă mare pentru SQL

### Pas 3: Copiază și Rulează SQL

**Copiază EXACT acest SQL:**

```sql
USE fovyarnx_cusut;

ALTER TABLE cart 
MODIFY COLUMN session_id VARCHAR(128) DEFAULT NULL;

ALTER TABLE cart 
MODIFY COLUMN user_id INT DEFAULT NULL;

ALTER TABLE cart DROP INDEX unique_product;

ALTER TABLE cart 
ADD UNIQUE KEY unique_product (user_id, session_id, product_id);

SELECT 'Cart table fixed!' as status;
```

### Pas 4: Click "Go" (Execută)
1. Lipește SQL-ul în casetă
2. Click butonul **"Go"** sau **"Execută"** (jos-dreapta)
3. Ar trebui să vezi: **"Cart table fixed!"**

### Pas 5: Verifică
Accesează în browser:
```
https://www.piesemasinicusut.ro/verify_fixes.php
```

Ar trebui să vezi **toate testele cu ✓ (verde)**

---

## 🔍 Verificare Rapidă

După ce ai rulat SQL-ul, verifică:

1. **verify_fixes.php** - toate testele ✓
2. **Homepage** - funcționează
3. **Adaugă produs în coș** - fără erori
4. **Cart count** - se actualizează

---

## ⚠️ Dacă Apare Eroare

### Eroare: "Index unique_product doesn't exist"
**Soluție:** Ignoră, continuă cu următoarea comandă

### Eroare: "Column session_id doesn't exist"
**Soluție:** Rulează mai întâi:
```sql
ALTER TABLE cart ADD COLUMN session_id VARCHAR(128) DEFAULT NULL;
```

### Eroare: "Access denied"
**Soluție:** Verifică că ai selectat baza de date corectă (fovyarnx_cusut)

---

## 📱 Alternative - Dacă phpMyAdmin nu merge

### Opțiune 1: MySQL Command Line
```bash
mysql -u fovyarnx_usercusut -p fovyarnx_cusut < sql/fix_cart_simple.sql
```

### Opțiune 2: Rulează comenzile una câte una
În phpMyAdmin SQL tab, rulează fiecare comandă separat:

1. `ALTER TABLE cart MODIFY COLUMN session_id VARCHAR(128) DEFAULT NULL;`
2. `ALTER TABLE cart MODIFY COLUMN user_id INT DEFAULT NULL;`
3. `ALTER TABLE cart DROP INDEX unique_product;`
4. `ALTER TABLE cart ADD UNIQUE KEY unique_product (user_id, session_id, product_id);`

---

## ✅ Success!

După ce ai rulat SQL-ul cu succes:
- ✅ Cart API va funcționa
- ✅ Poți adăuga produse în coș
- ✅ Cart count se actualizează
- ✅ Toate testele din verify_fixes.php vor fi verzi

---

## 🆘 Ajutor

Dacă încă ai probleme:
1. Fă screenshot la eroarea din phpMyAdmin
2. Accesează check_errors.php și trimite output-ul
3. Verifică /logs/php_errors.log

---

**Timp estimat:** 2 minute  
**Dificultate:** Foarte ușor  
**Risc:** Zero (SQL-ul doar modifică structura, nu șterge date)
