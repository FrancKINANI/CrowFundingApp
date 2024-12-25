<?php

class AuthController {
    private $userModel;

    public function __construct($db) {
        $this->userModel = new User($db);
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'];
            $password = $_POST['password'];

            // Check the credentials
            $user = $this->userModel->getUserByEmail($email);

            if ($user && password_verify($password, $user['password'])) {
                // login successful
                $_SESSION['user'] = $user;
                header('Location: /');
                exit;
            } else {
                // login failure
                $error = "Email ou mot de passe incorrect.";
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
                // password hash
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

                // register the user
                $this->userModel->addUser($name, $email, $hashedPassword);
                header('Location: /auth/login.php');
                exit;
            }
        }

        require_once __DIR__ . '/../Views/auth/register.php';
    }
}
