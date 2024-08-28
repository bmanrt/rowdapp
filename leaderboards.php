<?php
include('db_config.php');
include('utils.php');

// Example logic to update user points
function updateUserPoints($userId, $points) {
    global $conn;
    $sql = "UPDATE users SET points = points + $points WHERE id = $userId";
    if ($conn->query($sql) === TRUE) {
        return "User points updated successfully!";
    } else {
        return "Error updating user points: " . $conn->error;
    }
}

// Example usage
$userId = 1; // Replace with actual user ID
$points = 10; // Replace with actual points to add
echo updateUserPoints($userId, $points);

$conn->close();
?>
