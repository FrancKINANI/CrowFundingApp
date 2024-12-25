<?php
if(!isset($_SESSION)) { 
    session_start(); 
}

require_once __DIR__ . '/../Models/Project.php';
require_once __DIR__ . '/../Models/User.php';

class ProjectController {
    private $projectModel;

    public function __construct($db) {
        $this->projectModel = new Project($db);
    }

    public function createProject(){
        require_once __DIR__ . '/../Views/projects/create.php';
    }
    
    // Create a new project
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (isset($_SESSION['user'])) {
                $user = $_SESSION['user'];
                $userId = $user['id'];

                $title = $_POST['title'];
                $description = $_POST['description'];
                $goalAmount = $_POST['goalAmount'];

                if (empty($title) || empty($description) || $goalAmount <= 0) {
                    echo "All fields are required and the goal amount must be greater than zero.";
                    return false;
                }

                $this->projectModel->addProject($title, $description, $goalAmount, $userId);
                require_once __DIR__ . '/../Views/user/dashboard.php';
            } else {
                echo "User not logged in.";
                return false;
            }
        }
        require_once __DIR__ . '../Views/projects/create.php';
    }

    // List all projects
    public function list() {
        $projects = $this->projectModel->getAllProjects();
        require '../Views/projects/index.php'; // Display the view with the list of projects
    }

    // Show project details
    public function details() {
        $projectId = $_GET['projectId'];
        $project = $this->projectModel->getProjectById($projectId);
        require_once __DIR__ . '/../Views/projects/details.php';
    }

    // Edit a project
    public function edit($projectId, $title, $description, $goalAmount) {
        $project = $this->projectModel->getProjectById($projectId);
        if ($project) {
            if (!empty($title)) {
                $project['title'] = $title;
            }
            if (!empty($description)) {
                $project['description'] = $description;
            }
            if ($goalAmount > 0) {
                $project['goal_amount'] = $goalAmount;
            }
    
            $this->projectModel->updateProject($projectId, $project['title'], $project['description'], $project['goal_amount']);
            echo "Project updated successfully!";
        } else {
            echo "Project not found.";
        }
    }

    // Delete a project
    public function delete($projectId) {
        $project = $this->projectModel->getProjectById($projectId);
        if ($project) {
            $this->projectModel->deleteProject($projectId);
            echo "Project deleted successfully!";
        } else {
            echo "Project not found.";
        }
    }

    public function dashboard() {
        if (isset($_SESSION['user'])) {
            $user = $_SESSION['user'];
            $userId = $user['id'];

            $userProjects = $this->projectModel->getProjectsByUserId($userId);

            require_once __DIR__ . '/../Views/user/dashboard.php';
        } else {
            header('Location: /php/PHPCrowFundingApp/public/index.php?action=login');
            exit;
        }
    }
}