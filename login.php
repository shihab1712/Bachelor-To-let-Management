<?php
include 'db.php';
session_start();

$username = $_POST['username'];
$password = $_POST['password']; // ✅ This was missing earlier

// Fetch hashed password and user_type from DB
$sql = "SELECT id, password, user_type FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($user_id, $hashed_password, $user_type);
if ($stmt->fetch()) {
    if (password_verify($password, $hashed_password)) {
        // Password correct, log in user
        $_SESSION['user_id'] = $user_id; // <-- Add this line
        $_SESSION['username'] = $username;
        $_SESSION['user_type'] = $user_type; // <-- Save user_type in session
        header("Location: home.php");
        exit();
    } else {
        // Wrong password
        header("Location: index.html?error=invalid");
        exit();
    }
} else {
    // Username not found
    header("Location: index.html?error=invalid");
    exit();
}

$stmt->close();
$conn->close();
?>
