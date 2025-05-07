<?php
session_start();
require 'db.php';

if (!isset($_SESSION['username'])) {
    header("Location: index.html");
    exit();
}

$maids = $conn->query("SELECT * FROM maids");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Available Maids</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #e0f7fa;
            margin: 20px;
        }

        h2 {
            text-align: center;
            color: #004d40;
        }

        table {
            width: 90%;
            margin: auto;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        th, td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #00796b;
            color: white;
        }

        tr:hover {
            background-color: #f1f1f1;
        }
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
    <h2>Available Maids</h2>
    <table>
        <tr>
            <th>Name</th>
            <th>Phone</th>
            <th>Location</th>
            <th>Experience</th>
            <th>Working Hours</th>
            <th>Availability</th>
        </tr>
        <?php while ($maid = $maids->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($maid['name']) ?></td>
            <td><?= htmlspecialchars($maid['phone']) ?></td>
            <td><?= htmlspecialchars($maid['location']) ?></td>
            <td><?= htmlspecialchars($maid['experience_years']) ?> years</td>
            <td><?= htmlspecialchars($maid['working_hours']) ?> hrs/day</td>
            <td><?= htmlspecialchars($maid['availability']) ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
