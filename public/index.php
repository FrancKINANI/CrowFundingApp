<?php
session_start();

require_once __DIR__ . '/../Config/database.php';
require_once __DIR__ . '/../App/Controllers/Router.php';
require_once __DIR__ . '/../Config/autoload.php';

use App\Core\Router;

$router = new Router();
$router->route();