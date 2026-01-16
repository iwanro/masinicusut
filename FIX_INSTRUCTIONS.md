# Instrucțiuni de Reparare - Piese Mașini de Cusut

## Probleme Identificate și Soluții

### 1. ❌ Eroare Cart API - "Column not found: session_id"

**Problema:** Tabelul `cart` are probleme cu coloana `session_id`.

**Soluție:**
```bash
# Rulează acest SQL în phpMyAdmin sau MySQL:
mysql -u fovyarnx_usercusut -p fovyarnx_cusut < sql/fix_cart_session.sql
```

SAU manual în phpMyAdmin:
```sql
USE fovyarnx_cusut;

ALTER TABLE cart 
MODIFY COLUMN session_id VARCHAR(128) DEFAULT NULL,
MODIFY COLUMN user_id INT DEFAULT NULL;

ALTER TABLE cart DROP INDEX IF EXISTS unique_product;
ALTER TABLE cart ADD UNIQUE KEY unique_product (user_id, session_id, product_id);
```

### 2. ❌ Pagini Albe (login.php, register.php)

**Problema:** Erori PHP care nu sunt afișate.

**Soluție:**
1. Accesează: `https://www.piesemasinicusut.ro/check_errors.php`
2. Verifică ce erori apar
3. Verifică logurile în `/logs/php_errors.log`

**Cauze posibile:**
- Funcții lipsă în `includes/functions.php`
- Probleme cu `getSetting()` function
- Erori de sesiune

### 3. ✅ Număr de Telefon în Header - FIXED

**Soluție aplicată:**
- Am adăugat CSS pentru `.action-phone` în `assets/css/style.css`
- Am făcut numărul vizibil și colorat în portocaliu
- Numărul apare acum: **0766 221 688**

### 4. ❌ Eroare JSON la Adăugare în Coș

**Problema:** Cart API returnează eroare 500 sau JSON invalid.

**Soluție aplicată:**
- Am fixat ordinea în `api/cart.php` (session_start înainte de a folosi session_id)
- Am adăugat header JSON înainte de orice output

**Test:**
```bash
# Accesează pentru a testa:
https://www.piesemasinicusut.ro/test_cart_fix.php
```

### 5. ⏳ Carduri "Mărci Populare" cu Săgeți Laterale

**Implementare necesară:**

Adaugă acest cod în `index.php` sau în fișierul unde sunt afișate mărcile:

```html
<!-- Brands Carousel -->
<div class="brands-carousel-container">
    <button class="carousel-btn carousel-prev" onclick="scrollBrands(-1)">
        <i class="fas fa-chevron-left"></i>
    </button>
    
    <div class="brands-carousel" id="brands-carousel">
        <!-- Brand cards here -->
    </div>
    
    <button class="carousel-btn carousel-next" onclick="scrollBrands(1)">
        <i class="fas fa-chevron-right"></i>
    </button>
</div>
```

Adaugă CSS:
```css
.brands-carousel-container {
    position: relative;
    padding: 0 50px;
}

.brands-carousel {
    display: flex;
    gap: 20px;
    overflow-x: auto;
    scroll-behavior: smooth;
    scrollbar-width: none;
}

.brands-carousel::-webkit-scrollbar {
    display: none;
}

.carousel-btn {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: white;
    border: 1px solid #e2e8f0;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    cursor: pointer;
    z-index: 10;
}

.carousel-prev { left: 0; }
.carousel-next { right: 0; }
```

Adaugă JavaScript:
```javascript
function scrollBrands(direction) {
    const carousel = document.getElementById('brands-carousel');
    const scrollAmount = 300;
    carousel.scrollBy({
        left: direction * scrollAmount,
        behavior: 'smooth'
    });
}
```

## Pași de Urmat

### Pas 1: Fixează Database
```bash
# Conectează-te la cPanel > phpMyAdmin
# Selectează baza de date: fovyarnx_cusut
# Rulează SQL din sql/fix_cart_session.sql
```

### Pas 2: Verifică Erorile
```bash
# Accesează în browser:
https://www.piesemasinicusut.ro/check_errors.php

# Verifică ce erori apar și notează-le
```

### Pas 3: Testează Cart API
```bash
# Accesează în browser:
https://www.piesemasinicusut.ro/test_cart_fix.php

# Verifică dacă toate testele trec (✓)
```

### Pas 4: Testează Paginile
```bash
# Încearcă să accesezi:
https://www.piesemasinicusut.ro/pages/login.php
https://www.piesemasinicusut.ro/pages/register.php

# Dacă încă sunt albe, verifică logs/php_errors.log
```

### Pas 5: Curăță Cache
```bash
# În browser:
- Apasă Ctrl+Shift+R (hard refresh)
- Sau Ctrl+F5
- Sau șterge cache-ul browserului
```

## Verificare Finală

După ce ai aplicat toate fix-urile, verifică:

- [ ] Login page se încarcă corect
- [ ] Register page se încarcă corect
- [ ] Numărul de telefon apare în header (0766 221 688)
- [ ] Poți adăuga produse în coș fără erori
- [ ] Cart count se actualizează corect
- [ ] Mărcile populare au săgeți de navigare

## Fișiere Modificate

1. ✅ `api/cart.php` - Fixed session_start order
2. ✅ `includes/header.php` - Fixed phone number display
3. ✅ `assets/css/style.css` - Added phone styles
4. ✅ `sql/fix_cart_session.sql` - Database fix
5. ✅ `test_cart_fix.php` - Testing tool
6. ✅ `check_errors.php` - Error checking tool

## Suport

Dacă problemele persistă:
1. Verifică `/logs/php_errors.log`
2. Verifică `/logs/cart_debug.log`
3. Rulează `check_errors.php` și trimite output-ul
4. Rulează `test_cart_fix.php` și trimite output-ul

## Note Importante

- **BACKUP:** Fă backup la baza de date înainte de a rula SQL-ul
- **CACHE:** Curăță cache-ul browserului după fiecare modificare
- **LOGS:** Verifică întotdeauna logurile pentru erori detaliate
- **PERMISSIONS:** Asigură-te că folderul `/logs` are permisiuni de scriere (755)
