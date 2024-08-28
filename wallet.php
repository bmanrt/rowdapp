<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html"); // Redirect to login if not logged in
    exit();
}

include('db_config.php'); // Include your database configuration

$user_id = $_SESSION['user_id'];

// Calculate points from captured_data
$stmt = $conn->prepare("SELECT COUNT(id) AS data_count FROM captured_data WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$data_count = $row['data_count'];
$stmt->close();

$points = $data_count; // Assuming 1 point per entry
$dollars = ($points / 100) * 10;

$conn->close();

header('Content-Type: application/json');
echo json_encode(['points' => $points, 'dollars' => $dollars]);
?>
