<?php
/**
 * Test Conexiune Bază de Date
 */

// Configurare - completează cu datele tale
$db_host = 'localhost';
$db_name = 'nume_baza_date';       // ÎNLOCUIEȘTE cu numele complet din cPanel
$db_user = 'nume_user';             // ÎNLOCUIEȘTE cu user-ul complet din cPanel
$db_pass = 'parola_ta';             // ÎNLOCUIEȘTE cu parola

echo "==============================================\n";
echo "Test Conexiune Bază de Date\n";
echo "==============================================\n\n";

echo "Configurație curentă:\n";
echo "  Host: $db_host\n";
echo "  Database: $db_name\n";
echo "  User: $db_user\n";
echo "  Password: " . (empty($db_pass) ? '(GOL)' : '***') . "\n\n";

echo "Testare conexiune...\n\n";

try {
    $dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";
    $pdo = new PDO($dsn, $db_user, $db_pass);
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

    echo "\n==============================================\n";
    echo "✅ Totul pare OK!\n";
    echo "==============================================\n\n";

    echo "Completează config/config.php cu:\n";
    echo "  define('DB_HOST', '$db_host');\n";
    echo "  define('DB_NAME', '$db_name');\n";
    echo "  define('DB_USER', '$db_user');\n";
    echo "  define('DB_PASS', '$db_pass');\n";

} catch (PDOException $e) {
    echo "\n❌ EROARE DE CONEXIUNE!\n\n";
    echo "Mesaj eroare: " . $e->getMessage() . "\n\n";

    echo "==============================================\n";
    echo "Soluții posibile:\n";
    echo "==============================================\n\n";

    if (strpos($e->getMessage(), 'Access denied') !== false) {
        echo "1. Verifică userul și parola MySQL\n";
        echo "2. Verifică dacă user-ul are permisiuni la această bază de date\n";
        echo "3. În cPanel → MySQL Databases → verifică legătura user-DB\n\n";
    }

    if (strpos($e->getMessage(), "Unknown database") !== false) {
        echo "1. Baza de date '$db_name' nu există\n";
        echo "2. Creeaz-o din cPanel → MySQL Databases\n";
        echo "3. Importă scripturile SQL în phpMyAdmin\n\n";
    }

    if (strpos($e->getMessage(), 'Connection refused') !== false) {
        echo "1. MySQL nu rulează\n";
        echo "2. Verifică dacă 'localhost' e corect (poate fi '127.0.0.1')\n\n";
    }

    echo "Verifică în cPanel → MySQL Databases:\n";
    echo "  - Numele exact al bazei de date (cu prefix!)\n";
    echo "  - Numele exact al user-ului (cu prefix!)\n";
    echo "  - Parola corectă\n";
    echo "  - User-ul atașat la baza de date cu ALL PRIVILEGES\n\n";

    echo "Exemplu nume în cPanel:\n";
    echo "  - DB: numeutilizator_piese_m_cusut\n";
    echo "  - User: numeutilizator_user\n\n";
}
