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

// Add this check before processing the rental request
if ($property_id && $bachelor_id) {
    // Check room availability first
    $roomCheck = $conn->prepare("SELECT rooms FROM properties WHERE id = ?");
    $roomCheck->bind_param("i", $property_id);
    $roomCheck->execute();
    $roomResult = $roomCheck->get_result();
    $propertyData = $roomResult->fetch_assoc();

    if ($propertyData['rooms'] <= 0) {
        $_SESSION['error'] = "No rooms available in this property.";
        header("Location: view_properties.php");
        exit();
    }

    // Check if already requested
    $check = $conn->prepare("SELECT * FROM rental_requests WHERE bachelor_id = ? AND property_id = ?");
    $check->bind_param("ii", $bachelor_id, $property_id);
    $check->execute();
    $existing = $check->get_result();

    if ($existing->num_rows === 0) {
        // Insert rental request
        $insert = $conn->prepare("INSERT INTO rental_requests (bachelor_id, bachelor_username, property_id) VALUES (?, ?, ?)");
        $insert->bind_param("isi", $bachelor_id, $bachelor_username, $property_id);
        $insert->execute();

        // Get owner's username and user_id
        $ownerQuery = $conn->prepare("
            SELECT p.owner_username, u.id as owner_id 
            FROM properties p 
            JOIN users u ON p.owner_username = u.username 
            WHERE p.id = ?
        ");
        $ownerQuery->bind_param("i", $property_id);
        $ownerQuery->execute();
        $ownerResult = $ownerQuery->get_result();
        $ownerData = $ownerResult->fetch_assoc();
        
        // Create notification for owner
        if ($ownerData) {
            $message = "New rental request from " . htmlspecialchars($bachelor_username);
            $userType = "Owner";
            $stmt = $conn->prepare("INSERT INTO notifications (user_id, user_type, message) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $ownerData['owner_id'], $userType, $message);
            $stmt->execute();
        }

        // Set success message
        $_SESSION['success'] = "Rental request sent successfully! The owner will review your request.";
    } else {
        $_SESSION['error'] = "You have already requested this property.";
    }

    header("Location: view_properties.php");
    exit();
}
?>

