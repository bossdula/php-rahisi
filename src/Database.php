<?php

namespace App;

use PDO;
use PDOException;
use Exception;

class Database {
    private $host = "localhost";
    private $dbname = "angular";
    private $username = "root";
    private $password = "";
    private $conn;

    public function connect(): PDO {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host={$this->host};dbname={$this->dbname}", $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new Exception("Database connection error: " . $e->getMessage());
        }
        return $this->conn;
    }
}
