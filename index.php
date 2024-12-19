<?php

require __DIR__ . '/vendor/autoload.php';

use App\Controllers\UserController;

// Headers for CORS and JSON responses
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Debugging: Log the raw input
$rawInput = file_get_contents("php://input");
error_log("Raw input: " . $rawInput);

// Decode input data
$data = json_decode($rawInput, true);

if ($data === null) {
    http_response_code(400); // Bad request
    echo json_encode(["message" => "Invalid JSON or missing data."]);
    exit;
}


// Pass data to UserController
$response = UserController::registerUser($data);

http_response_code($response["status"]);
echo json_encode(["message" => $response["message"]]);
