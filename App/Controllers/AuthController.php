<?php
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Models/Project.php';
require_once __DIR__ . '/../Models/Donation.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class AuthController {
    private $userModel;
    private $projectModel;
    private $donationModel;

    public function __construct($db) {
        $this->userModel = new User($db);
        $this->projectModel = new Project($db);
        $this->donationModel = new Donation($db);
    }

    public function showLoginForm() {
        require_once __DIR__ . '/../Views/auth/login.php';
    }

    public function showRegisterForm() {
        require_once __DIR__ . '/../Views/auth/register.php';
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email']);
            $password = $_POST['password'];
    
            if (empty($email) || empty($password)) {
                echo "Email and password are required.";
            } else {
                $user = $this->userModel->getUserByEmail($email);
    
                if ($user && password_verify($password, $user['password'])) {
                    $_SESSION['user'] = $user;
                    $userId = $user['id'];
                    if($userId){
                        $userProjects = $this->projectModel->getProjectsByUserId($userId);
                        $userDonations =  $this->donationModel->getDonationsByUserId($userId);
                        $donationProjects = [];
                        $totalInvested = 0;
                        foreach ($userDonations as $donation) {
                            $projectId = $donation['project_id'];
                            $project = $this->projectModel->getProjectById($projectId);
                            $totalDonations = $this->donationModel->getTotalDonations($projectId);
                            $goalAmount = $project['goal_amount'];
                            $percentageRemaining = 100 - (($totalDonations / $goalAmount) * 100);
                            $project['total_donations'] = $totalDonations;
                            $project['percentage_remaining'] = $percentageRemaining;
                            $donationProjects[$projectId] = $project;
                            $totalInvested += $donation['amount'];
                        }
                        require_once __DIR__ . '/../Views/user/dashboard.php';
                        exit;
                    }else{
                            echo "User not found.";
                    }
                } else {
                    echo "Incorrect email or password.";
                }
            }
        }
        
        require_once __DIR__ . '/../Views/auth/login.php';
    }
    

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = trim($_POST['name']);
            $email = trim($_POST['email']);
            $password = $_POST['password'];
            $confirmPassword = $_POST['password_confirmation'];

            if ($password !== $confirmPassword) {
                $error = "The passwords do not match.";
            } elseif (empty($name) || empty($email) || empty($password)) {
                $error = "All fields are required.";
            } else {
                if ($this->userModel->emailExists($email)) {
                    $error = "Email already exists.";
                } else {
                    $user = $this->userModel->addUser($name, $email, $password);
                    if ($user) {
                        $_SESSION['user'] = $user;
                        $userId = $user['id'];
                        if ($userId) {
                            $userProjects = $this->projectModel->getProjectsByUserId($userId);
                            $userDonations = $this->donationModel->getDonationsByUserId($userId);
                            $donationProjects = [];
                            $totalInvested = 0;
                            if (is_array($userDonations)) {
                                foreach ($userDonations as $donation) {
                                    $projectId = $donation['project_id'];
                                    $project = $this->projectModel->getProjectById($projectId);
                                    $totalDonations = $this->donationModel->getTotalDonations($projectId);
                                    $goalAmount = $project['goal_amount'];
                                    $percentageRemaining = 100 - (($totalDonations / $goalAmount) * 100);
                                    $project['total_donations'] = $totalDonations;
                                    $project['percentage_remaining'] = floor($percentageRemaining);
                                    $donationProjects[$projectId] = $project;
                                    $totalInvested += $donation['amount'];
                                }
                            }
                            header('Location: /php/PHPCrowFundingApp/public/index.php?action=dashboard');
                            exit;
                        } else {
                            echo "User not found.";
                        }
                    } else {
                        $error = "An error occurred.";
                    }
                }
            }
        }
        require_once __DIR__ . '/../Views/auth/register.php';
    }

    public function logout() {
        if (!isset($_SESSION)) {
            session_start();
        }
        session_destroy();
        header('Location: /php/PHPCrowFundingApp/public/index.php?action=login');
        exit;
    }
}