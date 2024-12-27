<?php

class Donation {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function addDonation($amount, $projectId, $userId) {
        $query = "INSERT INTO donations (amount, project_id, user_id) VALUES (:amount, :project_id, :user_id)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':amount', $amount);
        $stmt->bindParam(':project_id', $projectId);
        $stmt->bindParam(':user_id', $userId);
        return $stmt->execute();
    }

    public function getDonationsByProject($projectId) {
        $query = "SELECT * FROM donations WHERE project_id = :project_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':project_id', $projectId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?? [];
    }

    public function getTotalDonations($projectId) {
        $stmt = $this->db->prepare('SELECT SUM(amount) as total FROM donations WHERE project_id = :project_id');
        $stmt->bindParam(':project_id', $projectId);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    }

    public function getDonationsByUserId($userId) {
        $stmt = $this->db->prepare('SELECT * FROM donations WHERE user_id = :user_id');
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?? [];
    }

    public function getDonationById($donationId) {
        $stmt = $this->db->prepare('SELECT * FROM donations WHERE id = :id');
        $stmt->bindParam(':id', $donationId);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($donationId, $amount, $projectId, $userId) {
        $stmt = $this->db->prepare('UPDATE donations SET amount = :amount, project_id = :project_id, user_id = :user_id WHERE id = :id');
        $stmt->bindParam(':amount', $amount);
        $stmt->bindParam(':project_id', $projectId);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':id', $donationId);
        return $stmt->execute();
    }

    public function deleteDonation($donationId) {
        $stmt = $this->db->prepare('DELETE FROM donations WHERE id = :id');
        $stmt->bindParam(':id', $donationId);
        return $stmt->execute();
    }
}
