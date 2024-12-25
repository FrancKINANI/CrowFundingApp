<?php

require_once __DIR__ . '/../Models/User.php';

class UserController {
    private $db;
    private $userModel;
    private $projectModel;
    private $donationModel;

    public function __construct($db) {
        $this->userModel = new User($db);
    }

    public function createUser ($name, $email, $password) {
        return $this->userModel->addUser ($name, $email, $password);
    }
    public function list() {
        return $this->userModel->getAllUsers();
    }

    public function delete($email) {
        return $this->userModel->deleteUserByEmail($email);
    }

    public function deleteUserByEmail($email) {
        $query = "DELETE FROM users WHERE email = :email";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':email', $email);
        return $stmt->execute();
    }

    public function dashboard() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user'])) {
            header("Location: /auth/login.php");
            exit;
        }

        $userId = $_SESSION['user']['id'];
        $userProjects = $this->projectModel->getByUserId($userId);
        $userContributions = $this->donationModel->getByUserId($userId);

        require '../Views/user/dashboard.php';
    }

    public function edit($email, $newName = "", $newEmail = "", $newPassword = "") {
        $user = $this->userModel->getUserByEmail($email);
        
        if ($user === null) {
            echo "User not found!";
            return;
        }

        if (!empty($newName)) {
            $user['name'] = $newName;
        }

        if (!empty($newEmail)) {
            $user['email'] = $newEmail;
        }

        if (!empty($newPassword)) {
            $user['password'] = password_hash($newPassword, PASSWORD_BCRYPT);
        }

        // Vous devez implémenter une méthode de mise à jour dans le modèle User
        // Exemple : $this->userModel->updateUser ($user);
        echo "User updated successfully!";
    }
}