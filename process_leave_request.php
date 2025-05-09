<?php
session_start();
require 'db.php';


$leave_request_id = $_POST['leave_request_id'] ?? null;
$rental_id = $_POST['rental_id'] ?? null;
$property_id = $_POST['property_id'] ?? null;
$action = $_POST['action'] ?? null;

if ($leave_request_id && $rental_id && $property_id && in_array($action, ['approve', 'reject'])) {
    $conn->begin_transaction();
    
    try {
        $new_status = ($action === 'approve') ? 'Approved' : 'Rejected';
        
        // Update leave request status
        $stmt = $conn->prepare("UPDATE leave_requests SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $leave_request_id);
        $stmt->execute();

        if ($action === 'approve') {
            // Update rental request status to 'Terminated'
            $stmt = $conn->prepare("UPDATE rental_requests SET status = 'Terminated' WHERE id = ?");
            $stmt->bind_param("i", $rental_id);
            $stmt->execute();

            // Increase room count
            $stmt = $conn->prepare("
                UPDATE properties 
                SET rooms = rooms + 1,
                    status = CASE 
                        WHEN rooms + 1 > 0 THEN 'Available'
                        ELSE status
                    END
                WHERE id = ?
            ");
            $stmt->bind_param("i", $property_id);
            $stmt->execute();

            // Get bachelor's user_id
            $stmt = $conn->prepare("
                SELECT bachelor_id FROM rental_requests WHERE id = ?
            ");
            $stmt->bind_param("i", $rental_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $bachelor = $result->fetch_assoc();

            // Create notification for bachelor
            $message = "Your leave request has been approved.";
            $stmt = $conn->prepare("
                INSERT INTO notifications (user_id, user_type, message)
                VALUES (?, 'Bachelor', ?)
            ");
            $stmt->bind_param("is", $bachelor['bachelor_id'], $message);
            $stmt->execute();
        }

        $conn->commit();
        $_SESSION['success'] = "Leave request has been " . $new_status;
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Error processing leave request: " . $e->getMessage();
    }
}

header("Location: manage_leave_requests.php");
exit();