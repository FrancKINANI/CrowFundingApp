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
                bio TEXT,
                avatar VARCHAR(255),
                location VARCHAR(255),
                website VARCHAR(255),
                is_verified BOOLEAN DEFAULT FALSE,
                is_admin BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )",
            'categories' => "CREATE TABLE IF NOT EXISTS categories (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL UNIQUE,
                description TEXT,
                icon VARCHAR(50),
                color VARCHAR(7) DEFAULT '#007bff',
                is_active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )",
            'projects' => "CREATE TABLE IF NOT EXISTS projects (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                slug VARCHAR(255) NOT NULL UNIQUE,
                description TEXT NOT NULL,
                short_description VARCHAR(500),
                goal_amount DECIMAL(10, 2) NOT NULL,
                current_amount DECIMAL(10, 2) DEFAULT 0.00,
                user_id INT NOT NULL,
                category_id INT,
                featured_image VARCHAR(255),
                video_url VARCHAR(500),
                start_date DATE,
                end_date DATE,
                status ENUM('draft', 'active', 'funded', 'expired', 'cancelled') DEFAULT 'draft',
                is_featured BOOLEAN DEFAULT FALSE,
                min_donation DECIMAL(10, 2) DEFAULT 1.00,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
                INDEX idx_status (status),
                INDEX idx_category (category_id),
                INDEX idx_featured (is_featured),
                INDEX idx_end_date (end_date)
            )",
            'project_images' => "CREATE TABLE IF NOT EXISTS project_images (
                id INT AUTO_INCREMENT PRIMARY KEY,
                project_id INT NOT NULL,
                image_path VARCHAR(255) NOT NULL,
                alt_text VARCHAR(255),
                is_primary BOOLEAN DEFAULT FALSE,
                sort_order INT DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
            )",
            'donations' => "CREATE TABLE IF NOT EXISTS donations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                amount DECIMAL(10, 2) NOT NULL,
                project_id INT NOT NULL,
                user_id INT NOT NULL,
                payment_method VARCHAR(50) DEFAULT 'manual',
                payment_status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'completed',
                payment_reference VARCHAR(255),
                is_anonymous BOOLEAN DEFAULT FALSE,
                message TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                INDEX idx_project (project_id),
                INDEX idx_user (user_id),
                INDEX idx_status (payment_status)
            )",
            'project_updates' => "CREATE TABLE IF NOT EXISTS project_updates (
                id INT AUTO_INCREMENT PRIMARY KEY,
                project_id INT NOT NULL,
                title VARCHAR(255) NOT NULL,
                content TEXT NOT NULL,
                is_public BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
            )",
            'project_comments' => "CREATE TABLE IF NOT EXISTS project_comments (
                id INT AUTO_INCREMENT PRIMARY KEY,
                project_id INT NOT NULL,
                user_id INT NOT NULL,
                parent_id INT NULL,
                content TEXT NOT NULL,
                is_approved BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (parent_id) REFERENCES project_comments(id) ON DELETE CASCADE
            )",
            'project_tags' => "CREATE TABLE IF NOT EXISTS project_tags (
                id INT AUTO_INCREMENT PRIMARY KEY,
                project_id INT NOT NULL,
                tag VARCHAR(50) NOT NULL,
                FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
                UNIQUE KEY unique_project_tag (project_id, tag)
            )",
            'user_favorites' => "CREATE TABLE IF NOT EXISTS user_favorites (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                project_id INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
                UNIQUE KEY unique_favorite (user_id, project_id)
            )",
            'notifications' => "CREATE TABLE IF NOT EXISTS notifications (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                type VARCHAR(50) NOT NULL,
                title VARCHAR(255) NOT NULL,
                message TEXT NOT NULL,
                data JSON,
                is_read BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                INDEX idx_user_unread (user_id, is_read)
            )"
        ];

        foreach ($tables as $tableName => $query) {
            try {
                $this->connection->exec($query);
                error_log("Created table: $tableName");
            } catch (PDOException $e) {
                error_log("Error creating table $tableName: " . $e->getMessage());
            }
        }

        // Insert default categories
        $this->insertDefaultCategories();
    }

    private function insertDefaultCategories() {
        $categories = [
            ['Technology', 'Innovative tech projects and startups', 'fas fa-laptop-code', '#007bff'],
            ['Arts & Crafts', 'Creative projects, art, and handmade items', 'fas fa-palette', '#e83e8c'],
            ['Music & Audio', 'Music albums, podcasts, and audio projects', 'fas fa-music', '#fd7e14'],
            ['Film & Video', 'Movies, documentaries, and video content', 'fas fa-video', '#dc3545'],
            ['Games', 'Board games, video games, and gaming projects', 'fas fa-gamepad', '#6f42c1'],
            ['Education', 'Educational content, courses, and learning materials', 'fas fa-graduation-cap', '#28a745'],
            ['Health & Fitness', 'Health, wellness, and fitness projects', 'fas fa-heartbeat', '#20c997'],
            ['Food & Beverage', 'Restaurants, food products, and culinary projects', 'fas fa-utensils', '#ffc107'],
            ['Fashion', 'Clothing, accessories, and fashion projects', 'fas fa-tshirt', '#e91e63'],
            ['Travel', 'Travel guides, experiences, and adventure projects', 'fas fa-plane', '#17a2b8'],
            ['Sports', 'Sports equipment, teams, and athletic projects', 'fas fa-football-ball', '#fd7e14'],
            ['Environment', 'Environmental and sustainability projects', 'fas fa-leaf', '#28a745'],
            ['Community', 'Local community and social impact projects', 'fas fa-users', '#6c757d'],
            ['Business', 'Startups, business ventures, and entrepreneurship', 'fas fa-briefcase', '#343a40'],
            ['Other', 'Projects that don\'t fit other categories', 'fas fa-question-circle', '#6c757d']
        ];

        $checkQuery = "SELECT COUNT(*) FROM categories";
        $stmt = $this->connection->query($checkQuery);
        $count = $stmt->fetchColumn();

        if ($count == 0) {
            $insertQuery = "INSERT INTO categories (name, description, icon, color) VALUES (?, ?, ?, ?)";
            $stmt = $this->connection->prepare($insertQuery);

            foreach ($categories as $category) {
                try {
                    $stmt->execute($category);
                } catch (PDOException $e) {
                    error_log("Error inserting category {$category[0]}: " . $e->getMessage());
                }
            }
            error_log("Inserted default categories");
        }
    }
}

$db = Database::getInstance();