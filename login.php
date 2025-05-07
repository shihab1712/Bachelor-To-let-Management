<?php
include 'db.php';
session_start();

$username = $_POST['username'];
$password = $_POST['password']; // ✅ This was missing earlier

// Query to get user info by username
$sql = "SELECT * FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user && $user['password'] === $password) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $username;
    header("Location: home.php");

    exit();
} else {
    echo "<script>alert('Invalid credentials!'); window.location.href='index.html';</script>";
}

$stmt->close();
$conn->close();
?>
