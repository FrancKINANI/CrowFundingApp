<?php
require_once __DIR__ . '../Models/User.php';

class AuthController {
    public function login($email = "", $password = "") {
        $users = User::getAll();
        foreach ($users as $user) {
            if ($user['email'] === $email && password_verify($password, $user['password'])) {
                echo "Login successful! Welcome " . $user['name'];
                return true;
            }
        }
        echo "Invalid credentials!";
        return false;
    }

    public function register($name = "", $email = "", $password = "") {
        $userController = new UserController();
        $userController->create($name, $email, $password);
    }

    public function logout() {
        session_start();
        session_unset();
        session_destroy();

        header("Location: ../../public/index.php?action=home");
        exit;
    }
}
