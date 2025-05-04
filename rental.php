<?php
session_start();
require 'db.php';

if (!isset($_SESSION['username']) || $_SESSION['user_type'] !== 'Bachelor') {
    header("Location: index.html");
    exit();
}

$username = $_SESSION['username'];
$property_id = $_POST['property_id'] ?? null;

if (!$property_id) {
    die("Invalid request.");
}

// Fetch additional user info
$stmt = $conn->prepare("SELECT user_type, preferred_location, email FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$user_profession = $user['user_type'];
$user_institute = $user['preferred_location']; // or change to real institute field if added

// Insert into rentals table
$stmt = $conn->prepare("INSERT INTO rentals (username, property_id, user_profession, user_institute) VALUES (?, ?, ?, ?)");
$stmt->bind_param("siss", $username, $property_id, $user_profession, $user_institute);

if ($stmt->execute()) {
    echo "<script>alert('Rent request submitted successfully!'); window.location.href='view_properties.php';</script>";
} else {
    echo "<script>alert('Error submitting request.'); window.location.href='view_properties.php';</script>";
}

$stmt->close();
?>
