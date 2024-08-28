<?php
include('db_config.php');

// Set headers for JSON response
header("Content-Type: application/json");

// Check the request method (POST or GET)
if ($_SERVER["REQUEST_METHOD"] == "POST" || $_SERVER["REQUEST_METHOD"] == "GET") {
    
    // Retrieve email and password based on the request method
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // For POST requests
        $email = isset($_POST['email']) ? $_POST['email'] : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
    } else {
        // For GET requests (using query parameters)
        $email = isset($_GET['email']) ? $_GET['email'] : '';
        $password = isset($_GET['password']) ? $_GET['password'] : '';
    }

    // Check if email and password are provided
    if (empty($email) || empty($password)) {
        echo json_encode(["status" => "error", "message" => "Email and password are required!"]);
    } else {
        // Prepare SQL statement to find user by email
        $sql = "SELECT id, password FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if user exists
        if ($result->num_rows > 0) {
            // User found, verify the password
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                // Password correct
                session_start();
                $_SESSION['user_id'] = $row['id'];
                echo json_encode(["status" => "success", "message" => "Login successful!"]);
            } else {
                // Password incorrect
                echo json_encode(["status" => "error", "message" => "Invalid email or password!"]);
            }
        } else {
            // User not found
            echo json_encode(["status" => "error", "message" => "Invalid email or password!"]);
        }

        // Close statement
        $stmt->close();
    }

    // Close connection
    $conn->close();
} else {
    // If request method is not POST or GET
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
}
?>
