<?php
session_start();
require 'db.php';

if (!isset($_SESSION['username'])) {
    header("Location: index.html");
    exit();
}

$username = $_SESSION['username'];

// Fetch all properties
$stmt = $conn->prepare("SELECT * FROM properties");
$stmt->execute();
$result = $stmt->get_result();
$properties = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All Posted Properties</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(to right, #f1f8e9, #aed581);
            padding: 20px;
        }

        .container {
            max-width: 900px;
            margin: auto;
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            color: #33691e;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
        }

        th, td {
            border: 1px solid #c5e1a5;
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #8bc34a;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f1f8e9;
        }

        .back-link {
            text-align: right;
            margin-bottom: 15px;
        }

        .back-link a {
            color: #2e7d32;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="back-link"><a href="home.php">← Back to Home</a></div>
    <h2>All Posted Properties</h2>

    <?php if (count($properties) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Location</th>
                    <th>Monthly Rent</th>
                    <th>Rooms</th>
                    <th>Features</th>
                    <th>Status</th>
                    <th>Owner</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($properties as $property): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($property['location']); ?></td>
                        <td><?php echo htmlspecialchars($property['rent']); ?></td>
                        <td><?php echo htmlspecialchars($property['rooms']); ?></td>
                        <td><?php echo htmlspecialchars($property['features']); ?></td>
                        <td><?php echo htmlspecialchars($property['status']); ?></td>
                        <td><?php echo htmlspecialchars($property['owner_username']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No properties found in the database.</p>
    <?php endif; ?>
</div>

</body>
</html>
