<?php
/**
 * Database Connection - MySQLi (PDO Compatible)
 * SUNDARI TOP STAR S.R.L.
 */

// Prevent direct access
defined('SITE_ROOT') OR exit('Direct access not allowed');

class PDOStatement_MySQLi {
    private $stmt;
    private $result;
    private $params = [];

    public function __construct($mysqli_stmt) {
        $this->stmt = $mysqli_stmt;
    }

    public function execute($params = null) {
        if ($params) {
            $this->params = $params;
            $types = str_repeat('s', count($params));
            $this->stmt->bind_param($types, ...$this->bindReferences($params));
        }
        return $this->stmt->execute();
    }

    private function bindReferences($params) {
        $refs = [];
        foreach ($params as $k => $v) {
            $refs[$k] = &$params[$k];
        }
        return $refs;
    }

    public function fetch($style = null) {
        $this->result = $this->stmt->get_result();
        if ($this->result) {
            $row = $this->result->fetch_assoc();
            if ($style === PDO::FETCH_OBJ && $row) {
                return (object)$row;
            }
            return $row;
        }
        return null;
    }

    public function fetchAll($style = null) {
        $this->result = $this->stmt->get_result();
        if ($this->result) {
            $rows = $this->result->fetch_all(MYSQLI_ASSOC);
            if ($style === PDO::FETCH_OBJ && $rows) {
                return array_map(function($row) {
                    return (object)$row;
                }, $rows);
            }
            return $rows ?: [];
        }
        return [];
    }

    public function fetchColumn($column = 0) {
        $this->result = $this->stmt->get_result();
        if ($this->result) {
            $row = $this->result->fetch_array();
            return $row[$column] ?? null;
        }
        return null;
    }

    public function rowCount() {
        return $this->stmt->affected_rows;
    }

    public function closeCursor() {
        if ($this->result) {
            $this->result->free();
        }
        $this->stmt->reset();
        return true;
    }
}

class PDO_MySQLi {
    private $mysqli;

    public function __construct($dsn, $username, $password, $options = []) {
        // Parse DSN: mysql:host=...;dbname=...
        preg_match('/host=([^;]+)/', $dsn, $host_match);
        preg_match('/dbname=([^;]+)/', $dsn, $db_match);

        $host = $host_match[1] ?? 'localhost';
        $dbname = $db_match[1] ?? '';

        $this->mysqli = new mysqli($host, $username, $password, $dbname);

        if ($this->mysqli->connect_error) {
            throw new Exception("Connection failed: " . $this->mysqli->connect_error);
        }

        $this->mysqli->set_charset("utf8mb4");
    }

    public function prepare($query) {
        $stmt = $this->mysqli->prepare($query);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->mysqli->error);
        }
        return new PDOStatement_MySQLi($stmt);
    }

    public function query($query) {
        $result = $this->mysqli->query($query);
        if (!$result) {
            throw new Exception("Query failed: " . $this->mysqli->error);
        }

        if ($result instanceof mysqli_result) {
            $rows = [];
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
            $result->free();
            return $rows;
        }

        return true;
    }

    public function exec($query) {
        $this->mysqli->query($query);
        return $this->mysqli->affected_rows;
    }

    public function lastInsertId($name = null) {
        return $this->mysqli->insert_id;
    }

    public function quote($string) {
        return "'" . $this->mysqli->real_escape_string($string) . "'";
    }

    public function beginTransaction() {
        return $this->mysqli->begin_transaction();
    }

    public function commit() {
        return $this->mysqli->commit();
    }

    public function rollBack() {
        return $this->mysqli->rollback();
    }

    public function errorInfo() {
        return [
            0 => $this->mysqli->sqlstate,
            1 => $this->mysqli->errno,
            2 => $this->mysqli->error
        ];
    }
}

// Define PDO constants if using MySQLi
if (!extension_loaded('pdo')) {
    if (!class_exists('PDO')) {
        class PDO {
            const ATTR_ERRMODE = 1;
            const ERRMODE_EXCEPTION = 2;
            const ATTR_DEFAULT_FETCH_MODE = 3;
            const FETCH_ASSOC = 4;
            const FETCH_OBJ = 5;
            const ATTR_EMULATE_PREPARES = 6;
        }
    }
}

class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";

            // Try PDO first, fallback to MySQLi wrapper
            if (class_exists('PDO') && extension_loaded('pdo')) {
                $this->pdo = new \PDO($dsn, DB_USER, DB_PASS, [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    \PDO::ATTR_EMULATE_PREPARES => false
                ]);
                $this->pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
            } else {
                // Use MySQLi wrapper
                $this->pdo = new PDO_MySQLi($dsn, DB_USER, DB_PASS);
            }

        } catch (Exception $e) {
            error_log("Database connection error: " . $e->getMessage());
            die("<!DOCTYPE html><html><head><title>Eroare Database</title><style>body{font-family:Arial,sans-serif;max-width:600px;margin:50px auto;padding:20px;background:#fee}</style></head><body><h1>❌ Eroare Conexiune</h1><p>Eroare: " . htmlspecialchars($e->getMessage()) . "</p><p>Contactează administratorul!</p></body></html>");
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

if (!function_exists('db')) {
    function db() {
        return Database::getInstance()->getConnection();
    }
}
