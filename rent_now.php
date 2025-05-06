<?php 
session_start();
require 'db.php';

if (!isset($_SESSION['username'])) {
    header("Location: index.html");
    exit();
}

$bachelor_username = $_SESSION['username'];
$property_id = $_POST['property_id'] ?? null;

// Get bachelor user id
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt->bind_param("s", $bachelor_username);
$stmt->execute();
$result = $stmt->get_result();
$bachelor = $result->fetch_assoc();
$bachelor_id = $bachelor['id'] ?? null;

if ($property_id && $bachelor_id) {
    // Check if already requested
    $check = $conn->prepare("SELECT * FROM rental_requests WHERE bachelor_id = ? AND property_id = ?");
    $check->bind_param("ii", $bachelor_id, $property_id);
    $check->execute();
    $existing = $check->get_result();

    if ($existing->num_rows === 0) {
        // Now insert with bachelor_username
        $insert = $conn->prepare("INSERT INTO rental_requests (bachelor_id, bachelor_username, property_id) VALUES (?, ?, ?)");
        $insert->bind_param("isi", $bachelor_id, $bachelor_username, $property_id);
        $insert->execute();
    }

    header("Location: view_properties.php");
    exit();
}
?>

