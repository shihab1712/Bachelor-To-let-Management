<?php
session_start();
require 'db.php';

if (!isset($_SESSION['username']) || !isset($_POST['rental_id'])) {
    header("Location: home.php");
    exit();
}

$rental_id = $_POST['rental_id'];
$username = $_SESSION['username'];

// Start transaction
$conn->begin_transaction();

try {
    // Create leave request
    $stmt = $conn->prepare("
        INSERT INTO leave_requests (rental_id, bachelor_username, request_date, status) 
        VALUES (?, ?, NOW(), 'Pending')
    ");
    $stmt->bind_param("is", $rental_id, $username);
    $stmt->execute();

    // Get owner information
    $ownerQuery = $conn->prepare("
        SELECT p.owner_username, u.id as owner_id
        FROM rental_requests r
        JOIN properties p ON r.property_id = p.id
        JOIN users u ON p.owner_username = u.username
        WHERE r.id = ?
    ");
    $ownerQuery->bind_param("i", $rental_id);
    $ownerQuery->execute();
    $ownerData = $ownerQuery->get_result()->fetch_assoc();

    // Create notification for owner
    $message = "Leave request from tenant " . htmlspecialchars($username);
    $stmt = $conn->prepare("
        INSERT INTO notifications (user_id, user_type, message) 
        VALUES (?, 'Owner', ?)
    ");
    $stmt->bind_param("is", $ownerData['owner_id'], $message);
    $stmt->execute();

    $conn->commit();
    $_SESSION['success'] = "Leave request submitted successfully.";
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = "Error submitting leave request.";
}

header("Location: manage_rental.php");
exit();