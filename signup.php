<?php
// signup.php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bachelor_tolet";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Receive form data
$user = $_POST['username'];
$pass = $_POST['password'];
$email = $_POST['email'];
$nid = $_POST['nid'];
$phone = $_POST['phone_number'];
$type = $_POST['user_type'];
$location = $_POST['preferred_location'];

// Insert into database
$sql = "INSERT INTO users (username, password, email, nid, phone_number, user_type, preferred_location)
        VALUES ('$user', '$pass', '$email', '$nid', '$phone', '$type', '$location')";

if ($conn->query($sql) === TRUE) {
    // Redirect back to signup with success flag
    header("Location: signup.html?success=1");
    exit();
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?>
