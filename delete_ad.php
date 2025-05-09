<?php

session_start();
require 'db.php';

if (!isset($_SESSION['username'])) {
    header("Location: index.html");
    exit();
}

$ad_id = $_GET['id'] ?? null;
$username = $_SESSION['username'];

if ($ad_id) {
    // Check if the ad belongs to the user
    $stmt = $conn->prepare("DELETE FROM vacancy_ads WHERE id = ? AND bachelor_username = ?");
    $stmt->bind_param("is", $ad_id, $username);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Ad deleted successfully";
    } else {
        $_SESSION['error'] = "Error deleting ad";
    }
}

header("Location: posted_ad.php");
exit();