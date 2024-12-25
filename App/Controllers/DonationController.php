<?php

require_once __DIR__ . '/../Models/Donation.php';
require_once __DIR__ . '/../Models/User.php';

class DonationController {
    private $donationModel;

    public function __construct($db) {
        $this->donationModel = new Donation($db);
    }
    public function createDonation() {
        require_once __DIR__ . '/../Views/donations/create.php';
    }
    public function addDonation(){
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (isset($_SESSION['user'])) {
                $user = $_SESSION['user'];
                $userId = $user['id'];
                $projectId = $_POST['projectId'];
                $amount = $_POST['amount'];
                $this->donationModel->addDonation($amount, $projectId, $userId);
                header('Location: /php/PHPCrowFundingApp/App/Views/user/dashboard.php');
            } else {
                echo "User not logged in.";
                return false;
            }
        }
    }

    public function getDonationsByProject($projectId){
        return $this->donationModel->getDonationsByProject($projectId);
    }

    public function getTotalDonations($projectId) {
        return $this->donationModel->getTotalDonations($projectId);
    }
}