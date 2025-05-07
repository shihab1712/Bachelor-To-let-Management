<?php
session_start();
require 'db.php';

// if (!isset($_SESSION['username']) || $_SESSION['user_type'] !== 'Bachelor') {
//     header("Location: index.html");
//     exit();
// }

$username = $_SESSION['username'];

$query = $conn->prepare("SELECT * FROM vacancies WHERE bachelor_username = ?");
$query->bind_param("s", $username);
$query->execute();
$result = $query->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Posted Vacancies</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f0f8ff; padding: 20px; }
        h2 { text-align: center; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background-color: #007BFF; color: white; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        .back { margin-top: 20px; display: inline-block; color: #007BFF; text-decoration: none; }
    </style>
</head>
<body>

<div style="text-align: right; margin-bottom: 20px;">
    <a href="home.php" style="
        padding: 10px 20px;
        background-color: #00796b;
        color: white;
        text-decoration: none;
        border-radius: 6px;
        font-weight: bold;
    ">
        ← Back to Home
    </a>
</div>

<h2>Your Posted Vacancies</h2>

<table>
    <tr>
        <th>Location</th>
        <th>Monthly Rent</th>
        <th>No. of Seats</th>
        <th>Posted On</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= htmlspecialchars($row['location']) ?></td>
        <td><?= htmlspecialchars($row['rent']) ?></td>
        <td><?= htmlspecialchars($row['seats_available']) ?></td>
        <td><?= date("d M Y", strtotime($row['created_at'])) ?></td>
    </tr>
    <?php endwhile; ?>
</table>

<a class="back" href="home.php">← Back to Home</a>

</body>
</html>
