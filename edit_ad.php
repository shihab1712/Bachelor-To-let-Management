<?php

session_start();
require 'db.php';

if (!isset($_SESSION['username'])) {
    header("Location: index.html");
    exit();
}

$ad_id = $_GET['id'] ?? null;
$username = $_SESSION['username'];

// Fetch ad details
$stmt = $conn->prepare("SELECT * FROM vacancy_ads WHERE id = ? AND bachelor_username = ?");
$stmt->bind_param("is", $ad_id, $username);
$stmt->execute();
$ad = $stmt->get_result()->fetch_assoc();

if (!$ad) {
    header("Location: posted_ad.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $location = $_POST['location'];
    $rent = $_POST['rent'];
    
    $stmt = $conn->prepare("UPDATE vacancy_ads SET title = ?, location = ?, rent = ? WHERE id = ?");
    $stmt->bind_param("ssdi", $title, $location, $rent, $ad_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Ad updated successfully";
        header("Location: posted_ad.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Vacancy Ad</title>
    <!-- Add your CSS here -->
</head>
<body>
<div class="container">
    <h2>Edit Vacancy Ad</h2>
    <form method="post">
        <div>
            <label>Title</label>
            <input type="text" name="title" value="<?= htmlspecialchars($ad['title']) ?>" required>
        </div>
        <div>
            <label>Location</label>
            <input type="text" name="location" value="<?= htmlspecialchars($ad['location']) ?>" required>
        </div>
        <div>
            <label>Rent</label>
            <input type="number" name="rent" value="<?= htmlspecialchars($ad['rent']) ?>" required>
        </div>
        <button type="submit">Update Ad</button>
    </form>
</div>
</body>
</html>