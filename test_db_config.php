<?php
/**
 * Test Conexiune BD - Citește din config.php
 */

// Încarcă config
require_once __DIR__ . '/config/config.php';

echo "==============================================\n";
echo "Test Conexiune Bază de Date\n";
echo "==============================================\n\n";

echo "Configurație din config.php:\n";
echo "  Host: " . DB_HOST . "\n";
echo "  Database: " . DB_NAME . "\n";
echo "  User: " . DB_USER . "\n";
echo "  Password: " . (empty(DB_PASS) ? '(GOL)' : '***') . "\n";
echo "  URL: " . SITE_URL . "\n\n";

echo "Testare conexiune...\n\n";

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "✅ CONEXIUNE REUȘITĂ!\n\n";

    // Verifică tabelele
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "Tabele în baza de date (" . count($tables) . "):\n";
    foreach ($tables as $table) {
        echo "  - $table\n";
    }
    echo "\n";

    // Verifică users table
    if (in_array('users', $tables)) {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
        $count = $stmt->fetch()['count'];
        echo "Utilizatori în BD: $count\n";
    }

    // Verifică products table
    if (in_array('products', $tables)) {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM products");
        $count = $stmt->fetch()['count'];
        echo "Produse în BD: $count\n";
    }

    echo "\n==============================================\n";
    echo "✅ BD e OK! Site-ul ar trebui să meargă!\n";
    echo "==============================================\n\n";

    echo "Dacă totuși primești eroare pe site:\n";
    echo "1. Șterge cache-ul browser-ului\n";
    echo "2. Verifică permisiuni fișiere config/config.php (644)\n";
    echo "3. Verifică error_log pentru detalii\n";

} catch (PDOException $e) {
    echo "\n❌ EROARE DE CONEXIUNE!\n\n";
    echo "Mesaj eroare: " . $e->getMessage() . "\n\n";

    echo "==============================================\n";
    echo "Cauze posibile:\n";
    echo "==============================================\n\n";

    $errorMsg = $e->getMessage();

    if (strpos($errorMsg, 'Access denied') !== false) {
        echo "❌ PROBLEMĂ: Access denied - User sau parolă greșită!\n\n";

        echo "Verifică în cPanel → MySQL Databases:\n\n";

        echo "1. **Verifică User-ul**:\n";
        echo "   - Intră în cPanel → MySQL Databases\n";
        echo "   - Caută 'fovyarnx_usercusut' în 'Current Users'\n";
        echo "   - Dacă nu există, creează-l!\n\n";

        echo "2. **Verifică Baza de Date**:\n";
        echo "   - Caută 'fovyarnx_cusut' în 'Current Databases'\n";
        echo "   - Dacă nu există, creeaz-o!\n\n";

        echo "3. **Atașează User-ul la BD** (FOARTE IMPORTANT!):\n";
        echo "   - Sub 'Add User to Database'\n";
        echo "   - Selectează: fovyarnx_usercusut\n";
        echo "   - Selectează: fovyarnx_cusut\n";
        echo "   - Click 'Add'\n";
        echo "   - Bifează 'ALL PRIVILEGES'\n";
        echo "   - Click 'Make Changes'\n\n";
    }

    if (strpos($errorMsg, "Unknown database") !== false) {
        echo "❌ PROBLEMĂ: Baza de date nu există!\n\n";
        echo "Soluții:\n";
        echo "1. Creează BD 'fovyarnx_cusut' din cPanel → MySQL Databases\n";
        echo "2. Importă database_cpanel.sql în phpMyAdmin\n\n";
    }

    echo "==============================================\n";
    echo "Pași de verificare:\n";
    echo "==============================================\n\n";

    echo "1. Intră în cPanel → MySQL Databases\n";
    echo "2. Verifică secțiunea 'Current Databases'\n";
    echo "   - Trebuie să vezi: fovyarnx_cusut\n";
    echo "3. Verifică secțiunea 'Current Users'\n";
    echo "   - Trebuie să vezi: fovyarnx_usercusut\n";
    echo "4. Sub 'Add User to Database':\n";
    echo "   - User: fovyarnx_usercusut\n";
    echo "   - Database: fovyarnx_cusut\n";
    echo "   - privileges: ALL PRIVILEGES\n\n";
}
