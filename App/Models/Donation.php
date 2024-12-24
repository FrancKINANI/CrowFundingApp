<?php

namespace App\Models;

use App\Config\Database;
use PDO;

class Donation {
    private $id;
    private $amount;
    private $userId;
    private $projectId;

    public function __construct($id = null, $amount = 0, $userId = null, $projectId = null) {
        $this->id = $id;
        $this->amount = $amount;
        $this->userId = $userId;
        $this->projectId = $projectId;
    }

    public static function create($amount, $userId, $projectId) {
        $pdo = Database::getConnection();
        $sql = "INSERT INTO donations (amount, user_id, project_id) VALUES (:amount, :userId, :projectId)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'amount' => $amount,
            'userId' => $userId,
            'projectId' => $projectId
        ]);
        return $pdo->lastInsertId();
    }

    public static function getByProject($projectId) {
        $pdo = Database::getConnection();
        $sql = "SELECT * FROM donations WHERE project_id = :projectId";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['projectId' => $projectId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getTotalByProject($projectId) {
        $pdo = Database::getConnection();
        $sql = "SELECT SUM(amount) as total FROM donations WHERE project_id = :projectId";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['projectId' => $projectId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }
}
