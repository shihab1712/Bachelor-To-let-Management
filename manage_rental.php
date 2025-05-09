<?php
session_start();
require 'db.php';

if (!isset($_SESSION['username'])) {
    header("Location: index.html");
    exit();
}

$username = $_SESSION['username'];

// Get active rental details
$stmt = $conn->prepare("
    SELECT r.*, p.location, p.rent, p.owner_username
    FROM rental_requests r
    JOIN properties p ON r.property_id = p.id
    WHERE r.bachelor_username = ? AND r.status = 'Approved'
");
$stmt->bind_param("s", $username);
$stmt->execute();
$rental = $stmt->get_result()->fetch_assoc();

// Check if there's a pending leave request
$leaveCheck = $conn->prepare("
    SELECT * FROM leave_requests 
    WHERE rental_id = ? AND status = 'Pending'
");
$leaveCheck->bind_param("i", $rental['id']);
$leaveCheck->execute();
$hasPendingRequest = $leaveCheck->get_result()->num_rows > 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Your Rental</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(to right, #e1f5fe, #b3e5fc);
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .rental-info {
            margin: 20px 0;
            padding: 15px;
            background: #f5f5f5;
            border-radius: 5px;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            color: white;
        }
        .btn-leave {
            background-color: #f44336;
        }
        .btn-leave:hover {
            background-color: #d32f2f;
        }
        .pending-notice {
            color: #ff9800;
            font-style: italic;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Your Current Rental</h2>
    
    <div class="rental-info">
        <p><strong>Location:</strong> <?= htmlspecialchars($rental['location']) ?></p>
        <p><strong>Monthly Rent:</strong> <?= htmlspecialchars($rental['rent']) ?></p>
        <p><strong>Owner:</strong> <?= htmlspecialchars($rental['owner_username']) ?></p>
        <p><strong>Status:</strong> <?= htmlspecialchars($rental['status']) ?></p>
    </div>

    <?php if (!$hasPendingRequest): ?>
        <form action="request_leave.php" method="post">
            <input type="hidden" name="rental_id" value="<?= $rental['id'] ?>">
            <button type="submit" class="btn btn-leave">Request to Leave Property</button>
        </form>
    <?php else: ?>
        <p class="pending-notice">Your leave request is pending owner approval.</p>
    <?php endif; ?>

    <p><a href="home.php">← Back to Home</a></p>
</div>
</body>
</html>