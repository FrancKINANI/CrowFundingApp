<?php

/**
 * Application Configuration
 * 
 * This file contains all the configuration settings for the CrowdFunding application.
 * Environment variables take precedence over default values.
 */

// Load environment variables from .env file if it exists
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        list($name, $value) = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value);
    }
}

// Application Configuration
define('APP_NAME', $_ENV['APP_NAME'] ?? 'CrowdFunding Platform');
define('APP_ENV', $_ENV['APP_ENV'] ?? 'production');
define('APP_DEBUG', filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN));
define('APP_URL', $_ENV['APP_URL'] ?? 'http://localhost');
define('APP_VERSION', '1.0.0');

// Database Configuration
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_PORT', $_ENV['DB_PORT'] ?? '3306');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'crowdfundingDb');
define('DB_USERNAME', $_ENV['DB_USERNAME'] ?? 'root');
define('DB_PASSWORD', $_ENV['DB_PASSWORD'] ?? '');

// Security Configuration
define('APP_KEY', $_ENV['APP_KEY'] ?? 'your-secret-key-here-change-in-production');
define('SESSION_LIFETIME', (int)($_ENV['SESSION_LIFETIME'] ?? 7200)); // 2 hours
define('CSRF_TOKEN_LIFETIME', (int)($_ENV['CSRF_TOKEN_LIFETIME'] ?? 3600)); // 1 hour

// File Upload Configuration
define('MAX_UPLOAD_SIZE', (int)($_ENV['MAX_UPLOAD_SIZE'] ?? 5242880)); // 5MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// Email Configuration (for future use)
define('MAIL_HOST', $_ENV['MAIL_HOST'] ?? 'localhost');
define('MAIL_PORT', (int)($_ENV['MAIL_PORT'] ?? 587));
define('MAIL_USERNAME', $_ENV['MAIL_USERNAME'] ?? '');
define('MAIL_PASSWORD', $_ENV['MAIL_PASSWORD'] ?? '');
define('MAIL_FROM_ADDRESS', $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@crowdfunding.local');
define('MAIL_FROM_NAME', $_ENV['MAIL_FROM_NAME'] ?? APP_NAME);

// Pagination Configuration
define('PROJECTS_PER_PAGE', (int)($_ENV['PROJECTS_PER_PAGE'] ?? 12));
define('DONATIONS_PER_PAGE', (int)($_ENV['DONATIONS_PER_PAGE'] ?? 20));

// Cache Configuration
define('CACHE_ENABLED', filter_var($_ENV['CACHE_ENABLED'] ?? false, FILTER_VALIDATE_BOOLEAN));
define('CACHE_LIFETIME', (int)($_ENV['CACHE_LIFETIME'] ?? 3600)); // 1 hour

// Logging Configuration
define('LOG_LEVEL', $_ENV['LOG_LEVEL'] ?? 'error');
define('LOG_FILE', $_ENV['LOG_FILE'] ?? __DIR__ . '/../logs/app.log');

// Set error reporting based on environment
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
} else {
    error_reporting(E_ERROR | E_WARNING | E_PARSE);
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
}

// Set timezone
date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'UTC');

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? 1 : 0);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.gc_maxlifetime', SESSION_LIFETIME);

/**
 * Get configuration value
 * 
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function config($key, $default = null) {
    return defined($key) ? constant($key) : $default;
}

/**
 * Check if application is in debug mode
 * 
 * @return bool
 */
function is_debug() {
    return APP_DEBUG;
}

/**
 * Get application URL
 * 
 * @param string $path
 * @return string
 */
function app_url($path = '') {
    return rtrim(APP_URL, '/') . '/' . ltrim($path, '/');
}

/**
 * Generate CSRF token
 * 
 * @return string
 */
function csrf_token() {
    if (!isset($_SESSION['csrf_token']) || 
        !isset($_SESSION['csrf_token_time']) || 
        (time() - $_SESSION['csrf_token_time']) > CSRF_TOKEN_LIFETIME) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 * 
 * @param string $token
 * @return bool
 */
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && 
           isset($_SESSION['csrf_token_time']) &&
           (time() - $_SESSION['csrf_token_time']) <= CSRF_TOKEN_LIFETIME &&
           hash_equals($_SESSION['csrf_token'], $token);
}
