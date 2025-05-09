<?php
session_start();
require 'db.php';


$id = $_GET['id'];
$username = $_SESSION['username'];
$msg = '';

// Fetch property
$stmt = $conn->prepare("SELECT * FROM properties WHERE id = ? AND owner_username = ?");
$stmt->bind_param("is", $id, $username);
$stmt->execute();
$result = $stmt->get_result();
$property = $result->fetch_assoc();

if (!$property) {
    die("Property not found or you don't have permission.");
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $location = $_POST['location'];
    $rent = $_POST['rent'];
    $rooms = $_POST['rooms'];
    $features = $_POST['features'];
    $status = $_POST['status'];

    $update = $conn->prepare("UPDATE properties SET location=?, rent=?, rooms=?, features=?, status=? WHERE id=? AND owner_username=?");
    $update->bind_param("sdsssis", $location, $rent, $rooms, $features, $status, $id, $username);
    
    if ($update->execute()) {
        $msg = "Updated successfully!";
        header("Location: view_posted_properties.php");
        exit();
    } else {
        $msg = "Failed to update.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Property</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(to right, #f1f8e9, #dcedc8);
            padding: 40px;
        }

        .container {
            max-width: 600px;
            background: white;
            padding: 30px;
            margin: auto;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            color: #558b2f;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            margin-top: 15px;
            font-weight: bold;
        }

        input, select {
            padding: 10px;
            border: 1px solid #c5e1a5;
            border-radius: 5px;
            margin-top: 5px;
        }

        button {
            margin-top: 20px;
            padding: 10px;
            background-color: #7cb342;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background-color: #558b2f;
        }

        .message {
            text-align: center;
            color: green;
            margin-top: 15px;
        }

        .back-link {
            text-align: center;
            margin-bottom: 20px;
        }

        .back-link a {
            color: #33691e;
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="back-link"><a href="view_posted_properties.php">← Back to My Properties</a></div>
    <h2>Edit Property</h2>

    <form method="post">
        <label>Location:</label>
        <input type="text" name="location" value="<?= htmlspecialchars($property['location']) ?>" required>

        <label>Rent:</label>
        <input type="number" step="0.01" name="rent" value="<?= $property['rent'] ?>" required>

        <label>Rooms:</label>
        <input type="number" name="rooms" value="<?= $property['rooms'] ?>" required>

        <label>Features:</label>
        <input type="text" name="features" value="<?= htmlspecialchars($property['features']) ?>">

        <label>Rent Status:</label>
        <select name="status">
            <option value="Available" <?= $property['status'] === 'Available' ? 'selected' : '' ?>>Available</option>
            <option value="Occupied" <?= $property['status'] === 'Occupied' ? 'selected' : '' ?>>Occupied</option>
        </select>

        <button type="submit">Update</button>
    </form>

    <?php if (!empty($msg)): ?>
        <p class="message"><?= htmlspecialchars($msg) ?></p>
    <?php endif; ?>
</div>

</body>
</html>
