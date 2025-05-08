<?php
session_start();
require 'db.php';


if (!isset($_SESSION['username'])) {
    header("Location: index.html");
    exit();
}


// Get bachelor ID
$username = $_SESSION['username'];
$user_stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$user_stmt->bind_param("s", $username);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();
$bachelor_id = $user['id'] ?? 0;


// Fetch all reviews and join with property location
$sql = "SELECT pr.*, p.location
        FROM property_reviews pr
        JOIN properties p ON pr.property_id = p.id";


$result = $conn->query($sql);
if (!$result) {
    die("Error retrieving reviews: " . $conn->error);
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Property Reviews</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(to right, #fce4ec, #f8bbd0);
            padding: 20px;
        }
        .container {
            max-width: 1000px;
            margin: auto;
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #880e4f;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 12px;
            text-align: center;
        }
        th {
            background-color: #f8bbd0;
            color: #880e4f;
        }
        tr:nth-child(even) {
            background-color: #fce4ec;
        }
        .back-btn {
            display: block;
            margin-bottom: 15px;
            text-align: right;
        }
        .back-btn a {
            text-decoration: none;
            background-color: #880e4f;
            color: white;
            padding: 8px 14px;
            border-radius: 6px;
        }
    </style>
</head>
<body>


<div class="container">
    <div class="back-btn">
        <a href="home.php">← Back to Home</a>
    </div>


    <h2>All Property Reviews</h2>


    <table>
        <thead>
            <tr>
                <th>Location</th>
                <th>Outer Environment</th>
                <th>Landlord</th>
                <th>Room Condition</th>
                <th>Amenities</th>
                <th>Bachelor-Friendly</th>
                <th>Accessibility</th>
                <th>Value</th>
                <th>Comment</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['location']) ?></td>
                    <td><?= str_repeat("★", $row['outer_env']) ?></td>
                    <td><?= str_repeat("★", $row['landlord_mgmt']) ?></td>
                    <td><?= str_repeat("★", $row['room_condition']) ?></td>
                    <td><?= str_repeat("★", $row['amenities']) ?></td>
                    <td><?= str_repeat("★", $row['bachelor_friendly']) ?></td>
                    <td><?= str_repeat("★", $row['location_access']) ?></td>
                    <td><?= str_repeat("★", $row['value_money']) ?></td>
                    <td><?= nl2br(htmlspecialchars($row['review_text'])) ?></td>
                    <td><?= date("M d, Y", strtotime($row['created_at'])) ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>


</body>
</html>
