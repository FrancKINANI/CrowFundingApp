<?php

require_once __DIR__ . '../Models/User.php';

class UserController {
    private $userModel;
    private $projectModel;
    private $contributionModel;

    public function __construct($userModel, $projectModel = null, $contributionModel = null) {
        $this->userModel = $userModel;
        $this->projectModel = $projectModel;
        $this->contributionModel = $contributionModel;
    }
    public function create($name = "", $email = "", $password = "") {
        $users = User::getAll();
        $id = count($users) + 1;

        $user = new User($id, $name, $email, $password);
        $user->save();
        echo "User created successfully!";
    }

    public function list() {
        $users = User::getAll();
        return $users;
    }

    public function delete($email = "") {
        $users = User::getAll();
        $updatedUsers = array_filter($users, function ($user) use ($email) {
            return $user['email'] !== $email;
        });

        file_put_contents(__DIR__ . '../Data/users.json', json_encode(array_values($updatedUsers), JSON_PRETTY_PRINT));
        echo "User deleted successfully!";
    }

    public function dashboard() {
        session_start();

        if (!isset($_SESSION['user'])) {
            header("Location: ../../public/index.php?action=login");
            exit;
        }

        $userId = $_SESSION['user']['id'];

        $userProjects = $this->projectModel->getByUserId($userId);

        $userContributions = $this->contributionModel->getByUserId($userId);

        require '../App/Views/user/dashboard.php';
    }

    public function edit($email, $newName = "", $newEmail = "", $newPassword = "") {
        $users = User::getAll();
        
        $userToEdit = null;
        foreach ($users as $user) {
            if ($user['email'] === $email) {
                $userToEdit = $user;
                break;
            }
        }
    
        if ($userToEdit === null) {
            echo "User  not found!";
            return;
        }
    
        if (!empty($newName)) {
            $userToEdit['name'] = $newName;
        }

        if (!empty($newEmail)) {
            $userToEdit['email'] = $newEmail;
        }
        if (!empty($newPassword)) {
            $userToEdit['password'] = $newPassword;
            file_put_contents(__DIR__ . '../Data/users.json', json_encode(array_values($users), JSON_PRETTY_PRINT));
            echo "User  updated successfully!";
        }
    }
}
