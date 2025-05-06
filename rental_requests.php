<?php
session_start();
require 'db.php';

// if (!isset($_SESSION['username']) || $_SESSION['user_type'] !== 'Owner') {
//     header("Location: index.html");
//     exit();
// }

$owner = $_SESSION['username'];

$query = $conn->prepare("
    SELECT r.id, r.bachelor_id, r.bachelor_username, r.property_id, r.request_date, r.status, p.location, p.rent, p.rooms
    FROM rental_requests r
    JOIN properties p ON r.property_id = p.id
    WHERE p.owner_username = ?
    ORDER BY r.request_date DESC
");
$query->bind_param("s", $owner);
$query->execute();
$result = $query->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Rental Requests</title>
    <style>
        body { font-family: Arial; background: #eef7f9; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ccc; }
        th { background-color: #00796b; color: white; }
    </style>
</head>
<body>
<h2>Rental Requests for Your Properties</h2>

<table>
    <tr>
        <th>Bachelor</th>
        <th>Property Location</th>
        <th>Rent</th>
        <th>Rooms</th>
        <th>Requested On</th>
        <th>Status</th>
        <th>Actions</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= htmlspecialchars($row['bachelor_username']) ?></td>
        <td><?= htmlspecialchars($row['location']) ?></td>
        <td><?= htmlspecialchars($row['rent']) ?></td>
        <td><?= htmlspecialchars($row['rooms']) ?></td>
        <td><?= date("d M Y H:i", strtotime($row['request_date'])) ?></td>
        <td><?= htmlspecialchars($row['status'] ?? 'Pending') ?></td>
        <td>
            <?php if ($row['status'] === 'Pending'): ?>
            <form method="post" action="process_request.php" style="display:inline;">
                <input type="hidden" name="request_id" value="<?= $row['id'] ?>">
                <button name="action" value="approve">Approve</button>
            </form>
            <form method="post" action="process_request.php" style="display:inline;">
                <input type="hidden" name="request_id" value="<?= $row['id'] ?>">
                <button name="action" value="reject">Reject</button>
            </form>
            <?php else: ?>
                <em><?= $row['status'] ?></em>
            <?php endif; ?>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

</body>
</html>
