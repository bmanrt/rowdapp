<?php
include('db_config.php');
include('send_email.php');

// Set the content-type to application/json for all responses
header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the raw JSON input from the request body
    $input = json_decode(file_get_contents("php://input"), true);

    // Check if the email is provided in the input
    if (isset($input['email'])) {
        $email = $input['email'];
        
        // Prepare and execute the query to check if the email exists
        $sql = "SELECT id FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Email exists, proceed with generating a reset token and expiry
            $token = bin2hex(random_bytes(50)); // 100 characters long token
            $expiry = date("Y-m-d H:i:s", strtotime('+1 hour'));

            // Update the user's reset_token and reset_expiry in the database
            $update_sql = "UPDATE users SET reset_token = ?, reset_expiry = ? WHERE email = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("sss", $token, $expiry, $email);

            if ($update_stmt->execute()) {
                // Send reset email using your sendResetEmail function
                sendResetEmail($email, $token);
                echo json_encode(["status" => "success", "message" => "A reset link has been sent to your email."]);
            } else {
                // Handle SQL execution error
                echo json_encode(["status" => "error", "message" => "Failed to update reset token in the database."]);
            }

            $update_stmt->close();
        } else {
            // Email not found in the database
            echo json_encode(["status" => "error", "message" => "No account found with that email address."]);
        }

        $stmt->close();
    } else {
        // Email not provided in the request
        echo json_encode(["status" => "error", "message" => "Email is required."]);
    }

    $conn->close();
} else {
    // Invalid request method
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
}
?>
