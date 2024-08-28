<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Respond with a JSON error message instead of redirecting to HTML
    header('Content-Type: application/json');
    echo json_encode(["status" => "error", "message" => "Not logged in"]);
    exit();
}

// Database connection
include('db_config.php');

// Set headers for JSON response
header('Content-Type: application/json');

// Check if the request method is GET
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $user_id = $_SESSION['user_id'];

    // Fetch latest entries for the logged-in user
    $sql = "SELECT name, email, phone, country, created_at FROM captured_data WHERE user_id = ? ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Initialize an array to hold the activities
    $activities = [];

    // If there are results, populate the activities array
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $activities[] = $row;
        }
    }

    // Close the database connection
    $stmt->close();
    $conn->close();

    // Return the results in JSON format
    if (count($activities) > 0) {
        echo json_encode(["status" => "success", "data" => $activities]);
    } else {
        echo json_encode(["status" => "success", "message" => "No activities found", "data" => []]);
    }
} else {
    // Respond with a JSON error if the request method is not GET
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}
