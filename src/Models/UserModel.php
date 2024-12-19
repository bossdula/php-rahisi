<?php

namespace App\Models;

use PDO;
use Exception;

class UserModel {
    private $conn;
    private $table = "users";

    public $regNo;
    public $email;
    public $mobile;
    public $password;

    public function __construct(PDO $db) {
        $this->conn = $db;
    }

    public function register(): bool {
        $query = "INSERT INTO {$this->table} (regno, email, mobile, password) 
                  VALUES (:regno, :email, :mobile, :password)";
        $stmt = $this->conn->prepare($query);

        $this->regNo = htmlspecialchars(strip_tags($this->regNo));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->mobile = htmlspecialchars(strip_tags($this->mobile));
        $this->password = password_hash($this->password, PASSWORD_BCRYPT);

        $stmt->bindParam(':regno', $this->regNo);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':mobile', $this->mobile);
        $stmt->bindParam(':password', $this->password);

        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Registration failed: " . $e->getMessage());
        }
    }
}
