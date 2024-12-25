<?php

require_once __DIR__ . '/../Config/database.php';
require_once __DIR__ . '/../Config/autoload.php';

$db = Database::getInstance();

require_once __DIR__ . '/../App/Controllers/Router.php';

$router = new Router($db);

$router->handleRequest();
