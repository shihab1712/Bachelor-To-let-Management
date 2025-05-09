<?php
session_start();
require 'db.php';

if (!isset($_SESSION['username'])) {
    header("Location: index.html");
    exit();
}

$username = $_SESSION['username'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $location = $_POST['location'] ?? '';
    $rent = $_POST['rent'] ?? 0;
    
    // Validate inputs
    if (empty($title) || empty($location) || $rent <= 0) {
        $_SESSION['error'] = "All fields are required and rent must be greater than 0";
        header("Location: post_vacancy.php");
        exit();
    }

    // Insert the vacancy ad
    $stmt = $conn->prepare("
        INSERT INTO vacancy_ads (bachelor_username, title, location, rent) 
        VALUES (?, ?, ?, ?)
    ");
    $stmt->bind_param("sssd", $username, $title, $location, $rent);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Vacancy ad posted successfully!";
        header("Location: posted_ad.php");
        exit();
    } else {
        $_SESSION['error'] = "Error posting vacancy ad";
        header("Location: post_vacancy.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Post Vacancy Ad</title>
    <style>
        body {
            font-family: Arial;
            background: #eef7f9;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"],
        input[type="number"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .btn {
            padding: 10px 20px;
            background-color: #00796b;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn:hover {
            background-color: #00695c;
        }
        .error {
            color: #f44336;
            margin-bottom: 15px;
        }
        .success {
            color: #4caf50;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
<div class="container">
    <div style="text-align: right; margin-bottom: 20px;">
        <a href="home.php" style="
            padding: 10px 20px;
            background-color: #00796b;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        ">← Back to Home</a>
    </div>

    <h2>Post Vacancy Ad</h2>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="error">
            <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="success">
            <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']);
            ?>
        </div>
    <?php endif; ?>

    <form method="post" action="post_vacancy.php">
        <div class="form-group">
            <label for="title">Title</label>
            <input type="text" id="title" name="title" required>
        </div>

        <div class="form-group">
            <label for="location">Location</label>
            <input type="text" id="location" name="location" required>
        </div>

        <div class="form-group">
            <label for="rent">Monthly Rent (৳)</label>
            <input type="number" id="rent" name="rent" min="1" required>
        </div>

        <button type="submit" class="btn">Post Vacancy</button>
    </form>
</div>
</body>
</html>
