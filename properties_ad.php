<?php
session_start();
require 'db.php';

if (!isset($_SESSION['username']) || $_SESSION['user_type'] !== 'Bachelor') {
    header("Location: view_properties.php");
    exit();
}

$username = $_SESSION['username'];

// Fetch all available properties
$stmt = $conn->prepare("SELECT * FROM properties WHERE status = 'Available'");
$stmt->execute();
$result = $stmt->get_result();
$properties = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Available Properties</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(to right, #e1f5fe, #b3e5fc);
            padding: 20px;
        }

        .container {
            max-width: 950px;
            margin: auto;
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            color: #0277bd;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
        }

        th, td {
            padding: 12px;
            border: 1px solid #b3e5fc;
            text-align: left;
        }

        th {
            background-color: #0288d1;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #e1f5fe;
        }

        .back-link {
            text-align: right;
            margin-bottom: 15px;
        }

        .back-link a {
            color: #01579b;
            text-decoration: none;
            font-weight: bold;
        }

        .btn-rent {
            padding: 8px 15px;
            background-color: #0288d1;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        .btn-rent:hover {
            background-color: #01579b;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="back-link"><a href="home.php">← Back to Home</a></div>
    <h2>Available Properties for Rent</h2>

    <?php if (count($properties) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Location</th>
                    <th>Monthly Rent</th>
                    <th>Rooms</th>
                    <th>Features</th>
                    <th>Status</th>
                    <th>Action</th>
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
                        <td><button class="btn-rent" disabled>Request to Rent</button></td> <!-- Placeholder -->
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No available properties at the moment.</p>
    <?php endif; ?>
</div>

</body>
</html>
