<?php

session_start();
require 'db.php';

if (!isset($_SESSION['username'])) {
    header("Location: index.html");
    exit();
}

$request_id = $_POST['request_id'] ?? null;
$action = $_POST['action'] ?? null;

if ($request_id && in_array($action, ['approve', 'reject'])) {
    $conn->begin_transaction();
    
    try {
        $new_status = ($action === 'approve') ? 'Approved' : 'Rejected';
        
        // Get requester information
        $userQuery = $conn->prepare("
            SELECT vr.requester_id, va.title
            FROM vacancy_requests vr
            JOIN vacancy_ads va ON vr.ad_id = va.id
            WHERE vr.id = ?
        ");
        $userQuery->bind_param("i", $request_id);
        $userQuery->execute();
        $requestInfo = $userQuery->get_result()->fetch_assoc();
        
        // Update vacancy request status
        $stmt = $conn->prepare("UPDATE vacancy_requests SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $request_id);
        $stmt->execute();

        // Send notification to requester
        $message = "Your request for vacancy '{$requestInfo['title']}' has been " . strtolower($new_status);
        addNotification($conn, $requestInfo['requester_id'], $message);

        if ($action === 'approve') {
            // Get the vacancy ad ID
            $adQuery = $conn->prepare("SELECT ad_id FROM vacancy_requests WHERE id = ?");
            $adQuery->bind_param("i", $request_id);
            $adQuery->execute();
            $adResult = $adQuery->get_result();
            $ad = $adResult->fetch_assoc();
            
            // Update vacancy status to Occupied
            $updateAd = $conn->prepare("UPDATE vacancy_ads SET status = 'Occupied' WHERE id = ?");
            $updateAd->bind_param("i", $ad['ad_id']);
            $updateAd->execute();
            
            // Reject all other pending requests for this ad
            $rejectOthers = $conn->prepare("
                UPDATE vacancy_requests 
                SET status = 'Rejected' 
                WHERE ad_id = ? AND id != ? AND status = 'Pending'
            ");
            $rejectOthers->bind_param("ii", $ad['ad_id'], $request_id);
            $rejectOthers->execute();
        }

        $conn->commit();
        $_SESSION['success'] = "Request has been " . strtolower($new_status);
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Error processing request: " . $e->getMessage();
    }
}

header("Location: posted_ad.php");
exit();