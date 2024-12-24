<?php

namespace App\Core;

use App\Controllers\AuthController;
use App\Controllers\ProjectController;
use App\Controllers\HomeController;

class Router
{
    private $routes = [];

    public function __construct()
    {
        // Define the routes and map them to their respective controllers and methods
        $this->routes = [
            '/' => [HomeController::class, 'index'],
            '/register' => [AuthController::class, 'register'],
            '/login' => [AuthController::class, 'login'],
            '/logout' => [AuthController::class, 'logout'],
            '/projects' => [ProjectController::class, 'index'],
            '/projects/create' => [ProjectController::class, 'create'],
            '/projects/store' => [ProjectController::class, 'store'],
            '/projects/show' => [ProjectController::class, 'show'],
        ];
    }

    public function route()
    {
        // Get the requested URI and parse it
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Check if the requested URI matches a defined route
        if (isset($this->routes[$uri])) {
            [$controllerClass, $method] = $this->routes[$uri];

            // Instantiate the controller and call the method
            $controller = new $controllerClass();
            $controller->$method();
        } else {
            // If the route is not found, show a 404 error page
            http_response_code(404);
            echo "404 Not Found";
        }
    }
}
