<?php
// Headers for CORS and JSON responses
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// --------------------
// Database Class
// --------------------
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

// --------------------
// User Class
// --------------------
class User {
    private $conn;
    private $table = "users";

    // User properties
    public $regNo;
    public $email;
    public $mobile;
    public $password;

    // Constructor with database connection
    public function __construct(PDO $db) {
        $this->conn = $db;
    }

    // Register a new user
    public function register(): bool {
        $query = "INSERT INTO {$this->table} (regno, email, mobile, password) 
                  VALUES (:regno, :email, :mobile, :password)";
        $stmt = $this->conn->prepare($query);

        // bind parameters
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

// --------------------
// Data validation and error handling
// --------------------
try {
    // Decode input data
    $data = json_decode(file_get_contents("php://input"), true);

    // Check required fields
    if (!isset($data['regNo'], $data['email'], $data['mobile'], $data['password'])) {
        http_response_code(400);
        echo json_encode(["message" => "All fields are required."]);
        exit;
    }

    // Input validation
    $regNo = $data['regNo'];
    $email = $data['email'];
    $mobile = $data['mobile'];
    $password = $data['password'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(["message" => "Invalid email address."]);
        exit;
    }

    if (!preg_match('/^\d{10}$/', $mobile)) {
        http_response_code(400);
        echo json_encode(["message" => "Invalid mobile number. It must be 10 digits."]);
        exit;
    }

    // Initialize Database and User objects
    $database = new Database();
    $db = $database->connect();

    $user = new User($db);
    $user->regNo = $regNo;
    $user->email = $email;
    $user->mobile = $mobile;
    $user->password = $password;

    // Attempt to register the user
    if ($user->register()) {
        http_response_code(201);
        echo json_encode(["message" => "User registered successfully."]);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "User registration failed."]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["message" => $e->getMessage()]);
}
?>
