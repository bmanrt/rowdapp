<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html"); // Redirect to login if not logged in
    exit();
}

include('db_config.php'); // Include your database configuration

$user_id = $_SESSION['user_id'];
$target_dir = "uploads/";
$unique_name = uniqid() . "_" . basename($_FILES["media"]["name"]);
$target_file = $target_dir . $unique_name;
$uploadOk = 1;
$imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

// Check if image or video file is an actual image or video
$check = getimagesize($_FILES["media"]["tmp_name"]);
if($check !== false) {
    $uploadOk = 1;
} else {
    $file_type = mime_content_type($_FILES["media"]["tmp_name"]);
    if(strstr($file_type, "video/")) {
        $uploadOk = 1;
    } else {
        echo "File is not an image or video.";
        $uploadOk = 0;
    }
}

// Check file size (limit to 50MB)
if ($_FILES["media"]["size"] > 50000000) {
    echo "Sorry, your file is too large.";
    $uploadOk = 0;
}

// Allow certain file formats
if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
&& $imageFileType != "gif" && $imageFileType != "mp4" && $imageFileType != "avi" && $imageFileType != "mov") {
    echo "Sorry, only JPG, JPEG, PNG, GIF, MP4, AVI & MOV files are allowed.";
    $uploadOk = 0;
}

// Check if $uploadOk is set to 0 by an error
if ($uploadOk == 0) {
    echo "Sorry, your file was not uploaded.";
// if everything is ok, try to upload file
} else {
    if (move_uploaded_file($_FILES["media"]["tmp_name"], $target_file)) {
        $media_type = (strstr(mime_content_type($target_file), "video/")) ? 'video' : 'image';

        $stmt = $conn->prepare("INSERT INTO user_media (user_id, file_path, media_type) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $user_id, $target_file, $media_type);
        $stmt->execute();
        $stmt->close();

        echo "<!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Upload Success</title>
            <link rel='stylesheet' href='styles.css'>
            <style>
                body {
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    height: 100vh;
                    margin: 0;
                    font-family: 'Roboto', sans-serif;
                }
                .container {
                    background-color: #fff;
                    padding: 20px;
                    border-radius: 10px;
                    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                    text-align: center;
                }
                .back-button {
                    margin-top: 20px;
                    padding: 10px 20px;
                    background-color: #ffcc00;
                    color: #fff;
                    border: none;
                    border-radius: 5px;
                    cursor: pointer;
                    text-decoration: none;
                    font-size: 16px;
                    transition: background-color 0.3s ease;
                }
                .back-button:hover {
                    background-color: #e6b800;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <h1>Upload Success</h1>
                <p>The file ". htmlspecialchars( basename( $_FILES["media"]["name"])). " has been uploaded.</p>
                <a class='back-button' href='media_capture.html'>Back to Media Capture</a>
            </div>
        </body>
        </html>";
    } else {
        echo "Sorry, there was an error uploading your file.";
    }
}

$conn->close();
?>
