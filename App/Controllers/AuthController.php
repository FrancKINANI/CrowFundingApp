<?php
// require_once '../Models/User.php';
class AuthController {
    private $userModel;
    public function __construct($userModel){
        $this->userModel = $userModel;
    }    
    public function login($email = "", $password = "") {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    
        $user = User::getByEmail($email);
    
        if ($user !== null) {
            if (password_verify($password, $user->getPassword())) {
                $_SESSION['user'] = [
                    'id' => $user->getId(),
                    'name' => $user->getName(),
                    'email' => $user->getEmail()
                ];
    
                header("Location: /../../../public/index.php?action=userDashboard");
                exit;
            } else {
                echo "Invalid credentials! (Incorrect password)";
            }
        } else {
            echo "Invalid credentials! (User not found)";
        }
    
        return false;
    }
    
    public function register($name = "", $email = "", $password = "") {
        $userController = new UserController($this->userModel);
        $userController->create($name, $email, $password);
    }

    public function logout() {
        if(session_status() === PHP_SESSION_NONE){
            session_start();
        }
        session_unset();
        session_destroy();

        header("Location: ../../public/index.php?action=home");
        exit;
    }
}
