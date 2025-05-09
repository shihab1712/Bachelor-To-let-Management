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

// Check if username exists
$check_sql = "SELECT id FROM users WHERE username = ?";
$stmt = $conn->prepare($check_sql);
$stmt->bind_param("s", $user);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    // Username exists, redirect with error
    header("Location: signup.html?error=username");
    exit();
}
$stmt->close();

// Hash the password before storing
$hashed_pass = password_hash($pass, PASSWORD_DEFAULT);

// Insert into database
$sql = "INSERT INTO users (username, password, email, nid, phone_number, user_type, preferred_location)
        VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssss", $user, $hashed_pass, $email, $nid, $phone, $type, $location);

if ($stmt->execute()) {
    // Redirect back to signup with success flag
    header("Location: signup.html?success=1");
    exit();
} else {
    echo "Error: " . $conn->error;
}

$stmt->close();
$conn->close();
?>
