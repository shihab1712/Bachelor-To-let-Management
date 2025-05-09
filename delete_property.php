<?php
session_start();
require 'db.php';


if (isset($_POST['property_id'])) {
    $property_id = $_POST['property_id'];
    
    // Check for active rentals
    $checkRentals = $conn->prepare("
        SELECT COUNT(*) as active_rentals 
        FROM rental_requests 
        WHERE property_id = ? AND status = 'Approved'
    ");
    $checkRentals->bind_param("i", $property_id);
    $checkRentals->execute();
    $rentalResult = $checkRentals->get_result();
    $activeRentals = $rentalResult->fetch_assoc()['active_rentals'];
    
    if ($activeRentals > 0) {
        $_SESSION['error'] = "Cannot delete property with active tenants.";
        header("Location: view_posted_properties.php");
        exit();
    }

    // Start transaction for safe deletion
    $conn->begin_transaction();

    try {
        // First delete leave requests
        $deleteLeaveRequests = $conn->prepare("
            DELETE lr FROM leave_requests lr
            INNER JOIN rental_requests rr ON lr.rental_id = rr.id
            WHERE rr.property_id = ?
        ");
        $deleteLeaveRequests->bind_param("i", $property_id);
        $deleteLeaveRequests->execute();

        // Then delete property reviews
        $deleteReviews = $conn->prepare("DELETE FROM property_reviews WHERE property_id = ?");
        $deleteReviews->bind_param("i", $property_id);
        $deleteReviews->execute();

        // Next delete rental requests
        $deleteRentals = $conn->prepare("DELETE FROM rental_requests WHERE property_id = ?");
        $deleteRentals->bind_param("i", $property_id);
        $deleteRentals->execute();

        // Finally delete the property
        $deleteProperty = $conn->prepare("DELETE FROM properties WHERE id = ? AND owner_username = ?");
        $deleteProperty->bind_param("is", $property_id, $_SESSION['username']);
        $deleteProperty->execute();

        $conn->commit();
        $_SESSION['success'] = "Property and all related records deleted successfully.";
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Error deleting property. Please try again.";
    }
}

header("Location: view_posted_properties.php");
exit();
