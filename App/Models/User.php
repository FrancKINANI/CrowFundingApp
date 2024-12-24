<?php

namespace App\Models;

use App\Config\Database;
use PDO;

class User {
    private $id;
    private $name;
    private $email;
    private $password;

    public function __construct($id = null, $name = "", $email = "", $password = "") {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
    }

    public static function create($name, $email, $password) {
        $pdo = Database::getConnection();
        $sql = "INSERT INTO users (name, email, password) VALUES (:name, :email, :password)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'name' => $name,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_BCRYPT)
        ]);
        return $pdo->lastInsertId();
    }

    public static function getByEmail($email) {
        $pdo = Database::getConnection();
        $sql = "SELECT * FROM users WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function authenticate($email, $password) {
        $user = self::getByEmail($email);
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }
}
