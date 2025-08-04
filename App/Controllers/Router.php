<?php

if(!isset($_SESSION)) { 
    session_start(); 
}

require_once __DIR__ . '/../../App/Controllers/HomeController.php';
require_once __DIR__ . '/../../App/Controllers/ProjectController.php';
require_once __DIR__ . '/../../App/Controllers/AuthController.php';
require_once __DIR__ . '/../../App/Controllers/UserController.php';
require_once __DIR__ . '/../../App/Controllers/DonationController.php';
require_once __DIR__ . '/../../App/Controllers/CategoryController.php';
require_once __DIR__ . '/../../App/Controllers/SearchController.php';



class Router {
    private $db;
    private $routes = [];

    public function __construct($db, $action, $method) {
        $this->db = $db;
        $this->defineRoutes();
        $this->handleRequest($action, $method);
    }

    private function defineRoutes() {
        $this->routes = [
            'GET' => [
                // Core routes
                'home' => [HomeController::class, 'index'],
                'login' => [AuthController::class, 'showLoginForm'],
                'register' => [AuthController::class, 'showRegisterForm'],
                'logout' => [AuthController::class, 'logout'],
                'dashboard' => [UserController::class, 'dashboard'],

                // Project routes
                'create' => [ProjectController::class, 'createProject'],
                'projectDetails' => [ProjectController::class, 'details'],
                'list' => [ProjectController::class, 'list'],
                'editProject' => [ProjectController::class, 'edit'],
                'deleteProject' => [ProjectController::class, 'delete'],
                'detailsProject' => [ProjectController::class, 'details'],

                // Donation routes
                'donate' => [DonationController::class, 'createDonation'],
                'editDonation' => [DonationController::class, 'edit'],
                'deleteDonation' => [DonationController::class, 'deleteDonation'],

                // Category routes
                'categories' => [CategoryController::class, 'index'],
                'category' => [CategoryController::class, 'show'],

                // Search routes
                'search' => [SearchController::class, 'search'],
                'search_advanced' => [SearchController::class, 'advanced'],
                'search_suggestions' => [SearchController::class, 'suggestions'],
                'search_filter' => [SearchController::class, 'filter'],
                'trending' => [SearchController::class, 'trending'],
                'ending_soon' => [SearchController::class, 'endingSoon'],

                // Admin routes
                'admin_categories' => [CategoryController::class, 'adminIndex'],
                'admin_category_create' => [CategoryController::class, 'create'],
                'admin_category_edit' => [CategoryController::class, 'edit'],
                'admin_category_toggle' => [CategoryController::class, 'toggleStatus'],
                'admin_category_delete' => [CategoryController::class, 'delete'],
            ],
            'POST' => [
                // Authentication
                'login' => [AuthController::class, 'login'],
                'register' => [AuthController::class, 'register'],

                // Projects
                'create' => [ProjectController::class, 'create'],
                'updateProject' => [ProjectController::class, 'updateProject'],
                'confirmDeleteProject' => [ProjectController::class, 'confirmDelete'],

                // Donations
                'submitDonation' => [DonationController::class, 'submit'],
                'updateDonation' => [DonationController::class, 'updateDonation'],
                'confirmDeleteDonation' => [DonationController::class, 'confirmDeleteDonation'],

                // Admin categories
                'admin_category_create' => [CategoryController::class, 'create'],
                'admin_category_edit' => [CategoryController::class, 'edit'],
                'admin_category_delete' => [CategoryController::class, 'delete'],
            ]
        ];
    }

    public function handleRequest($action, $method) {
        if (isset($this->routes[$method][$action])) {
            list($controllerClass, $methodName) = $this->routes[$method][$action];
            error_log("Attempting to load controller: " . $controllerClass);

            if (class_exists($controllerClass)) {
                $controller = new $controllerClass($this->db);
                
                if (method_exists($controller, $methodName)) {
                    $controller->$methodName();
                } else {
                    $this->handleError(500);
                    $error = "Method not found: " . $methodName;
                }
            } else {
                $this->handleError(500);
                $error = "Controller class not found: " . $controllerClass;
            }
        } else {
            $this->handleError(404);
        }
    }

    private function handleError($code) {
        http_response_code($code);

        Logger::error("HTTP Error $code", [
            'url' => $_SERVER['REQUEST_URI'] ?? 'unknown',
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);

        switch ($code) {
            case 404:
                $title = "Page Not Found";
                $message = "The page you are looking for could not be found.";
                break;
            case 500:
                $title = "Internal Server Error";
                $message = "We're sorry, but something went wrong on our end.";
                break;
            default:
                $title = "Error";
                $message = "An unexpected error occurred.";
                break;
        }

        // Load error page template
        $this->loadErrorPage($code, $title, $message);
    }

    private function loadErrorPage($code, $title, $message) {
        echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>$title</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
        .error-container { max-width: 500px; margin: 0 auto; }
        h1 { color: #e74c3c; }
        .btn { display: inline-block; padding: 10px 20px; background: #3498db; color: white; text-decoration: none; border-radius: 5px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class='error-container'>
        <h1>$code</h1>
        <h2>$title</h2>
        <p>$message</p>
        <a href='" . app_url('public/index.php') . "' class='btn'>Go Home</a>
    </div>
</body>
</html>";
    }
}
