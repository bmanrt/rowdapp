<?php
// Set headers for JSON response and to accept JSON request
header('Content-Type: application/json');

// Ensure we are receiving a POST request with JSON content
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(["error" => "Only POST requests are allowed"]);
    exit();
}

// Get JSON input and check if it's valid
$input = json_decode(file_get_contents("php://input"), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400); // Bad Request
    echo json_encode(["error" => "Malformed JSON input"]);
    exit();
}

// Check if user_id is provided
if (!isset($input['user_id'])) {
    http_response_code(400); // Bad Request
    echo json_encode(["error" => "Missing user_id"]);
    exit();
}

include 'db_config.php';

// Sanitize and assign the user_id
$user_id = $conn->real_escape_string($input['user_id']);

// Prepare the query to fetch user data
$query = $conn->prepare("SELECT name, email, country, profile_picture FROM users WHERE id = ?");
if (!$query) {
    http_response_code(500); // Internal Server Error
    echo json_encode(["error" => "Database query preparation failed: " . $conn->error]);
    exit();
}

$query->bind_param('i', $user_id);
$query->execute();
$result = $query->get_result();

// Check if the user was found
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();

    // Optionally, add the base URL for the profile picture
    if (!empty($user['profile_picture'])) {
        $user['profile_picture'] = 'http://yourserver.com/' . $user['profile_picture']; // Adjust the base URL
    }

    echo json_encode($user); // Return user details as JSON
} else {
    http_response_code(404); // Not Found
    echo json_encode(["error" => "User not found"]);
}

$query->close();
$conn->close();
?>
