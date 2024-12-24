<?php
namespace App\Config;

use PDO;
use PDOException;

class Database {
    private static $host = "localhost";
    private static $dbname = "crowdfunding_db";
    private static $username = "root";
    private static $password = "";
    private static $charset = "utf8mb4";

    private static $pdo = null;

    public static function getConnection() {
        if (self::$pdo === null) {
            try {
                $dsn = "mysql:host=" . self::$host . ";dbname=" . self::$dbname . ";charset=" . self::$charset;
                self::$pdo = new PDO($dsn, self::$username, self::$password);
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die("Database connection failed: " . $e->getMessage());
            }
        }
        return self::$pdo;
    }
}
