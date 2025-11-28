<?php
namespace Database;
use PDO;

/**
 * database/connection.php
 *
 * Purpose:
 *  - Provide a single PDO connection (singleton).
 *
 * Flow:
 *  - Reads DB env variables (or uses defaults).
 *  - Creates PDO with ERRMODE_EXCEPTION.
 */

class Connection {
    private static $pdo = null;

    public static function get() {
        if (!self::$pdo) {
            $host = getenv('DB_HOST') ?: '127.0.0.1';
            $db = getenv('DB_DATABASE') ?: 'jollikod';
            $user = getenv('DB_USERNAME') ?: 'root';
            $pass = getenv('DB_PASSWORD') ?: '';
            $dsn = "\"mysql:host={$host};dbname={$db};charset=utf8mb4\"";
            self::$pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        }
        return self::$pdo;
    }
}
