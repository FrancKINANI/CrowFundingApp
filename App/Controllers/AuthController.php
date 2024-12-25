<?php
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Models/Project.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
class AuthController {
    private $userModel;

    public function __construct($db) {
        $this->userModel = new User($db);
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
                    header('Location: /php/PHPCrowFundingApp/App/Views/user/dashboard.php');
                    exit;
                } else {
                    echo "Incorrect email or password.";
                }
            }
        }
        
        require_once __DIR__ . '/../Views/auth/login.php';
    }
    

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'];
            $email = $_POST['email'];
            $password = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];

            if ($password !== $confirm_password) {
                $error = "The passwords do not match.";
            } else {
                if ($this->userModel->emailExists($email)) {
                    $error = "Email already exists.";
                } else {
                    $user = $this->userModel->addUser($name, $email, $password);
                    $_SESSION['user'] = $user;
                    header('Location: /php/PHPCrowFundingApp/App/Views/user/dashboard.php');
                    exit;
                }
            }
        }

        require_once __DIR__ . '/../Views/auth/register.php';
    }
}