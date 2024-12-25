<?php
require_once __DIR__ . '/../Config/database.php';
require_once __DIR__ . '/../Config/autoload.php';

$db = Database::getInstance();

$action = $_GET['action'] ?? 'home';
$method = $_SERVER['REQUEST_METHOD'];

require_once __DIR__ . '/../App/Controllers/Router.php';

$router = new Router($db, $action, $method);

$router->handleRequest();
