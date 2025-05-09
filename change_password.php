<?php
session_start();
require 'db.php';

if (!isset($_SESSION['username'])) {
    header("Location: index.html");
    exit();
}

$username = $_SESSION['username'];
$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Fetch current hashed password from DB
$stmt = $conn->prepare("SELECT password FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($hashed_password);
$stmt->fetch();
$stmt->close();

// If you are NOT using password_hash, remove the password_verify part and use plain comparison (not recommended).
if (!password_verify($current_password, $hashed_password)) {
    header("Location: profile.php?passmsg=Current password is incorrect.&passerr=1");
    exit();
}

if ($new_password !== $confirm_password) {
    header("Location: profile.php?passmsg=New passwords do not match.&passerr=1");
    exit();
}

if (strlen($new_password) < 6) {
    header("Location: profile.php?passmsg=Password must be at least 6 characters.&passerr=1");
    exit();
}

// Hash new password
$new_hashed = password_hash($new_password, PASSWORD_DEFAULT);

// Update password in DB
$update = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
$update->bind_param("ss", $new_hashed, $username);

if ($update->execute()) {
    header("Location: profile.php?passmsg=Password updated successfully!");
} else {
    header("Location: profile.php?passmsg=Failed to update password.&passerr=1");
}
exit();
?>