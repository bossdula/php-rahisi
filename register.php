<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Decode incoming JSON
$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['regNo']) && isset($data['email']) && isset($data['mobile']) && isset($data['password'])) {
    $regNo = htmlspecialchars($data['regNo']);
    $email = htmlspecialchars($data['email']);
    $mobile = htmlspecialchars($data['mobile']);
    $password = password_hash($data['password'], PASSWORD_BCRYPT);

    // Validate input data
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(["message" => "Invalid email address"]);
        exit;
    }

    if (!preg_match('/^\d{10}$/', $mobile)) {
        http_response_code(400);
        echo json_encode(["message" => "Invalid mobile number"]);
        exit;
    }


    // Database connection
    $conn = new mysqli("localhost", "root", "", "angular");

    if ($conn->connect_error) {
        http_response_code(500);
        echo json_encode(["message" => "Database connection failed"]);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO users (regno, email, mobile, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $regNo, $email, $mobile, $password);

    if ($stmt->execute()) {
        echo json_encode(["message" => "User registered successfully"]);
    } else {
        echo json_encode(["message" => "Registration failed"]);
    }

    $stmt->close();
    $conn->close();
} else {
    http_response_code(400);
    echo json_encode(["message" => "Invalid input"]);
}
?>
