<?php
require_once __DIR__ . '../Models/User.php';

class AuthController {
    private $userModel;
    public function __construct($userModel){
        $this->userModel = $userModel;
    }    
    public function login($email = "", $password = "") {
        $users = User::getAll();
        foreach ($users as $user) {
            if ($user['email'] === $email && password_verify($password, $user['password'])) {
                echo "Login successful! Welcome " . $user['name'];
                header("Location: ../.../public/index.php?action=userDashboard");
                return true;
            }
        }
        echo "Invalid credentials!";
        return false;
    }

    public function register($name = "", $email = "", $password = "") {
        $userController = new UserController($this->userModel);
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
