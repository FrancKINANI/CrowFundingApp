<?php
session_start();

require_once '../Config/autoload.php';

$userModel = new User();
$projectModel = new Project();
$contributionModel = new Contribution();
$fileManager = new FileManager();

$action = isset($_GET['action']) ? $_GET['action'] : 'home';

try {
    switch ($action) {
        case 'home':
            require '../views/home.php';
            break;

        case 'login':
            require '../Controllers/AuthController.php';
            $controller = new AuthController();
            $controller->login();
            break;

        case 'register':
            require '../controllers/AuthController.php';
            $controller = new AuthController();
            $controller->register();
            break;

        case 'logout':
            require '../controllers/AuthController.php';
            $controller = new AuthController();
            $controller->logout();
            break;

        case 'userDashboard':
            require '../controllers/UserController.php';
            $controller = new UserController($userModel, $projectModel, $contributionModel);
            $controller->dashboard();
            break;

        case 'createProject':
        case 'editProject':
        case 'deleteProject':
        case 'projectDetails':
            require '../controllers/ProjectController.php';
            $controller = new ProjectController($projectModel, $fileManager);
            if ($action === 'createProject') $controller->create();
            if ($action === 'editProject') $controller->edit();
            if ($action === 'deleteProject') $controller->delete();
            if ($action === 'projectDetails') $controller->details();
            break;

        case 'createContribution':
        case 'editContribution':
        case 'deleteContribution':
            require '../controllers/ContributionController.php';
            $controller = new ContributionController($contributionModel, $projectModel);
            if ($action === 'createContribution') $controller->create();
            if ($action === 'editContribution') $controller->edit();
            if ($action === 'deleteContribution') $controller->delete();
            break;

        case 'listUsers':
        case 'createUser':
        case 'editUser':
        case 'deleteUser':
            require '../controllers/UserController.php';
            $controller = new UserController($userModel);
            if ($action === 'listUsers') $controller->list();
            if ($action === 'createUser') $controller->create();
            if ($action === 'editUser') $controller->edit();
            if ($action === 'deleteUser') $controller->delete();
            break;

        default:
            throw new Exception("Page not found");
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
