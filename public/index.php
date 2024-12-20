<?php
session_start();

require_once '../Config/autoload.php';

// Instantiate required models
$userModel = new User();
$projectModel = new Project();
$contributionModel = new Contribution();
$fileManager = new FileManager();

class Router {
    private $action;
    private $userModel;
    private $projectModel;
    private $contributionModel;
    private $fileManager;

    const ACTIONS = [
        'home' => 'home',
        'login' => 'login',
        'register' => 'register',
        'logout' => 'logout',
        'userDashboard' => 'userDashboard',
        'createProject' => 'createProject',
        'editProject' => 'editProject',
        'deleteProject' => 'deleteProject',
        'projectDetails' => 'projectDetails',
        'createContribution' => 'createContribution',
        'editContribution' => 'editContribution',
        'deleteContribution' => 'deleteContribution',
        'listUsers' => 'listUsers',
        'createUser ' => 'createUser ',
        'editUser ' => 'editUser ',
        'deleteUser ' => 'deleteUser ',
    ];

    public function __construct($action, $userModel, $projectModel, $contributionModel, $fileManager) {
        $this->action = $action;
        $this->userModel = $userModel;
        $this->projectModel = $projectModel;
        $this->contributionModel = $contributionModel;
        $this->fileManager = $fileManager;
    }

    public function route() {
        try {
            if (array_key_exists($this->action, self::ACTIONS)) {
                $method = self::ACTIONS[$this->action];
                $this->$method();
            } else {
                throw new Exception("Unrecognized action: {$this->action}");
            }
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    private function home() {
        require '../App/views/home.php';
    }

    private function login() {
        $controller = new AuthController();
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $email = $_GET['email'] ?? null;
            $password = $_GET['password'] ?? null;
            if ($email && $password) {
                $controller->login($email, $password);
            } else {
                throw new Exception("Email and password are required.");
            }
        } else {
            $controller->login(); // Show login
        }
    }

    private function register() {
        $controller = new AuthController();
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $name = $_GET['name'] ?? null;
            $email = $_GET['email'] ?? null;
            $password = $_GET['password'] ?? null;
            if ($name && $email && $password) {
                $controller->register($name, $email, $password);
            } else {
                throw new Exception("All fields are required to register.");
            }
        } else {
            $controller->register(); // Show registration form
        }
    }

    private function logout() {
        $controller = new AuthController();
        $controller->logout();
    }

    private function userDashboard() {
        $controller = new UserController($this->userModel, $this->projectModel, $this->contributionModel);
        $controller->dashboard();
    }

    private function createProject() {
        $controller = new ProjectController($this->projectModel, $this->fileManager);
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $title = $_GET['title'] ?? null;
            $description = $_GET['description'] ?? null;
            $goalAmount = $_GET['goalAmount'] ?? null;
            if ($title && $description && $goalAmount) {
                $controller->create($title, $description, $goalAmount);
            } else {
                throw new Exception("All fields are required to create a project.");
            }
        } else {
            $controller->create();
        }
    }

    private function editProject() {
        $controller = new ProjectController($this->projectModel, $this->fileManager);
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $id = $_GET['id'] ?? null;
            $title = $_GET['title'] ?? null;
            $description = $_GET['description'] ?? null;
            $goalAmount = $_GET['goalAmount'] ?? null;
            if ($id && $title && $description && $goalAmount) {
                $controller->edit($id, $title, $description, $goalAmount);
            } else {
                throw new Exception("All fields are required to edit a project.");
            }
        } else {
            $controller->edit($_GET['id'] ?? null);
        }
    }

    private function deleteProject() {
        $controller = new ProjectController($this->projectModel, $this->fileManager);
        $id = $_GET['id'] ?? null;
        if ($id) {
            $controller->delete($id);
        } else {
            throw new Exception("Project ID is required to delete.");
        }
    }

    private function projectDetails() {
        $controller = new ProjectController($this->projectModel, $this->fileManager);
        $id = $_GET['id'] ?? null;
        if ($id) {
            $controller->details($id);
        } else {
            throw new Exception("Project ID is required to view details.");
        }
    }

    private function createContribution() {
        $controller = new ContributionController($this->contributionModel, $this->projectModel);
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $projectId = $_GET['projectId'] ?? null;
            $userId = $_SESSION['user']['id'] ?? null;
            $amount = $_GET['amount'] ?? null;
            if ($projectId && $userId && $amount) {
                $controller->create($projectId, $userId, $amount);
            } else {
                throw new Exception("All fields are required to contribute.");
            }
        } else {
            throw new Exception("Invalid request.");
        }
    }

    private function editContribution() {
        $controller = new ContributionController($this->contributionModel, $this->projectModel);
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $id = $_GET['id'] ?? null;
            $amount = $_GET['amount'] ?? null;
            if ($id && $amount) {
                $controller->edit($id, $amount);
            } else {
                throw new Exception("ID and amount are required to edit.");
            }
        } else {
            throw new Exception("Invalid request.");
        }
    }

    private function deleteContribution() {
        $controller = new ContributionController($this->contributionModel, $this->projectModel);
        $id = $_GET['id'] ?? null;
        if ($id) {
            $controller->delete($id);
        } else {
            throw new Exception("Contribution ID is required to delete.");
        }
    }

    private function listUsers() {
        $controller = new UserController($this->userModel);
        $controller->list();
    }

    private function createUser () {
        $controller = new UserController($this->userModel);
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $name = $_GET['name'] ?? null;
            $email = $_GET['email'] ?? null;
            $password = $_GET['password'] ?? null;
            if ($name && $email && $password) {
                $controller->create($name, $email, $password);
            } else {
                throw new Exception("All fields are required to create a user.");
            }
        } else {
            $controller->create();
        }
    }

    private function editUser () {
        $controller = new UserController($this->userModel);
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $id = $_GET['id'] ?? null;
            $name = $_GET['name'] ?? null;
            $email = $_GET['email'] ?? null;
            $password = $_GET['password'] ?? null;
            if ($id && $name && $email && $password) {
                $controller->edit($id, $name, $email, $password);
            } else {
                throw new Exception("All fields are required to edit a user.");
            }
        } else {
            $controller->edit($_GET['id'] ?? null);
        }
    }

    private function deleteUser () {
        $controller = new UserController($this->userModel);
        $email = $_GET['email'] ?? null;
            if ($email) {
                $controller->delete($email);
            } else {
                throw new Exception("Email is required to delete a user.");
            }
    }
}