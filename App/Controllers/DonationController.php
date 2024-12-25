<?php

require_once __DIR__ . '/../Models/Donation.php';

class DonationController {
    private $donationModel;

    public function __construct($db) {
        $this->donationModel = new Donation($db);
    }

    // Ajouter un don
    public function addDonation($amount, $projectId, $userId) {
        if (empty($userId)) {
            echo "User  ID is required.";
            return false;
        }
        if ($amount <= 0) {
            echo "The donation amount must be greater than zero.";
            return false;
        }
    
        return $this->donationModel->addDonation($amount, $projectId, $userId);
    }

    // Récupérer les dons pour un projet
    public function getDonationsByProject($projectId) {
        return $this->donationModel->getDonationsByProject($projectId);
    }

    // Récupérer le total des dons pour un projet
    public function getTotalDonations($projectId) {
        return $this->donationModel->getTotalDonations($projectId);
    }
}