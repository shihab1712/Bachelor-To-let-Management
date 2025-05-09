<?php
session_start();
require 'db.php';


$owner = $_SESSION['username'];

// Modified query to use existing columns
$query = $conn->prepare("
    SELECT r.id, r.bachelor_id, r.bachelor_username, r.property_id, r.request_date, r.status,
           p.location, p.rent, p.rooms,
           u.email, u.phone_number as phone, u.nid
    FROM rental_requests r
    JOIN properties p ON r.property_id = p.id
    JOIN users u ON r.bachelor_username = u.username
    WHERE p.owner_username = ?
    ORDER BY r.request_date DESC
");
$query->bind_param("s", $owner);
$query->execute();
$result = $query->get_result();

// Add a new query to get current tenants
$tenantsQuery = $conn->prepare("
    SELECT r.bachelor_username, r.request_date,
           p.location, p.rent,
           u.email, u.phone_number as phone, u.nid
    FROM rental_requests r
    JOIN properties p ON r.property_id = p.id
    JOIN users u ON r.bachelor_username = u.username
    WHERE p.owner_username = ? AND r.status = 'Approved'
    ORDER BY r.request_date DESC
");
$tenantsQuery->bind_param("s", $owner);
$tenantsQuery->execute();
$tenants = $tenantsQuery->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Rental Requests & Current Tenants</title>
    <style>
        body { 
            font-family: Arial; 
            background: #eef7f9; 
            padding: 20px; 
        }
        .container {
            max-width: 1200px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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
            background-color: #00796b; 
            color: white; 
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .bachelor-info {
            margin-top: 5px;
            font-size: 0.9em;
            color: #666;
        }
        .status-pending {
            color: #f57c00;
            font-weight: bold;
        }
        .status-approved {
            color: #2e7d32;
            font-weight: bold;
        }
        .status-rejected {
            color: #c62828;
            font-weight: bold;
        }
        .section-divider {
            margin: 40px 0;
            border-top: 2px solid #00796b;
            padding-top: 20px;
        }
        
        .tenant-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            border-left: 4px solid #00796b;
        }
        
        .tenant-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-weight: bold;
        }
        
        .tenant-details {
            font-size: 0.9em;
            color: #666;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }
    </style>
</head>
<body>
<div class="container">
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
    <h2>Rental Requests for Your Properties</h2>

    <table>
        <tr>
            <th>Bachelor Details</th>
            <th>Property Location</th>
            <th>Rent</th>
            <th>Rooms</th>
            <th>Requested On</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td>
                <strong><?= htmlspecialchars($row['bachelor_username']) ?></strong>
                <div class="bachelor-info">
                    <div>NID: <?= htmlspecialchars($row['nid']) ?></div>
                    <div>Phone: <?= htmlspecialchars($row['phone']) ?></div>
                    <div>Email: <?= htmlspecialchars($row['email']) ?></div>
                </div>
            </td>
            <td><?= htmlspecialchars($row['location']) ?></td>
            <td><?= htmlspecialchars($row['rent']) ?></td>
            <td><?= htmlspecialchars($row['rooms']) ?></td>
            <td><?= date('Y-m-d H:i', strtotime($row['request_date'])) ?></td>
            <td>
                <span class="status-<?= strtolower($row['status']) ?>">
                    <?= htmlspecialchars($row['status']) ?>
                </span>
            </td>
            <td>
                <?php if ($row['status'] === 'Pending'): ?>
                    <form method="post" action="process_request.php" style="display:inline;">
                        <input type="hidden" name="request_id" value="<?= $row['id'] ?>">
                        <button type="submit" name="action" value="approve" 
                            onclick="return confirm('Are you sure you want to approve this request?')"
                            style="background-color: #4caf50; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; margin-right: 5px;">
                            Approve
                        </button>
                        <button type="submit" name="action" value="reject"
                            onclick="return confirm('Are you sure you want to reject this request?')"
                            style="background-color: #f44336; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer;">
                            Reject
                        </button>
                    </form>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>

    <!-- Add Current Tenants Section -->
    <div class="section-divider">
        <h2>Current Tenants</h2>
        
        <?php if ($tenants->num_rows > 0): ?>
            <?php while ($tenant = $tenants->fetch_assoc()): ?>
                <div class="tenant-card">
                    <div class="tenant-header">
                        <span>Property: <?= htmlspecialchars($tenant['location']) ?></span>
                        <span>Rent: ৳<?= htmlspecialchars($tenant['rent']) ?></span>
                    </div>
                    <div class="tenant-details">
                        <div>
                            <strong>Tenant:</strong> <?= htmlspecialchars($tenant['bachelor_username']) ?>
                        </div>
                        <div>
                            <strong>Since:</strong> <?= date('Y-m-d', strtotime($tenant['request_date'])) ?>
                        </div>
                        <div>
                            <strong>Phone:</strong> <?= htmlspecialchars($tenant['phone']) ?>
                        </div>
                        <div>
                            <strong>Email:</strong> <?= htmlspecialchars($tenant['email']) ?>
                        </div>
                        <div>
                            <strong>NID:</strong> <?= htmlspecialchars($tenant['nid']) ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No current tenants.</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
