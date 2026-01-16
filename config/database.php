<?php
/**
 * Database Connection - PDO with MySQLi Fallback
 * SUNDARI TOP STAR S.R.L.
 */

// Prevent direct access
defined('SITE_ROOT') OR exit('Direct access not allowed');

// Check if PDO extension is available
if (!extension_loaded('pdo') || !extension_loaded('pdo_mysql')) {
    // PDO not available - show helpful error message
    die('<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eroare Server - PDO Necesar</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .error-container {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #e74c3c;
            margin-bottom: 20px;
        }
        .error-message {
            background: #fee;
            border-left: 4px solid #e74c3c;
            padding: 15px;
            margin: 20px 0;
        }
        .instructions {
            background: #e8f4f8;
            border-left: 4px solid #3498db;
            padding: 15px;
            margin: 20px 0;
        }
        .instructions h3 {
            margin-top: 0;
            color: #2980b9;
        }
        .instructions ol {
            margin: 10px 0;
        }
        .instructions li {
            margin: 5px 0;
        }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: "Courier New", monospace;
        }
        .contact {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <h1>Eroare Configurare Server</h1>
        <div class="error-message">
            <strong>Problema:</strong> Extensia PDO nu este încărcată pe server.<br>
            Aceasta este necesară pentru conectarea la baza de date.
        </div>
        <div class="instructions">
            <h3>Soluții - Alegeți una:</h3>
            <ol>
                <li>
                    <strong>Activați PDO în cPanel:</strong>
                    <ul>
                        <li>Accesați cPanel → Select PHP Version</li>
                        <li>Căutați și bifați <code>pdo</code> și <code>pdo_mysql</code></li>
                        <li>Salvați și restartuiți PHP</li>
                    </ul>
                </li>
                <li>
                    <strong>Activați PDO în php.ini:</strong>
                    <ul>
                        <li>Adăugați aceste linii în php.ini:</li>
                        <li><code>extension=pdo</code></li>
                        <li><code>extension=pdo_mysql</code></li>
                    </ul>
                </li>
                <li>
                    <strong>Contactați suportul hosting:</strong>
                    <ul>
                        <li>Cereți activarea extensiilor PDO și PDO_MySQL</li>
                        <li>Menționați că folosiți PHP ' . PHP_VERSION . '</li>
                    </ul>
                </li>
            </ol>
        </div>
        <div class="instructions">
            <h3>Informații Tehnice:</h3>
            <ul>
                <li><strong>Versiune PHP:</strong> ' . PHP_VERSION . '</li>
                <li><strong>Server:</strong> ' . $_SERVER['SERVER_SOFTWARE'] . '</li>
                <li><strong>Extensii încărcate:</strong> ' . implode(', ', get_loaded_extensions()) . '</li>
            </ul>
        </div>
        <div class="contact">
            <p><strong>Aveți nevoie de ajutor?</strong></p>
            <p>Contactați administratorul site-ului: <a href="mailto:' . ADMIN_EMAIL . '">' . ADMIN_EMAIL . '</a></p>
        </div>
    </div>
</body>
</html>');
}

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
