<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

header('Content-Type: application/json');
include('db_config.php');

$user_id = $_SESSION['user_id'];

// Fetch number of data captures
$dataCapturesResult = $conn->query("SELECT COUNT(id) AS dataCaptures FROM captured_data WHERE user_id = $user_id");
$dataCapturesRow = $dataCapturesResult->fetch_assoc();
$dataCaptures = $dataCapturesRow['dataCaptures'];

// Fetch number of media captures
$mediaCapturesResult = $conn->query("SELECT COUNT(id) AS mediaCaptures FROM media WHERE user_id = $user_id");
$mediaCapturesRow = $mediaCapturesResult->fetch_assoc();
$mediaCaptures = $mediaCapturesRow['mediaCaptures'];

// Calculate points and dollars
$points = $dataCaptures + $mediaCaptures; // Assuming 1 point per capture
$dollars = ($points / 100) * 10;

$statistics = [
    'dataCaptures' => $dataCaptures,
    'mediaCaptures' => $mediaCaptures,
    'points' => $points,
    'dollars' => $dollars
];

echo json_encode($statistics);

$conn->close();
?>
