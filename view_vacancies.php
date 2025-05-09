<?php
session_start();
require 'db.php';

if (!isset($_SESSION['username'])) {
    header("Location: index.html");
    exit();
}

$username = $_SESSION['username'];

// Get all available vacancy ads except user's own ads
$query = $conn->prepare("
    SELECT v.*, u.phone_number, u.email
    FROM vacancy_ads v
    JOIN users u ON v.bachelor_username = u.username
    WHERE v.status = 'Available' 
    AND v.bachelor_username != ?
    ORDER BY v.created_at DESC
");
$query->bind_param("s", $username);
$query->execute();
$vacancies = $query->get_result();

// Check if user has any pending requests
$checkRequests = $conn->prepare("
    SELECT ad_id FROM vacancy_requests 
    WHERE requester_id = (SELECT id FROM users WHERE username = ?)
    AND status = 'Pending'
");
$checkRequests->bind_param("s", $username);
$checkRequests->execute();
$pendingRequests = $checkRequests->get_result();
$requestedAds = [];
while ($row = $pendingRequests->fetch_assoc()) {
    $requestedAds[] = $row['ad_id'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Available Vacancies</title>
    <style>
        body {
            font-family: Arial;
            background: #eef7f9;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .vacancy-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 4px solid #00796b;
            display: grid;
            grid-template-columns: 3fr 1fr;
            gap: 20px;
        }
        .vacancy-info h3 {
            margin: 0 0 10px 0;
            color: #00796b;
        }
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            color: white;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
        }
        .btn-request {
            background-color: #00796b;
        }
        .btn-pending {
            background-color: #f57c00;
            cursor: not-allowed;
        }
        .contact-info {
            color: #666;
            font-size: 0.9em;
            margin-top: 10px;
        }
        .success, .error {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .success {
            background-color: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #a5d6a7;
        }
        .error {
            background-color: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }
    </style>
</head>
<body>
<div class="container">
    <div style="text-align: right; margin-bottom: 20px;">
        <a href="home.php" class="btn btn-request">← Back to Home</a>
    </div>

    <h2>Available Vacancies</h2>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="success">
            <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="error">
            <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
            ?>
        </div>
    <?php endif; ?>

    <?php if ($vacancies->num_rows > 0): ?>
        <?php while ($vacancy = $vacancies->fetch_assoc()): ?>
            <div class="vacancy-card">
                <div class="vacancy-info">
                    <h3><?= htmlspecialchars($vacancy['title']) ?></h3>
                    <p><strong>Location:</strong> <?= htmlspecialchars($vacancy['location']) ?></p>
                    <p><strong>Monthly Rent:</strong> ৳<?= htmlspecialchars($vacancy['rent']) ?></p>
                    <div class="contact-info">
                        <p><strong>Posted by:</strong> <?= htmlspecialchars($vacancy['bachelor_username']) ?></p>
                        <p><strong>Contact:</strong> <?= htmlspecialchars($vacancy['phone_number']) ?></p>
                        <p><strong>Email:</strong> <?= htmlspecialchars($vacancy['email']) ?></p>
                    </div>
                </div>
                <div style="text-align: right;">
                    <?php if (in_array($vacancy['id'], $requestedAds)): ?>
                        <button class="btn btn-pending" disabled>Request Pending</button>
                    <?php else: ?>
                        <form method="post" action="request_vacancy.php">
                            <input type="hidden" name="vacancy_id" value="<?= $vacancy['id'] ?>">
                            <button type="submit" class="btn btn-request" 
                                onclick="return confirm('Are you sure you want to request this vacancy?')">
                                Request to Rent
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No vacancies available at the moment.</p>
    <?php endif; ?>
</div>
</body>
</html>
