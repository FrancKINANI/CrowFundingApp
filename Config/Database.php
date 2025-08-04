<?php

class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        // Load environment variables
        $host = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?? 'localhost';
        $dbname = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?? 'crowdfundingDb';
        $username = $_ENV['DB_USERNAME'] ?? getenv('DB_USERNAME') ?? 'root';
        $password = $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?? '';
        $port = $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?? '3306';

        try {
            $dsn = "mysql:host=$host;port=$port;charset=utf8mb4";
            $this->connection = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ]);
            $this->createDatabase($dbname);
        } catch (PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            if ($_ENV['APP_ENV'] ?? 'production' === 'development') {
                die("Database connection error: " . $e->getMessage());
            } else {
                die("Database connection error. Please try again later.");
            }
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance->connection;
    }

    private function createDatabase($dbname) {
        $this->connection->exec("CREATE DATABASE IF NOT EXISTS $dbname");
        $this->connection->exec("USE $dbname");
        $this->createTables();
    }

    private function createTables() {
        $tables = [
            'users' => "CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )",
            'projects' => "CREATE TABLE IF NOT EXISTS projects (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                description TEXT NOT NULL,
                goal_amount DECIMAL(10, 2) NOT NULL,
                user_id INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )",
            'donations' => "CREATE TABLE IF NOT EXISTS donations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                amount DECIMAL(10, 2) NOT NULL,
                project_id INT NOT NULL,
                user_id INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )"
        ];

        foreach ($tables as $query) {
            $this->connection->exec($query);
        }
    }
}

$db = Database::getInstance();