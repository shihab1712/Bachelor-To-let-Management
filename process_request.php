<?php
session_start();
require 'db.php';

$request_id = $_POST['request_id'] ?? null;
$action = $_POST['action'] ?? null;

if ($request_id && in_array($action, ['approve', 'reject'])) {
    // Start transaction
    $conn->begin_transaction();
    
    try {
        $new_status = ($action === 'approve') ? 'Approved' : 'Rejected';

        // Get property info first
        $stmt = $conn->prepare("
            SELECT p.id, p.rooms, r.bachelor_id 
            FROM rental_requests r
            JOIN properties p ON r.property_id = p.id
            WHERE r.id = ?
        ");
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $request = $result->fetch_assoc();

        if ($action === 'approve' && $request['rooms'] <= 0) {
            throw new Exception("No rooms available");
        }

        // Update request status
        $stmt = $conn->prepare("UPDATE rental_requests SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $request_id);
        $stmt->execute();

        if ($action === 'approve') {
            // Decrease room count
            $new_rooms = $request['rooms'] - 1;
            $stmt = $conn->prepare("UPDATE properties SET rooms = ?, status = CASE WHEN ? = 0 THEN 'Occupied' ELSE status END WHERE id = ?");
            $stmt->bind_param("iii", $new_rooms, $new_rooms, $request['id']);
            $stmt->execute();
        }

        // Create notification for bachelor
        $message = "Your rental request has been " . $action . "ed";
        $userType = "Bachelor";
        $stmt = $conn->prepare("INSERT INTO notifications (user_id, user_type, message) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $request['bachelor_id'], $userType, $message);
        $stmt->execute();

        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = $e->getMessage();
    }
}

header("Location: rental_requests.php");
exit();
