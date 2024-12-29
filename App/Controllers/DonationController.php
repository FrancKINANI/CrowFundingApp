<?php

require_once __DIR__ . '/../Models/Donation.php';
require_once __DIR__ . '/../Models/Project.php';

class DonationController {
    private $donationModel;
    private $projectModel;

    public function __construct($db) {
        $this->donationModel = new Donation($db);
        $this->projectModel = new Project($db);
    }

    public function createDonation() {
        if (!isset($_SESSION['user'])) {
            require __DIR__ . '/../Views/auth/login.php';
            exit;
        }
        
        if (isset($_GET['project_id'])) {            
            $projectId = $_GET['project_id'];
            $totalDonations = $this->donationModel->getTotalDonations($projectId);
            $project = $this->projectModel->getProjectById($projectId);
            if($totalDonations >= $project['goal_amount']){
                $error = "The goal amount for this project has already been reached.";
                require __DIR__ . '/../Views/donation/create.php';
                return false;
            }
            if ($project) {
                require_once __DIR__ . '/../Views/donation/create.php';
            } else {
                $error = "Project not found.";
                exit;
            }
        } else {
            header('Location: /php/PHPCrowFundingApp/public/index.php?action=home');
            exit;
        }
    }

    public function submit() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (isset($_SESSION['user'])) {
                $user = $_SESSION['user'];
                $userId = $user['id'];
                if (isset($_GET['project_id'])) {
                    $projectId = $_GET['project_id'];
                    $amount = $_POST['amount'];

                    if ($amount <= 0) {
                        $error = "The donation amount must be greater than zero.";
                        return false;
                    }

                    $project = $this->projectModel->getProjectById($projectId);
                    $totalDonations = $this->donationModel->getTotalDonations($projectId);
                    if ($totalDonations + $amount > $project['goal_amount']) {
                        $error = "The donation amount exceeds the goal amount for this project.";
                        require_once __DIR__ . '/../Views/donation/create.php';
                        return false;
                    }

                    $this->donationModel->addDonation($amount, $projectId, $userId);
                    header('Location: /php/PHPCrowFundingApp/public/index.php?action=dashboard');
                    exit;
                } else {
                    $error = "Project Id not provided.";
                    return false;
                }
            } else {
                $error = "User not logged in.";
                return false;
            }
        } else {
            $error = "Invalid request method.";
            return false;
        }
    }

    public function edit(){
        $donationId = $_GET['id'];
        $donation = $this->donationModel->getDonationById($donationId);
        $projectId = $donation['project_id'];
        $totalDonations = $this->donationModel->getTotalDonations($projectId);
        $project = $this->projectModel->getProjectById($projectId);
        if ($donation) {
            require_once __DIR__ . '/../Views/donation/edit.php';
        } else {
            $error = "Donation not found.";
            require_once __DIR__ . '/../Views/user/dashboard.php';
            exit;
        }
    }
    public function editDonation() {
        if (!isset($_SESSION['user'])) {
            header('Location: /php/PHPCrowFundingApp/public/index.php?action=login');
            exit;
        }

        if (isset($_GET['id'])) {
            $donationId = $_GET['id'];
            $donation = $this->donationModel->getDonationById($donationId);
            $project = $this->projectModel->getProjectById($donation['project_id']);
            if ($donation) {
                require_once __DIR__ . '/../Views/donation/edit.php';
            } else {
                echo "Donation not found.";
                exit;
            }
        } else {
            header('Location: /php/PHPCrowFundingApp/public/index.php?action=home');
            exit;
        }
    }

    public function updateDonation() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (isset($_SESSION['user'])) {
                $user = $_SESSION['user'];
                $userId = $user['id'];
                if (isset($_GET['id'])) {
                    $donationId = $_GET['id'];
                    $amount = $_POST['amount'];
                    $donation = $this->donationModel->getDonationById($donationId);
                    $projectId = $donation['project_id'];

                    if ($amount <= 0) {
                        echo "The donation amount must be greater than zero.";
                        header('Location: ' . $_SERVER['HTTP_REFERER']);
                        return false;
                    }

                    $project = $this->projectModel->getProjectById($projectId);
                    $totalDonations = $this->donationModel->getTotalDonations($projectId);
                    $currentDonationAmount = $donation['amount'];
                    $newTotalDonations = $totalDonations - $currentDonationAmount + $amount;

                    if ($newTotalDonations > $project['goal_amount']) {
                        $error = "The donation amount exceeds the goal amount for this project.";
                        require_once __DIR__ . '/../Views/donation/edit.php';
                        return false;
                    }

                    $this->donationModel->update($donationId, $amount, $projectId, $userId);
                    header('Location: /php/PHPCrowFundingApp/public/index.php?action=dashboard');
                    exit;
                } else {
                    echo "Donation Id not provided.";
                    return false;
                }
            } else {
                echo "User not logged in.";
                return false;
            }
        }
    }

    public function deleteDonation() {
        if (!isset($_SESSION['user'])) {
            header('Location: /php/PHPCrowFundingApp/public/index.php?action=login');
            exit;
        }

        if (isset($_GET['id'])) {
            $donationId = $_GET['id'];
            $donation = $this->donationModel->getDonationById($donationId);
            $project = $this->projectModel->getProjectById($donation['project_id']);
            if ($donation) {
                require_once __DIR__ . '/../Views/donation/delete.php';
            } else {
                $error = "Donation not found.";
                exit;
            }
        } else {
            header('Location: /php/PHPCrowFundingApp/public/index.php?action=dashboard');
            exit;
        }
    }

    public function confirmDeleteDonation() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (isset($_GET['id'])) {
                $donationId = $_GET['id'];
                $this->donationModel->deleteDonation($donationId);
                header('Location: /php/PHPCrowFundingApp/public/index.php?action=dashboard');
                exit;
            } else {
                $error = "Donation Id not provided.";
                exit;
            }
        }
    }
}