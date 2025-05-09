<?php

session_start();
require 'db.php';



$username = $_SESSION['username'];
$vacancy_id = $_POST['vacancy_id'] ?? null;

if ($vacancy_id) {
    // Get requester's user ID
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if ($user) {
        // Get vacancy owner information
        $ownerQuery = $conn->prepare("
            SELECT v.bachelor_username, v.title, u.id as owner_id
            FROM vacancy_ads v
            JOIN users u ON v.bachelor_username = u.username
            WHERE v.id = ?
        ");
        $ownerQuery->bind_param("i", $vacancy_id);
        $ownerQuery->execute();
        $ownerInfo = $ownerQuery->get_result()->fetch_assoc();

        // Check if already requested
        $checkStmt = $conn->prepare("
            SELECT * FROM vacancy_requests 
            WHERE requester_id = ? AND ad_id = ? AND status = 'Pending'
        ");
        $checkStmt->bind_param("ii", $user['id'], $vacancy_id);
        $checkStmt->execute();
        
        if ($checkStmt->get_result()->num_rows === 0) {
            $conn->begin_transaction();
            
            try {
                // Insert new request
                $insertStmt = $conn->prepare("
                    INSERT INTO vacancy_requests (requester_id, ad_id) 
                    VALUES (?, ?)
                ");
                $insertStmt->bind_param("ii", $user['id'], $vacancy_id);
                
                if ($insertStmt->execute()) {
                    // Notify vacancy owner
                    $message = "New request for your vacancy '{$ownerInfo['title']}' from {$username}";
                    addNotification($conn, $ownerInfo['owner_id'], $message);
                    
                    $conn->commit();
                    $_SESSION['success'] = "Request sent successfully!";
                } else {
                    throw new Exception("Error sending request.");
                }
            } catch (Exception $e) {
                $conn->rollback();
                $_SESSION['error'] = $e->getMessage();
            }
        } else {
            $_SESSION['error'] = "You have already requested this vacancy.";
        }
    }
}

header("Location: view_vacancies.php");
exit();