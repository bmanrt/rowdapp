<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Respond with JSON instead of redirecting (since this is an API)
    echo json_encode(["status" => "error", "message" => "Not logged in."]);
    exit();
}

include('db_config.php');

// Set headers for JSON response
header("Content-Type: application/json");

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the JSON input
    $input = json_decode(file_get_contents("php://input"), true);

    // Retrieve user data from session and input data from request
    $user_id = $_SESSION['user_id'];
    $name = isset($input['name']) ? $conn->real_escape_string($input['name']) : '';
    $email = isset($input['email']) ? $conn->real_escape_string($input['email']) : '';
    $phone = isset($input['phone']) ? $conn->real_escape_string($input['phone']) : '';
    $country = isset($input['country']) ? $conn->real_escape_string($input['country']) : '';

    // Check if required fields are provided
    if (empty($name) || empty($email) || empty($phone) || empty($country)) {
        echo json_encode(["status" => "error", "message" => "All fields are required."]);
    } else {
        // Insert data into the captured_data table
        $sql = "INSERT INTO captured_data (user_id, name, email, phone, country) VALUES ('$user_id', '$name', '$email', '$phone', '$country')";

        if ($conn->query($sql) === TRUE) {
            // Success response
            echo json_encode(["status" => "success", "message" => "Data captured successfully!"]);
        } else {
            // Error response
            echo json_encode(["status" => "error", "message" => "Database error: " . $conn->error]);
        }
    }
} else {
    // Invalid request method response
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
}

// Close the database connection
$conn->close();
?>
