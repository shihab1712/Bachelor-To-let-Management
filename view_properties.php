<?php
session_start();
require 'db.php';

if (!isset($_SESSION['username'])) {
    header("Location: index.html");
    exit();
}

$username = $_SESSION['username'];

// Check if user has active rental
$activeRental = $conn->prepare("
    SELECT * FROM rental_requests 
    WHERE bachelor_username = ? 
    AND status = 'Approved'
");
$activeRental->bind_param("s", $username);
$activeRental->execute();
$hasActiveRental = $activeRental->get_result()->num_rows > 0;

if ($hasActiveRental) {
    header("Location: manage_rental.php");
    exit();
}

$username = $_SESSION['username'];

// Get any pending requests for this bachelor
$pendingRequests = [];
if (isset($_SESSION['username'])) {
    $requestCheck = $conn->prepare("
        SELECT property_id 
        FROM rental_requests 
        WHERE bachelor_username = ? AND status IN ('Pending', 'Approved')
    ");
    $requestCheck->bind_param("s", $username);
    $requestCheck->execute();
    $result = $requestCheck->get_result();
    while ($row = $result->fetch_assoc()) {
        $pendingRequests[] = $row['property_id'];
    }
}

// Build search filters
$where = ["status = 'Available'", "rooms > 0"];
$params = [];
$types = "";

if (!empty($_GET['location'])) {
    $where[] = "location LIKE ?";
    $params[] = "%" . $_GET['location'] . "%";
    $types .= "s";
}
if (!empty($_GET['min_rent'])) {
    $where[] = "rent >= ?";
    $params[] = $_GET['min_rent'];
    $types .= "d";
}
if (!empty($_GET['max_rent'])) {
    $where[] = "rent <= ?";
    $params[] = $_GET['max_rent'];
    $types .= "d";
}
if (!empty($_GET['rooms'])) {
    $where[] = "rooms = ?";
    $params[] = $_GET['rooms'];
    $types .= "i";
}

$sql = "SELECT * FROM properties";
if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
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
        form.search-form {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }
        form.search-form input, form.search-form select, form.search-form button {
            padding: 8px;
            border: 1px solid #90caf9;
            border-radius: 5px;
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
        .action-btn {
            display: inline-block;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            font-weight: bold;
            text-decoration: none;
            cursor: pointer;
            transition: background-color 0.3s;
            margin: 5px;
        }
        .rent-btn {
            background-color: #00796b;
            color: white;
        }
        .review-btn {
            background-color: #00796b;
            color: white;
        }
        .rent-btn:hover, .review-btn:hover {
            background-color: #004d40;
        }
        .fully-occupied {
            color: red;
            font-weight: bold;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .alert.success {
            background-color: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #a5d6a7;
        }
        .alert.error {
            background-color: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }
        .pending-request {
            color: #f57c00;
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="container">
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert success">
            <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert error">
            <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
            ?>
        </div>
    <?php endif; ?>

    <div class="back-link"><a href="home.php">← Back to Home</a></div>
    <h2>Available Properties for Rent</h2>
    <form method="get" class="search-form">
        <input type="text" name="location" placeholder="Location" value="<?= htmlspecialchars($_GET['location'] ?? '') ?>">
        <input type="number" name="min_rent" placeholder="Min Rent" value="<?= htmlspecialchars($_GET['min_rent'] ?? '') ?>">
        <input type="number" name="max_rent" placeholder="Max Rent" value="<?= htmlspecialchars($_GET['max_rent'] ?? '') ?>">
        <input type="number" name="rooms" placeholder="Rooms" value="<?= htmlspecialchars($_GET['rooms'] ?? '') ?>">
        <button type="submit">Search</button>
    </form>
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
                    <th>Reviews</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($properties as $property): ?>
                    <tr>
                        <td><?= htmlspecialchars($property['location']); ?></td>
                        <td><?= htmlspecialchars($property['rent']); ?></td>
                        <td><?= htmlspecialchars($property['rooms']); ?></td>
                        <td><?= htmlspecialchars($property['features']); ?></td>
                        <td><?= htmlspecialchars($property['status']); ?></td>
                        <td>
                            <?php if ($property['rooms'] > 0): ?>
                                <?php if (!in_array($property['id'], $pendingRequests)): ?>
                                    <form method="post" action="rent_now.php" style="display:inline;">
                                        <input type="hidden" name="property_id" value="<?= htmlspecialchars($property['id']); ?>">
                                        <button type="submit" class="action-btn rent-btn">Rent Now</button>
                                    </form>
                                <?php else: ?>
                                    <span class="pending-request">Request Pending</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="fully-occupied">Fully Occupied</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="property_reviews.php?property_id=<?= $property['id'] ?>" 
                               class="action-btn review-btn">
                                Property Reviews
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No available properties found matching your search.</p>
    <?php endif; ?>
</div>
</body>
</html>
