<?php

require_once __DIR__ . '/../../Config/Database.php';
require_once __DIR__ . '/../../Config/autoload.php';

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
                'home' => [HomeController::class, 'index'],
                'login' => [AuthController::class, 'showLoginForm'],
                'register' => [AuthController::class, 'showRegisterForm'],
                'create' => [ProjectController::class, 'createProject'],
                'dashboard' => [ProjectController::class, 'dashboard'],
                'donate' => [DonationController::class, 'createDonation'],
                'projectDetails' => [ProjectController::class, 'details'],
                // Add other GET routes here
            ],
            'POST' => [
                'login' => [AuthController::class, 'login'],
                'register' => [AuthController::class, 'register'],
                'create' => [ProjectController::class, 'create'],
                'submitDonation' => [DonationController::class, 'addDonation'],
                // Add other POST routes here
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
                    error_log("Method not found: " . $methodName);
                }
            } else {
                $this->handleError(500);
                error_log("Controller class not found: " . $controllerClass);
            }
        } else {
            $this->handleError(404);
        }
    }

    private function handleError($code) {
        http_response_code($code);
        switch ($code) {
            case 404:
                echo "404 - Page not found.";
                break;
            case 500:
                echo "500 - Internal Server Error.";
                break;
            default:
                echo "An unexpected error occurred.";
                break;
        }
    }
}
