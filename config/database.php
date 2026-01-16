<?php
/**
 * Database Connection - PDO
 * SUNDARI TOP STAR S.R.L.
 */

// Prevent direct access
defined('SITE_ROOT') OR exit('Direct access not allowed');

class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ];

            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

            // Set charset manually after connection (compatible with PHP 8+)
            $this->pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
        } catch (PDOException $e) {
            // Log error și afișează mesaj generic
            error_log("Database connection error: " . $e->getMessage());
            die("Eroare conectare la baza de date. Vă rugăm încercați mai târziu.");
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

    // Previne clonarea
    private function __clone() {}

    // Previne unserializarea
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

// Funcție helper pentru a obține conexiunea rapid
if (!function_exists('db')) {
    function db() {
        return Database::getInstance()->getConnection();
    }
}
