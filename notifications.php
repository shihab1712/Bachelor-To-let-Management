<?php
session_start();
require 'db.php';

$user_id = $_SESSION['user_id'];

if (!isset($_SESSION['username'])) {
    header("Location: index.html");
    exit();
}

$username = $_SESSION['username'];
$user_type = $_SESSION['user_type'];

// Mark notifications as read
$stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();

// Get user_id
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$user_id = $user['id'];

// Handle clear notifications
if (isset($_POST['clear_notifications'])) {
    $deleteStmt = $conn->prepare("DELETE FROM notifications WHERE user_id = ? AND user_type = ?");
    $deleteStmt->bind_param("is", $user_id, $user_type);
    if ($deleteStmt->execute()) {
        $_SESSION['success'] = "All notifications cleared successfully.";
        header("Location: notifications.php");
        exit();
    }
}

// Fetch notifications
$query = $conn->prepare("
    SELECT message, created_at, is_read 
    FROM notifications 
    WHERE user_id = ? 
    ORDER BY created_at DESC
");
$query->bind_param("i", $user_id);
$query->execute();
$notifications = $query->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Your Notifications</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f3f3f3;
            padding: 20px;
        }
        .container {
            background: white;
            max-width: 800px;
            margin: auto;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 0 10px #aaa;
        }
        h2 {
            color: #0288d1;
        }
        .notification {
            border-bottom: 1px solid #ddd;
            padding: 15px 0;
        }
        .notification:last-child {
            border-bottom: none;
        }
        .time {
            font-size: 0.9em;
            color: #999;
        }
        .clear-btn {
            float: right;
            padding: 8px 16px;
            background-color: #f44336;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-bottom: 20px;
        }
        .clear-btn:hover {
            background-color: #d32f2f;
        }
        .header-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .back-btn {
            padding: 10px 20px;
            background-color: #00796b;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        .back-btn:hover {
            background-color: #004d40;
        }
        .button-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header-actions">
        <h2>Notifications</h2>
        <div class="button-group">
            <a href="home.php" class="back-btn">← Back to Home</a>
            <?php if ($notifications->num_rows > 0): ?>
                <form method="POST" onsubmit="return confirmClear()" style="display: inline-block; margin-left: 10px;">
                    <button type="submit" name="clear_notifications" class="clear-btn">
                        Clear All Notifications
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($notifications->num_rows > 0): ?>
        <?php while ($row = $notifications->fetch_assoc()): ?>
            <div class="notification">
                <p><?= htmlspecialchars($row['message']) ?></p>
                <div class="time"><?= $row['created_at'] ?></div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No notifications yet.</p>
    <?php endif; ?>
</div>

<script>
function confirmClear() {
    return confirm('Are you sure you want to clear all notifications? This action cannot be undone.');
}
</script>
</body>
</html>
