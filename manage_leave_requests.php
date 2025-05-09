<?php
session_start();
require 'db.php';


$owner_username = $_SESSION['username'];

// Get all leave requests for properties owned by this owner
$query = $conn->prepare("
    SELECT lr.*, r.property_id, p.location, p.rooms
    FROM leave_requests lr
    JOIN rental_requests r ON lr.rental_id = r.id
    JOIN properties p ON r.property_id = p.id
    WHERE p.owner_username = ? AND lr.status = 'Pending'
    ORDER BY lr.request_date DESC
");
$query->bind_param("s", $owner_username);
$query->execute();
$result = $query->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Leave Requests</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(to right, #e1f5fe, #b3e5fc);
            padding: 20px;
        }
        .container {
            max-width: 1000px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #0288d1;
            color: white;
        }
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            color: white;
            margin: 0 5px;
        }
        .btn-approve {
            background-color: #4caf50;
        }
        .btn-reject {
            background-color: #f44336;
        }
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #0288d1;
            text-decoration: none;
        }
    </style>
</head>
<body>
<div class="container">
    <a href="home.php" class="back-link">← Back to Home</a>
    <h2>Manage Leave Requests</h2>

    <?php if ($result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Bachelor</th>
                    <th>Property Location</th>
                    <th>Request Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['bachelor_username']) ?></td>
                        <td><?= htmlspecialchars($row['location']) ?></td>
                        <td><?= date('Y-m-d H:i', strtotime($row['request_date'])) ?></td>
                        <td>
                            <form method="post" action="process_leave_request.php" style="display:inline;">
                                <input type="hidden" name="leave_request_id" value="<?= $row['id'] ?>">
                                <input type="hidden" name="rental_id" value="<?= $row['rental_id'] ?>">
                                <input type="hidden" name="property_id" value="<?= $row['property_id'] ?>">
                                <button type="submit" name="action" value="approve" class="btn btn-approve">Approve</button>
                                <button type="submit" name="action" value="reject" class="btn btn-reject">Reject</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No pending leave requests.</p>
    <?php endif; ?>
</div>
</body>
</html>