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
            require __DIR__ . '/../Views/donation/create.php';
        }
        
        if (isset($_GET['project_id'])) {            
            $projectId = $_GET['project_id'];
            $totalDonations = $this->donationModel->getTotalDonations($projectId);
            $project = $this->projectModel->getProjectById($projectId);
            if($totalDonations >= $project['goal_amount']){
                $error = "The goal amount for this project has already been reached.";
                header('Location: ' . $_SERVER['HTTP_REFERER']);
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
                        header('Location: ' . $_SERVER['HTTP_REFERER']);
                        return false;
                    }

                    $project = $this->projectModel->getProjectById($projectId);
                    $totalDonations = $this->donationModel->getTotalDonations($projectId);
                    if ($totalDonations >= $project['goal_amount']) {
                        $error =  "The goal amount for this project has already been reached.";
                        header('Location: ' . $_SERVER['HTTP_REFERER']);
                        return false;
                    }

                    $this->donationModel->addDonation($amount, $projectId, $userId);
                    header('Location: /php/PHPCrowFundingApp/public/index.php?action=dashboard');
                    exit;
                } else {
                    $error = "Project ID not provided.";
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
        $project = $this->projectModel->getProjectById($donation['project_id']);
        if ($donation) {
            require_once __DIR__ . '/../Views/donation/edit.php';
        } else {
            $error = "Donation not found.";
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
                $error = "Donation not found.";
                exit;
            }
        } else {
            header('Location: /php/PHPCrowFundingApp/public/index.php?action=home');
            exit;
        }
    }

    public function updateDonation() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $donationId = $_GET['id'];
            $amount = $_POST['amount'];
            $donation = $this->donationModel->getDonationById($donationId);
            $project = $this->projectModel->getProjectById($donation['project_id']);
            $userId = $_SESSION['user']['id'];

            if ($amount <= 0) {
                $error = "The donation amount must be greater than zero.";
                return false;
            }

            $this->donationModel->update($donationId, $amount, $project['id'], $userId);
            header('Location: /php/PHPCrowFundingApp/public/index.php?action=dashboard');
            exit;
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
                $error = "Donation ID not provided.";
                exit;
            }
        }
    }
}