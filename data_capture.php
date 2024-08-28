<?php
// Set the header to indicate the content type as JSON
header('Content-Type: application/json');

// Start the session to get the user_id
session_start();

// Include database configuration
include('db_config.php');

// Get the JSON input from the Flutter app
$input = json_decode(file_get_contents("php://input"), true);

// Validate the input
if (!isset($input['user_id']) || !isset($input['name']) || !isset($input['email']) || !isset($input['phone']) || !isset($input['country'])) {
    http_response_code(400); // Bad Request
    echo json_encode(["status" => "error", "message" => "Missing required fields"]);
    exit();
}

// Sanitize and assign the input values
$user_id = $conn->real_escape_string($input['user_id']);
$name = $conn->real_escape_string($input['name']);
$email = $conn->real_escape_string($input['email']);
$phone = $conn->real_escape_string($input['phone']);
$country = $conn->real_escape_string($input['country']);

// Insert the data into the captured_data table
$sql = "INSERT INTO captured_data (user_id, name, email, phone, country) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("issss", $user_id, $name, $email, $phone, $country);
    
    if ($stmt->execute()) {
        // Send success response
        echo json_encode(["status" => "success", "message" => "Data captured successfully"]);
    } else {
        // If there was a problem executing the query
        http_response_code(500); // Internal Server Error
        echo json_encode(["status" => "error", "message" => "Failed to capture data"]);
    }

    $stmt->close();
} else {
    // If there was a problem preparing the SQL statement
    http_response_code(500); // Internal Server Error
    echo json_encode(["status" => "error", "message" => "Database error: " . $conn->error]);
}

// Close the database connection
$conn->close();
?>
