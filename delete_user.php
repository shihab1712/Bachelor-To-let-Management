<?php
session_start();
require 'db.php';


if (isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];
    
    // Get user's username before deletion
    $stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    
    if (!$user) {
        echo json_encode(['success' => false, 'error' => 'User not found']);
        exit();
    }

    $username = $user['username'];
    $conn->begin_transaction();
    
    try {
        // Delete user's notifications first
        $stmt = $conn->prepare("DELETE FROM notifications WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        // Get the user's ID to use for property reviews
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $userId = $stmt->get_result()->fetch_assoc()['id'];

        // Delete user's rental requests
        $stmt = $conn->prepare("DELETE FROM rental_requests WHERE bachelor_username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        
        // Delete user's property reviews using bachelor_id
        $stmt = $conn->prepare("DELETE FROM property_reviews WHERE bachelor_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        
        // Delete user's properties if they're an owner
        $stmt = $conn->prepare("DELETE FROM properties WHERE owner_username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        
        // Delete user's vacancy ads if they're a bachelor
        $stmt = $conn->prepare("DELETE FROM vacancy_ads WHERE bachelor_username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        
        // Finally delete the user
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        $conn->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'No user ID provided']);
}