<?php
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents("php://input"));
        $regno = $data->regNo; // Ensure the key name matches what you send from Angular
        $password = $data->password;
    
        // Validate input data
        if (empty($regno) || empty($password)) {
            echo json_encode(["message" => "RegNo and password are required", "status" => 400]);
            exit;
        }
    
        // Connect to the database
        $conn = new mysqli('localhost', 'root', '', 'angular'); // Update with your DB details
    
        if ($conn->connect_error) {
            die(json_encode(["message" => "Database connection failed", "status" => 500]));
        }
    
        // Query to find the user by regno
        $stmt = $conn->prepare("SELECT * FROM users WHERE regno = ?");
        $stmt->bind_param("s", $regno);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows === 0) {
            echo json_encode(["message" => "Invalid credentials", "status" => 401]);
        } else {
            $user = $result->fetch_assoc();
            // Check if the password matches
            if (password_verify($password, $user['password'])) {
                echo json_encode(["message" => "Login successful", "status" => 200]);
            } else {
                echo json_encode(["message" => "Invalid credentials", "status" => 401]);
            }
        }
    
        $conn->close();
    } else {
        echo json_encode(["message" => "Invalid request method", "status" => 400]);
    }
    ?>
    