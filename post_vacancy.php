<?php
session_start();
require 'db.php';

// if (!isset($_SESSION['username']) || $_SESSION['user_type'] !== 'Bachelor') {
//     header("Location: add_property.php");
//     exit();
// }

$username = $_SESSION['username'];
$successMsg = '';
$errorMsg = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $location = trim($_POST['location']);
    $rent = $_POST['rent'];
    $seats = $_POST['seats_available'];

    if ($location && $rent && $seats) {
        $stmt = $conn->prepare("INSERT INTO vacancies (bachelor_username, location, rent, seats_available) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssdi", $username, $location, $rent, $seats);

        if ($stmt->execute()) {
            $successMsg = "Vacancy posted successfully!";
        } else {
            $errorMsg = "Failed to post vacancy.";
        }
    } else {
        $errorMsg = "All fields are required.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Post a Vacancy</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(to right, #fff3e0, #ffe0b2);
            padding: 20px;
        }

        .container {
            max-width: 600px;
            margin: auto;
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            color: #ef6c00;
        }

        label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
        }

        input[type="text"], input[type="number"] {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .btn {
            margin-top: 20px;
            background-color: #ef6c00;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        .btn:hover {
            background-color: #e65100;
        }

        .message {
            text-align: center;
            margin-top: 15px;
            font-weight: bold;
        }

        .success {
            color: green;
        }

        .error {
            color: red;
        }

        .back-link {
            text-align: right;
            margin-bottom: 10px;
        }

        .back-link a {
            color: #bf360c;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="back-link"><a href="home.php">← Back to Home</a></div>
    <h2>Post Room Vacancy</h2>
    <form method="post">
        <label>Location</label>
        <input type="text" name="location" required>

        <label>Monthly Rent</label>
        <input type="number" name="rent" step="0.01" required>

        <label>Number of Seats Available</label>
        <input type="number" name="seats_available" required>

        <button type="submit" class="btn">Post Vacancy</button>
    </form>

    <?php if ($successMsg): ?>
        <p class="message success"><?php echo $successMsg; ?></p>
    <?php elseif ($errorMsg): ?>
        <p class="message error"><?php echo $errorMsg; ?></p>
    <?php endif; ?>
</div>

</body>
</html>
