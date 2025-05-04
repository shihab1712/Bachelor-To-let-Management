<?php
session_start();
require 'db.php';

if (!isset($_SESSION['username'])) {
    header("Location: index.html");
    exit();
}

$username = $_SESSION['username'];
$successMsg = "";
$errorMsg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $location = $_POST['location'];
    $rent = $_POST['rent'];
    $rooms = $_POST['rooms'];
    $features = $_POST['features'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("INSERT INTO properties (owner_username, location, rent, rooms, features, status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssdss", $username, $location, $rent, $rooms, $features, $status);

    if ($stmt->execute()) {
        $successMsg = "Property added successfully!";
    } else {
        $errorMsg = "Error adding property: " . $stmt->error;
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Property</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(to right, #e3f2fd, #90caf9);
            padding: 20px;
        }

        .container {
            max-width: 600px;
            background: white;
            margin: auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            color: #1565c0;
        }

        label {
            display: block;
            margin-top: 12px;
            font-weight: bold;
        }

        input[type="text"], input[type="number"], textarea, select {
            width: 100%;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #ccc;
            margin-top: 4px;
        }

        .btn {
            margin-top: 20px;
            padding: 12px 25px;
            background-color: #1565c0;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            width: 100%;
        }

        .btn:hover {
            background-color: #0d47a1;
        }

        .message {
            text-align: center;
            margin-top: 15px;
            font-weight: bold;
        }

        .success { color: green; }
        .error { color: red; }

        .back-link {
            text-align: right;
            margin-bottom: 10px;
        }

        .back-link a {
            color: #0d47a1;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="back-link"><a href="home.php">← Back to Home</a></div>
    <h2>Add Property</h2>
    <form method="post">
        <label>Location</label>
        <input type="text" name="location" required>

        <label>Monthly Rent</label>
        <input type="number" step="0.01" name="rent" required>

        <label>Number of Rooms</label>
        <input type="number" name="rooms" required>

        <label>Other Features</label>
        <textarea name="features" rows="4" placeholder="E.g., Furnished, AC, Parking..." required></textarea>

        <label>Status</label>
        <select name="status" required>
            <option value="Available">Available</option>
            <option value="Occupied">Occupied</option>
        </select>

        <button class="btn" type="submit">Add Property</button>
    </form>

    <?php if ($successMsg): ?>
        <p class="message success"><?php echo $successMsg; ?></p>
    <?php elseif ($errorMsg): ?>
        <p class="message error"><?php echo $errorMsg; ?></p>
    <?php endif; ?>
</div>

</body>
</html>
