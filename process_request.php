<?php
session_start();
require 'db.php';

// if (!isset($_SESSION['username']) || $_SESSION['user_type'] !== 'Owner') {
//     header("Location: index.html");
//     exit();
// }

$request_id = $_POST['request_id'] ?? null;
$action = $_POST['action'] ?? null;

if ($request_id && in_array($action, ['approve', 'reject'])) {
    $new_status = ($action === 'approve') ? 'Approved' : 'Rejected';

    $stmt = $conn->prepare("UPDATE rental_requests SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $request_id);
    $stmt->execute();
}

header("Location: rental_requests.php");
exit();
