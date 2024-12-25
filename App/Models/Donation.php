<?php

class Donation {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Ajouter une donation
    public function addDonation($amount, $projectId, $userId) {
        $query = "INSERT INTO donations (amount, project_id, user_id) VALUES (:amount, :project_id, :user_id)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':amount', $amount);
        $stmt->bindParam(':project_id', $projectId);
        $stmt->bindParam(':user_id', $userId);
        return $stmt->execute();
    }

    // Récupérer les donations pour un projet
    public function getDonationsByProject($projectId) {
        $query = "SELECT * FROM donations WHERE project_id = :project_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':project_id', $projectId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupérer le total des donations pour un projet
    public function getTotalDonations($projectId) {
        $query = "SELECT SUM(amount) as total FROM donations WHERE project_id = :project_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':project_id', $projectId);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
}
