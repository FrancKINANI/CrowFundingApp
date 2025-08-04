<?php

/**
 * CrowdFunding Platform - Main Entry Point
 *
 * This file serves as the main entry point for the application.
 * It handles routing, security, and error handling.
 */

// Start session with security settings
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load configuration
require_once __DIR__ . '/../Config/config.php';

// Load security middleware
require_once __DIR__ . '/../App/Middleware/SecurityMiddleware.php';

// Load utilities
require_once __DIR__ . '/../App/Utils/Logger.php';
require_once __DIR__ . '/../App/Utils/Validator.php';

// Apply security headers
SecurityMiddleware::applySecurityHeaders();

// Set up error handling
set_error_handler(function($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return false;
    }

    Logger::error("PHP Error: {$message}", [
        'file' => $file,
        'line' => $line,
        'severity' => $severity
    ]);

    if (APP_ENV === 'development') {
        throw new ErrorException($message, 0, $severity, $file, $line);
    }

    return true;
});

set_exception_handler(function($exception) {
    Logger::critical("Uncaught Exception: " . $exception->getMessage(), [
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
        'trace' => $exception->getTraceAsString()
    ]);

    if (APP_ENV === 'development') {
        echo "<h1>Application Error</h1>";
        echo "<p>" . htmlspecialchars($exception->getMessage()) . "</p>";
        echo "<pre>" . htmlspecialchars($exception->getTraceAsString()) . "</pre>";
    } else {
        http_response_code(500);
        echo "<h1>Internal Server Error</h1>";
        echo "<p>We're sorry, but something went wrong. Please try again later.</p>";
    }
});

try {
    // Load database connection
    require_once __DIR__ . '/../Config/database.php';

    // Sanitize input
    $action = SecurityMiddleware::sanitizeInput($_GET['action'] ?? 'home');
    $method = $_SERVER['REQUEST_METHOD'];

    // Load and execute router
    require_once __DIR__ . '/../App/Controllers/Router.php';
    $router = new Router($db, $action, $method);

} catch (Exception $e) {
    Logger::critical("Application startup error: " . $e->getMessage());

    if (APP_ENV === 'development') {
        throw $e;
    } else {
        http_response_code(500);
        echo "<h1>Service Unavailable</h1>";
        echo "<p>The application is temporarily unavailable. Please try again later.</p>";
    }
}
