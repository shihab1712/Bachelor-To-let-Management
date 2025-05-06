<?php
session_start();
require 'db.php';

// if (!isset($_SESSION['username']) || $_SESSION['user_type'] !== 'Owner') {
//     header("Location: index.html");
//     exit();
// }

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

    $update = $conn->prepare("UPDATE properties SET location=?, rent=?, rooms=?, features=?, status=?, rent_status=? WHERE id=? AND owner_username=?");
    $update->bind_param("sdisssis", $location, $rent, $rooms, $features, $status, $id, $username);
    
    if ($update->execute()) {
        $msg = "Updated successfully!";
        header("Location: view_posted_properties.php");
        exit();
    } else {
        $msg = "Failed to update.";
    }
}
?>

<!-- HTML Form -->
<!DOCTYPE html>
<html>
<head>
    <title>Edit Property</title>
</head>
<body>
<h2>Edit Property</h2>
<form method="post">
    Location: <input type="text" name="location" value="<?= htmlspecialchars($property['location']) ?>" required><br>
    Rent: <input type="number" step="0.01" name="rent" value="<?= $property['rent'] ?>" required><br>
    Rooms: <input type="number" name="rooms" value="<?= $property['rooms'] ?>" required><br>
    Features: <input type="text" name="features" value="<?= htmlspecialchars($property['features']) ?>"><br>
    Rent Status:
    <select name="status">
        <option value="Available" <?= $property['status'] === 'Available' ? 'selected' : '' ?>>Available</option>
        <option value="Occupied" <?= $property['status'] === 'Occupied' ? 'selected' : '' ?>>Occupied</option>
    </select><br>
    <button type="submit">Update</button>
</form>

<p style="color: green"><?= $msg ?></p>
</body>
</html>
