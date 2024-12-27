<?php

require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Models/Project.php';
require_once __DIR__ . '/../Models/Donation.php';


class UserController {
    private $db;
    private $userModel;
    private $projectModel;
    private $donationModel;

    public function __construct($db) {
        $this->userModel = new User($db);
        $this->projectModel = new Project($db);
        $this->donationModel = new Donation($db);
    }

    public function createUser ($name, $email, $password) {
        return $this->userModel->addUser ($name, $email, $password);
    }
    public function list() {
        return $this->userModel->getAllUsers();
    }
    public function dashboard() {
        if (isset($_SESSION['user'])) {
            $user = $_SESSION['user'];
            $userId = $user['id'];

            $userProjects = $this->projectModel->getProjectsByUserId($userId);

            $userDonations = $this->donationModel->getDonationsByUserId($userId);

            $donationProjects = [];
            $totalInvested = 0;
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

            require_once __DIR__ . '/../Views/user/dashboard.php';
        } else {
            header('Location: /php/PHPCrowFundingApp/public/index.php?action=login');
            exit;
        }
    }
}