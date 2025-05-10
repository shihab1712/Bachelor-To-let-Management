<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = $_POST['request_id'];
    $action = $_POST['action'];

    // Get bachelor_username and property_id for this request
    $stmt = $conn->prepare("SELECT bachelor_username, property_id FROM rental_requests WHERE id = ?");
    $stmt->bind_param("i", $request_id);
    $stmt->execute();
    $stmt->bind_result($bachelor_username, $property_id);
    $stmt->fetch();
    $stmt->close();

    if ($action === 'approve') {
        // 1. Check if bachelor already has an approved rental
        $check = $conn->prepare("SELECT COUNT(*) FROM rental_requests WHERE bachelor_username = ? AND status = 'Approved'");
        $check->bind_param("s", $bachelor_username);
        $check->execute();
        $check->bind_result($approved_count);
        $check->fetch();
        $check->close();

        if ($approved_count > 0) {
            $_SESSION['error'] = "This bachelor already has an approved rental.";
            header("Location: rental_requests.php");
            exit();
        }

        // 2. Approve this request
        $approve = $conn->prepare("UPDATE rental_requests SET status = 'Approved' WHERE id = ?");
        $approve->bind_param("i", $request_id);
        $approve->execute();
        $approve->close();

        // 3. Reject all other pending requests for this bachelor
        $reject = $conn->prepare("UPDATE rental_requests SET status = 'Rejected' WHERE bachelor_username = ? AND status = 'Pending' AND id != ?");
        $reject->bind_param("si", $bachelor_username, $request_id);
        $reject->execute();
        $reject->close();

        // 4. Optionally, decrease available rooms for the property
        $conn->query("UPDATE properties SET rooms = rooms - 1 WHERE id = $property_id");

        $_SESSION['success'] = "Request approved and other pending requests for this bachelor have been rejected.";
        header("Location: rental_requests.php");
        exit();
    } elseif ($action === 'reject') {
        // Delete this request instead of updating status
        $delete = $conn->prepare("DELETE FROM rental_requests WHERE id = ?");
        $delete->bind_param("i", $request_id);
        $delete->execute();
        $delete->close();

        $_SESSION['success'] = "Request rejected and removed.";
        header("Location: rental_requests.php");
        exit();
    }
}
?>
