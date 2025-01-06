<?php

require_once __DIR__ . '/../Models/Project.php';
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Models/Donation.php';


class ProjectController {
    private $projectModel;
    private $donationModel;
    private $userModel;

    public function __construct($db) {
        $this->projectModel = new Project($db);
        $this->donationModel = new Donation($db);
        $this->userModel = new User($db);
    }

    public function createProject(){
        if (!isset($_SESSION['user'])) {
            require __DIR__ . '/../Views/auth/login.php';
            exit;
        }
        require __DIR__ . '/../Views/projects/create.php';
    }

    public function create(){
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            $projectTitle = $_POST['title'];
            $projectDescription = $_POST['description'];
            $projectGoal = $_POST['goalAmount']; 
            $projectUser_id = $_SESSION['user']['id'];
            $this->projectModel->addProject($projectTitle, $projectDescription, $projectGoal, $projectUser_id);
            $projects = $this->projectModel->getAllProjects();
            require __DIR__ . '/../Views/projects/list.php';
        }
    }

    public function list() {
        $projects = $this->projectModel->getAllProjects();
        require __DIR__ . '/../Views/projects/list.php';
    }

    public function details() {
        if (isset($_GET['id'])) {
            $projectId = $_GET['id'];
            $project = $this->projectModel->getProjectById($projectId);
            $donations = $this->donationModel->getDonationsByProject($projectId);

            $donors = [];
            foreach ($donations as $donation) {
                $userId = $donation['user_id'];
                $user = $this->userModel->getUserById($userId);
                $donors[$donation['id']] = $user;
            }

            $totalDonations = $this->donationModel->getTotalDonations($projectId);
            $goalAmount = $project['goal_amount'];
            $percentageRemaining = 100 - (($totalDonations / $goalAmount) * 100);
            require_once __DIR__ . '/../Views/projects/details.php';
        } else {
            header('Location: /php/PHPCrowFundingApp/public/index.php?action=home');
            exit;
        }
    }

    public function edit() {
        if (isset($_GET['project_id'])) {
            $projectId = $_GET['project_id'];
            $project = $this->projectModel->getProjectById($projectId);
            if ($project) {
                require_once __DIR__ . '/../Views/projects/edit.php';
            } else {
                $error = "Project not found.";
                exit;
            }
        } else {
            header('Location: /php/PHPCrowFundingApp/public/index.php?action=dashboard');
            exit;
        }
    }

    public function updateProject() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (isset($_GET['project_id'])) {
                $projectId = $_GET['project_id'];
                $title = $_POST['title'];
                $description = $_POST['description'];
                $goalAmount = $_POST['goalAmount'];

                if (empty($title) || empty($description) || $goalAmount <= 0) {
                    $error = "All fields are required and the goal amount must be greater than zero.";
                    return false;
                }

                $this->projectModel->updateProject($projectId, $title, $description, $goalAmount);
                header('Location: /php/PHPCrowFundingApp/public/index.php?action=dashboard');
                exit;
            } else {
                $error = "Project Id not provided.";
                exit;
            }
        }
    }

    public function delete() {
        if (isset($_GET['project_id'])) {
            $projectId = $_GET['project_id'];
            $project = $this->projectModel->getProjectById($projectId);
            if ($project) {
                require_once __DIR__ . '/../Views/projects/delete.php';
            } else {
                $error = "Project not found.";
                exit;
            }
        } else {
            header('Location: /php/PHPCrowFundingApp/public/index.php?action=dashboard');
            exit;
        }
    }

    public function confirmDelete() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (isset($_GET['project_id'])) {
                $projectId = $_GET['project_id'];
                $this->projectModel->deleteProject($projectId);
                header('Location: /php/PHPCrowFundingApp/public/index.php?action=dashboard');
                exit;
            } else {
                $error = "Project Id not provided.";
                exit;
            }
        }
    }
}