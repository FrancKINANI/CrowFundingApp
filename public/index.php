<?php
session_start();

require_once '../Config/autoload.php';

$action = $_GET['action'] ?? 'home';

$userModel = new User();
$projectModel = new Project();
$contributionModel = new Contribution();
$fileManager = new FileManager();

$router = new Router($action, $userModel, $projectModel, $contributionModel, $fileManager);
$router->route();
