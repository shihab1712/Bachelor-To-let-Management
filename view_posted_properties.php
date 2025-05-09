<?php
session_start();
require 'db.php';



$username = $_SESSION['username'];

// Fetch properties posted by the owner
$stmt = $conn->prepare("SELECT * FROM properties WHERE owner_username = ? ORDER BY created_at DESC");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$properties = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Posted Properties</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(to right, #e8f5e9, #a5d6a7);
            padding: 20px;
        }

        .container {
            max-width: 1000px;
            margin: auto;
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            color: #2e7d32;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
        }

        th, td {
            padding: 12px;
            border: 1px solid #a5d6a7;
            text-align: left;
        }

        th {
            background-color: #388e3c;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #e8f5e9;
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

        .btn-delete {
            padding: 8px 15px;
            background-color: #f44336;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .btn-delete:hover {
            background-color: #d32f2f;
        }
        
        .has-tenant {
            color: #f57c00;
            font-style: italic;
        }
    </style>
    <script>
    function confirmDelete() {
        return confirm('Are you sure you want to delete this property? This action cannot be undone.');
    }
    </script>
</head>
<body>

<div class="container">
    <div class="back-link"><a href="home.php">← Back to Home</a></div>
    <h2>Your Posted Properties</h2>

    <?php if (count($properties) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Location</th>
                    <th>Monthly Rent</th>
                    <th>Rooms</th>
                    <th>Features</th>
                    <th>Rent Status</th>
                    <th>Posted On</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($properties as $prop): ?>
                    <tr>
                        <td><?= htmlspecialchars($prop['location']); ?></td>
                        <td><?= htmlspecialchars($prop['rent']); ?></td>
                        <td><?= htmlspecialchars($prop['rooms']); ?></td>
                        <td><?= htmlspecialchars($prop['features']); ?></td>
                        <td><?= htmlspecialchars($prop['status']); ?></td>
                        <td><?= date("d M Y", strtotime($prop['created_at'])); ?></td>
                        <td>
                            <a href="edit_property.php?id=<?= $prop['id'] ?>">Edit</a> |
                            <?php
                            // Add this check before displaying the delete button
                            $checkRentals = $conn->prepare("
                                SELECT COUNT(*) as active_rentals 
                                FROM rental_requests 
                                WHERE property_id = ? AND status = 'Approved'
                            ");
                            $checkRentals->bind_param("i", $prop['id']);
                            $checkRentals->execute();
                            $rentalResult = $checkRentals->get_result();
                            $activeRentals = $rentalResult->fetch_assoc()['active_rentals'];
                            ?>
                            <td>
                                <?php if ($activeRentals == 0): ?>
                                    <form method="post" action="delete_property.php" style="display:inline;" onsubmit="return confirmDelete()">
                                        <input type="hidden" name="property_id" value="<?= $prop['id'] ?>">
                                        <button type="submit" class="btn-delete">Delete</button>
                                    </form>
                                <?php else: ?>
                                    <span class="has-tenant" title="Cannot delete property with active tenants">
                                        Has Active Tenant
                                    </span>
                                <?php endif; ?>
                            </td>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No properties posted yet.</p>
    <?php endif; ?>
</div>

</body>
</html>
