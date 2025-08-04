<?php

require_once __DIR__ . '/../Models/Project.php';
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Models/Donation.php';
require_once __DIR__ . '/../Models/Category.php';
require_once __DIR__ . '/../Middleware/SecurityMiddleware.php';
require_once __DIR__ . '/../Utils/Validator.php';
require_once __DIR__ . '/../Utils/Logger.php';
require_once __DIR__ . '/../Utils/FileUpload.php';

class ProjectController {
    private $projectModel;
    private $donationModel;
    private $userModel;
    private $categoryModel;
    private $fileUpload;

    public function __construct($db) {
        $this->projectModel = new Project($db);
        $this->donationModel = new Donation($db);
        $this->userModel = new User($db);
        $this->categoryModel = new Category($db);
        $this->fileUpload = new FileUpload('uploads/projects/');
    }

    public function createProject(){
        if (!isset($_SESSION['user'])) {
            require __DIR__ . '/../Views/auth/login.php';
            exit;
        }

        // Get categories for the form
        $categories = $this->categoryModel->getAllCategories();
        require __DIR__ . '/../Views/projects/create.php';
    }

    public function create(){
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            if (!isset($_SESSION['user'])) {
                header('Location: ' . app_url('public/index.php?action=login'));
                exit;
            }

            // CSRF validation
            if (!SecurityMiddleware::validateCSRF($_POST)) {
                $error = "Invalid security token. Please try again.";
                $categories = $this->categoryModel->getAllCategories();
                require __DIR__ . '/../Views/projects/create.php';
                return;
            }

            // Input validation
            $validator = Validator::make($_POST)
                ->required('title', 'Project title is required')
                ->min('title', 3, 'Title must be at least 3 characters')
                ->max('title', 255, 'Title must not exceed 255 characters')
                ->required('description', 'Project description is required')
                ->min('description', 50, 'Description must be at least 50 characters')
                ->required('goal_amount', 'Goal amount is required')
                ->numeric('goal_amount', 'Goal amount must be a number')
                ->positive('goal_amount', 'Goal amount must be positive')
                ->minValue('goal_amount', 1, 'Goal amount must be at least $1');

            if (!empty($_POST['end_date'])) {
                $validator->custom('end_date', function($date) {
                    return strtotime($date) > time();
                }, 'End date must be in the future');
            }

            if ($validator->fails()) {
                $errors = $validator->getAllErrors();
                $error = implode('<br>', $errors);
                $categories = $this->categoryModel->getAllCategories();
                require __DIR__ . '/../Views/projects/create.php';
                return;
            }

            // Handle file upload
            $featuredImage = null;
            if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
                $uploadResult = $this->fileUpload->uploadFile($_FILES['featured_image'], 'featured');
                if ($uploadResult) {
                    $featuredImage = $uploadResult['relative_path'];
                } else {
                    $error = "Error uploading image: " . implode(', ', $this->fileUpload->getErrors());
                    $categories = $this->categoryModel->getAllCategories();
                    require __DIR__ . '/../Views/projects/create.php';
                    return;
                }
            }

            // Prepare project data
            $projectData = [
                'title' => SecurityMiddleware::sanitizeInput($_POST['title']),
                'description' => SecurityMiddleware::sanitizeInput($_POST['description']),
                'short_description' => SecurityMiddleware::sanitizeInput($_POST['short_description'] ?? ''),
                'goal_amount' => (float)$_POST['goal_amount'],
                'user_id' => $_SESSION['user']['id'],
                'category_id' => !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null,
                'featured_image' => $featuredImage,
                'video_url' => SecurityMiddleware::sanitizeInput($_POST['video_url'] ?? ''),
                'end_date' => !empty($_POST['end_date']) ? $_POST['end_date'] : null,
                'min_donation' => !empty($_POST['min_donation']) ? (float)$_POST['min_donation'] : 1.00,
                'status' => 'active'
            ];

            // Process tags
            $tags = [];
            if (!empty($_POST['tags'])) {
                $tags = array_map('trim', explode(',', $_POST['tags']));
                $tags = array_filter($tags); // Remove empty tags
            }
            $projectData['tags'] = $tags;

            // Create project
            $projectId = $this->projectModel->addProject($projectData);
            if ($projectId) {
                Logger::logUserActivity('project_created', $_SESSION['user']['id'], ['project_id' => $projectId]);
                header('Location: ' . app_url('public/index.php?action=projectDetails&id=' . $projectId));
                exit;
            } else {
                $error = "Failed to create project. Please try again.";
                $categories = $this->categoryModel->getAllCategories();
                require __DIR__ . '/../Views/projects/create.php';
            }
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