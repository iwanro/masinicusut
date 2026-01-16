<?php
/**
 * MANUAL DEPLOY SCRIPT
 * Încarcă acest fișier în cPanel File Manager la:
 * public_html/deploy_database.php
 * Apoi accesează: https://www.piesemasinicusut.ro/deploy_database.php
 */

// Conținutul corect al database.php
$correct_content = '<?php
/**
 * Database Connection - PDO with Auto-load
 * SUNDARI TOP STAR S.R.L.
 */

// Prevent direct access
defined("SITE_ROOT") OR exit("Direct access not allowed");

// Try to load PDO extensions if not loaded
if (!extension_loaded("pdo")) {
    @dl("pdo.so");
}

if (!extension_loaded("pdo_mysql")) {
    @dl("pdo_mysql.so");
}

class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        try {
            // Check if PDO is available
            if (!class_exists("PDO")) {
                throw new Exception(\'PDO extension not loaded. Contact hosting support to enable pdo_mysql extension.\');
            }

            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ];

            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            $this->pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
        } catch (PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            die("Eroare conectare la baza de date.");
        } catch (Exception $e) {
            error_log("Database error: " . $e->getMessage());
            die("Eroare: Extensia PDO nu este activă. Contactează suportul hosting.");
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->pdo;
    }

    private function __clone() {}

    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

if (!function_exists("db")) {
    function db() {
        return Database::getInstance()->getConnection();
    }
}
';

// Path către database.php
$db_file = __DIR__ . '/config/database.php';

// Scrie conținutul nou
if (file_put_contents($db_file, $correct_content)) {
    echo "<h1>✅ SUCCES!</h1>";
    echo "<p>database.php a fost actualizat!</p>";
    echo "<p><strong>Șterge acest fișier acum:</strong> public_html/deploy_database.php</p>";
    echo "<p><a href='/'>Intră pe site</a></p>";
} else {
    echo "<h1>❌ EROARE</h1>";
    echo "<p>Nu s-a putut scrie în $db_file</p>";
    echo "<p>Verifică permisiunile!</p>";
}
