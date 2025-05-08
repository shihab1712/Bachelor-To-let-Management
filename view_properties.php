<?php
session_start();
require 'db.php';

if (!isset($_SESSION['username'])) {
    header("Location: index.html");
    exit();
}

$username = $_SESSION['username'];

// Build search filters
$where = ["status = 'Available'"];
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
            padding: 8px 15px;
            background-color: #0288d1;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
    </style>
</head>
<body>
<div class="container">
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
                            <form method="post" action="rent_now.php" style="display:inline;">
                                <input type="hidden" name="property_id" value="<?= htmlspecialchars($property['id']); ?>">
                                <button type="submit" class="btn-rent">Rent Now!</button>
                            </form>
                        </td>
                        <td>
                            <a href="property_reviews.php?property_id=<?= $property['id'] ?>" class="btn-review">Property Review</a>
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
