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
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(to right, #e0f7fa, #80deea);
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 450px;
            margin: 60px auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 0 16px rgba(0,0,0,0.10);
            padding: 35px 30px 30px 30px;
        }
        .back-link {
            margin-bottom: 18px;
            text-align: right;
        }
        .back-link a {
            color: #00796b;
            text-decoration: none;
            font-weight: bold;
            font-size: 15px;
        }
        .back-link a:hover {
            text-decoration: underline;
        }
        h2 {
            text-align: center;
            color: #00796b;
            margin-bottom: 25px;
        }
        form div {
            margin-bottom: 18px;
        }
        label {
            display: block;
            margin-bottom: 7px;
            color: #00796b;
            font-weight: 500;
        }
        input[type="text"],
        input[type="number"] {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #b2ebf2;
            border-radius: 7px;
            font-size: 15px;
            background: #f1fafd;
            transition: border 0.2s;
        }
        input[type="text"]:focus,
        input[type="number"]:focus {
            border: 1.5px solid #00796b;
            outline: none;
            background: #e0f7fa;
        }
        button[type="submit"] {
            width: 100%;
            padding: 12px;
            background: #00796b;
            color: #fff;
            border: none;
            border-radius: 7px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.2s;
        }
        button[type="submit"]:hover {
            background: #004d40;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="back-link">
        <a href="posted_ad.php">&larr; Back to Posted Ads</a>
    </div>
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