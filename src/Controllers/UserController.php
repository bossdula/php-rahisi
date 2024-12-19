<?php

namespace App\Controllers;

use App\Database;
use App\Models\UserModel;
use Exception;

class UserController {
    public static function registerUser(array $data) {
        try {
            if (!isset($data['regNo'], $data['email'], $data['mobile'], $data['password'])) {
                throw new Exception("All fields are required.");
            }

            $regNo = $data['regNo'];
            $email = $data['email'];
            $mobile = $data['mobile'];
            $password = $data['password'];

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Invalid email address.");
            }

            if (!preg_match('/^\d{10}$/', $mobile)) {
                throw new Exception("Invalid mobile number. It must be 10 digits.");
            }

            $database = new Database();
            $db = $database->connect();

            $user = new UserModel($db);
            $user->regNo = $regNo;
            $user->email = $email;
            $user->mobile = $mobile;
            $user->password = $password;

            if ($user->register()) {
                return ["status" => 201, "message" => "User registered successfully."];
            } else {
                throw new Exception("User registration failed.");
            }
        } catch (Exception $e) {
            return ["status" => 500, "message" => $e->getMessage()];
        }
    }
}
