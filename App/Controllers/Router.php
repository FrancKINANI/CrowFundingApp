<?php

if(!isset($_SESSION)) { 
    session_start(); 
}

require_once __DIR__ . '/../../App/Controllers/HomeController.php';
require_once __DIR__ . '/../../App/Controllers/ProjectController.php';
require_once __DIR__ . '/../../App/Controllers/AuthController.php';
require_once __DIR__ . '/../../App/Controllers/UserController.php';
require_once __DIR__ . '/../../App/Controllers/DonationController.php';



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
                'dashboard' => [UserController::class, 'dashboard'],
                'donate' => [DonationController::class, 'createDonation'],
                'projectDetails' => [ProjectController::class, 'details'],
                'logout' => [AuthController::class, 'logout'],
                'list' => [ProjectController::class, 'list'],
                'editProject' => [ProjectController::class, 'edit'],
                'editDonation' => [DonationController::class, 'edit'],
                'deleteProject' => [ProjectController::class, 'delete'],
                'deleteDonation' => [DonationController::class, 'deleteDonation'],
                'detailsProject' => [ProjectController::class, 'details'],
            ],
            'POST' => [
                'login' => [AuthController::class, 'login'],
                'register' => [AuthController::class, 'register'],
                'create' => [ProjectController::class, 'create'],
                'submitDonation' => [DonationController::class, 'submit'],
                'updateProject' => [ProjectController::class, 'updateProject'],
                'updateDonation' => [DonationController::class, 'updateDonation'],
                'confirmDeleteProject' => [ProjectController::class, 'confirmDelete'],
                'confirmDeleteDonation' => [DonationController::class, 'confirmDeleteDonation'],
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
        switch ($code) {
            case 404:
                $error = "404 - Page not found.";
                break;
            case 500:
                $error = "500 - Internal Server Error.";
                break;
            default:
                $error = "An unexpected unknown error occurred.";
                break;
        }
    }
}
