<?php
header('Content-Type: application/json');

// Ensure the user is logged in by checking user_id in JSON request
$input = json_decode(file_get_contents("php://input"), true);

if (!isset($input['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Missing user_id"]);
    exit();
}

include('db_config.php');
$user_id = $conn->real_escape_string($input['user_id']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if a profile picture is provided
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['size'] > 0) {
        $profile_picture = $_FILES['profile_picture'];
        $imageFileType = strtolower(pathinfo($profile_picture['name'], PATHINFO_EXTENSION));
        $target_dir = "uploads/";
        $target_file = $target_dir . uniqid('', true) . '.' . $imageFileType;

        // Validate the file type and size
        $check = getimagesize($profile_picture['tmp_name']);
        if ($check === false) {
            echo json_encode(["status" => "error", "message" => "File is not a valid image"]);
            exit();
        }

        if ($profile_picture['size'] > 5000000) { // 5MB size limit
            echo json_encode(["status" => "error", "message" => "File size exceeds 5MB limit"]);
            exit();
        }

        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($imageFileType, $allowed_types)) {
            echo json_encode(["status" => "error", "message" => "Only JPG, JPEG, PNG & GIF formats are allowed"]);
            exit();
        }

        // Upload the new profile picture
        if (move_uploaded_file($profile_picture['tmp_name'], $target_file)) {
            // Fetch the existing profile picture (if any)
            $sql = "SELECT profile_picture FROM users WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $existing_profile_picture = $user['profile_picture'];
            $stmt->close();

            // Delete the old profile picture if it exists
            if (!empty($existing_profile_picture) && file_exists($existing_profile_picture)) {
                unlink($existing_profile_picture);
            }

            // Update the database with the new profile picture path
            $sql = "UPDATE users SET profile_picture = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('si', $target_file, $user_id);
            if ($stmt->execute()) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Profile picture updated successfully",
                    "profile_picture_url" => $target_file
                ]);
            } else {
                echo json_encode(["status" => "error", "message" => "Failed to update profile picture"]);
            }
            $stmt->close();
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to upload the profile picture"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "No profile picture uploaded"]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // Return the user's current profile picture URL
    $sql = "SELECT profile_picture FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if ($user) {
        echo json_encode([
            "status" => "success",
            "profile_picture_url" => $user['profile_picture']
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "User not found"]);
    }
}

$conn->close();
?>
