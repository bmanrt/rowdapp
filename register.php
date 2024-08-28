<?php
include('db_config.php');

// Set headers for JSON response
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the JSON input data
    $input = json_decode(file_get_contents("php://input"), true);

    // Validate that necessary data is provided
    if (isset($input['name']) && isset($input['email']) && isset($input['country']) && isset($input['password'])) {
        $name = $conn->real_escape_string($input['name']);
        $email = $conn->real_escape_string($input['email']);
        $country = $conn->real_escape_string($input['country']);
        $password = password_hash($conn->real_escape_string($input['password']), PASSWORD_DEFAULT);

        // Check if email is valid
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(["status" => "error", "message" => "Invalid email format"]);
            exit();
        }

        // Check for duplicate email
        $checkEmail = "SELECT id FROM users WHERE email = ?";
        $stmt = $conn->prepare($checkEmail);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            echo json_encode(["status" => "error", "message" => "Email already exists"]);
            $stmt->close();
            exit();
        }
        $stmt->close();

        // Insert data into the database
        $sql = "INSERT INTO users (name, email, country, password) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $name, $email, $country, $password);

        if ($stmt->execute()) {
            // Success response
            echo json_encode(["status" => "success", "message" => "Registration successful!"]);
        } else {
            // Error response
            echo json_encode(["status" => "error", "message" => "Database error: " . $stmt->error]);
        }

        $stmt->close();
    } else {
        // Invalid input response
        echo json_encode(["status" => "error", "message" => "Invalid input data"]);
    }
} else {
    // Invalid request method response
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}

// Close the database connection
$conn->close();
?>
