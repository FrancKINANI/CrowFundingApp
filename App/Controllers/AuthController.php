<?php
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Models/Project.php';
require_once __DIR__ . '/../Models/Donation.php';
require_once __DIR__ . '/../Middleware/SecurityMiddleware.php';
require_once __DIR__ . '/../Utils/Validator.php';
require_once __DIR__ . '/../Utils/Logger.php';

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
            // Rate limiting check
            if (!SecurityMiddleware::checkRateLimit('login', 5, 300)) {
                $error = "Too many login attempts. Please try again in 5 minutes.";
                SecurityMiddleware::logSecurityEvent('login_rate_limit_exceeded', ['ip' => $_SERVER['REMOTE_ADDR']]);
                require_once __DIR__ . '/../Views/auth/login.php';
                return;
            }

            // CSRF validation
            if (!SecurityMiddleware::validateCSRF($_POST)) {
                $error = "Invalid security token. Please try again.";
                SecurityMiddleware::logSecurityEvent('csrf_validation_failed', ['action' => 'login']);
                require_once __DIR__ . '/../Views/auth/login.php';
                return;
            }

            // Input validation
            $validator = Validator::make($_POST)
                ->required('email', 'Email is required')
                ->email('email', 'Please enter a valid email address')
                ->required('password', 'Password is required');

            if ($validator->fails()) {
                $errors = $validator->getAllErrors();
                $error = implode('<br>', $errors);
                require_once __DIR__ . '/../Views/auth/login.php';
                return;
            }

            $email = SecurityMiddleware::sanitizeInput($_POST['email']);
            $password = $_POST['password']; // Don't sanitize password

            $user = $this->userModel->getUserByEmail($email);
            if ($user && password_verify($password, $user['password'])) {
                // Regenerate session ID for security
                session_regenerate_id(true);

                $_SESSION['user'] = $user;
                $_SESSION['last_activity'] = time();

                Logger::logUserActivity('login', $user['id']);

                // Redirect to dashboard
                header('Location: ' . app_url('public/index.php?action=dashboard'));
                exit;
            } else {
                $error = "Invalid email or password.";
                Logger::warning('Failed login attempt', ['email' => $email, 'ip' => $_SERVER['REMOTE_ADDR']]);
            }
        }

        require_once __DIR__ . '/../Views/auth/login.php';
    }
    

    public function register() {
        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Rate limiting check
            if (!SecurityMiddleware::checkRateLimit('register', 3, 300)) {
                $error = "Too many registration attempts. Please try again in 5 minutes.";
                SecurityMiddleware::logSecurityEvent('register_rate_limit_exceeded', ['ip' => $_SERVER['REMOTE_ADDR']]);
                require_once __DIR__ . '/../Views/auth/register.php';
                return;
            }

            // CSRF validation
            if (!SecurityMiddleware::validateCSRF($_POST)) {
                $error = "Invalid security token. Please try again.";
                SecurityMiddleware::logSecurityEvent('csrf_validation_failed', ['action' => 'register']);
                require_once __DIR__ . '/../Views/auth/register.php';
                return;
            }

            // Input validation
            $validator = Validator::make($_POST)
                ->required('name', 'Name is required')
                ->min('name', 2, 'Name must be at least 2 characters')
                ->max('name', 100, 'Name must not exceed 100 characters')
                ->required('email', 'Email is required')
                ->email('email', 'Please enter a valid email address')
                ->required('password', 'Password is required')
                ->min('password', 8, 'Password must be at least 8 characters')
                ->required('password_confirmation', 'Password confirmation is required')
                ->matches('password_confirmation', 'password', 'Password confirmation must match password');

            if ($validator->fails()) {
                $errors = $validator->getAllErrors();
                $error = implode('<br>', $errors);
                require_once __DIR__ . '/../Views/auth/register.php';
                return;
            }

            // Additional password strength validation
            $passwordValidation = SecurityMiddleware::validatePassword($_POST['password']);
            if (!$passwordValidation['valid']) {
                $error = implode('<br>', $passwordValidation['errors']);
                require_once __DIR__ . '/../Views/auth/register.php';
                return;
            }

            $name = SecurityMiddleware::sanitizeInput($_POST['name']);
            $email = SecurityMiddleware::sanitizeInput($_POST['email']);
            $password = $_POST['password']; // Don't sanitize password

            // Check if email already exists
            if ($this->userModel->emailExists($email)) {
                $error = "An account with this email address already exists.";
                Logger::warning('Registration attempt with existing email', ['email' => $email, 'ip' => $_SERVER['REMOTE_ADDR']]);
                require_once __DIR__ . '/../Views/auth/register.php';
                return;
            }

            // Create user
            $user = $this->userModel->addUser($name, $email, $password);
            if ($user) {
                // Regenerate session ID for security
                session_regenerate_id(true);

                $_SESSION['user'] = $user;
                $_SESSION['last_activity'] = time();

                Logger::logUserActivity('register', $user['id']);

                // Redirect to dashboard
                header('Location: ' . app_url('public/index.php?action=dashboard'));
                exit;
            } else {
                $error = "An error occurred while creating your account. Please try again.";
                Logger::error('User registration failed', ['email' => $email]);
            }
        }
        require_once __DIR__ . '/../Views/auth/register.php';
    }

    public function logout() {
        if (isset($_SESSION['user'])) {
            Logger::logUserActivity('logout', $_SESSION['user']['id']);
        }

        // Clear session data
        $_SESSION = [];

        // Delete session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        // Destroy session
        session_destroy();

        // Redirect to login
        header('Location: ' . app_url('public/index.php?action=login'));
        exit;
    }
}