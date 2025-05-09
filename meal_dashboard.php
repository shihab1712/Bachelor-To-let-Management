<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    // Not logged in or session expired
    header("Location: index.html");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Handle new person
if (isset($_POST['add_person'])) {
    $name = trim($_POST['person_name']);
    if ($name) {
        $stmt = $conn->prepare("INSERT INTO persons (name, user_id) VALUES (?, ?)");
        $stmt->bind_param("si", $name, $user_id);
        $stmt->execute();
    }
}

// Handle meal update
if (isset($_POST['update_meal'])) {
    $date = $_POST['meal_date'];
    foreach ($_POST['meal_counts'] as $person_id => $meal_count) {
        $stmt = $conn->prepare("INSERT INTO meals (person_id, user_id, meal_date, meal_count) VALUES (?, ?, ?, ?) 
                                ON DUPLICATE KEY UPDATE meal_count = ?");
        $stmt->bind_param("iisii", $person_id, $user_id, $date, $meal_count, $meal_count);
        $stmt->execute();
    }
}

// Handle shopping update
if (isset($_POST['update_shopping'])) {
    $date = $_POST['shopping_date'];
    foreach ($_POST['shopping_amounts'] as $person_id => $amount) {
        $stmt = $conn->prepare("INSERT INTO shopping (person_id, user_id, shopping_date, amount) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iisd", $person_id, $user_id, $date, $amount);
        $stmt->execute();
    }
}

// Reset all (person, meals, shopping)
if (isset($_POST['reset_all'])) {
    $conn->query("DELETE FROM meals WHERE user_id = $user_id");
    $conn->query("DELETE FROM shopping WHERE user_id = $user_id");
    $conn->query("DELETE FROM persons WHERE user_id = $user_id");
}

// Fetch persons
$persons = $conn->prepare("SELECT person_id, name FROM persons WHERE user_id = ?");
$persons->bind_param("i", $user_id);
$persons->execute();
$persons_result = $persons->get_result();
$person_list = $persons_result->fetch_all(MYSQLI_ASSOC);

// Calculate summary
$summary = [];
$total_meal = 0;
$total_cost = 0;

foreach ($person_list as $p) {
    $pid = $p['person_id'];
    $name = $p['name'];

    // Total meals
    $stmt = $conn->prepare("SELECT SUM(meal_count) FROM meals WHERE person_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $pid, $user_id);
    $stmt->execute();
    $stmt->bind_result($meal_sum);
    $stmt->fetch();
    $stmt->close();

    // Total cost
    $stmt = $conn->prepare("SELECT SUM(amount) FROM shopping WHERE person_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $pid, $user_id);
    $stmt->execute();
    $stmt->bind_result($cost_sum);
    $stmt->fetch();
    $stmt->close();

    $meal = $meal_sum ?: 0;
    $cost = $cost_sum ?: 0;

    $total_meal += $meal;
    $total_cost += $cost;

    $summary[] = [
        'name' => $name,
        'meals' => $meal,
        'cost' => $cost,
        'person_id' => $pid
    ];
}

$meal_rate = $total_meal > 0 ? $total_cost / $total_meal : 0;

?>

<!DOCTYPE html>
<html>
<head>
    <title>Meal Dashboard</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f6f8;
            padding: 30px;
        }

        .container {
            max-width: 1000px;
            margin: auto;
            background: #ffffff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        h2, h3 {
            color: #333;
            border-bottom: 2px solid #00796b;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }

        .section {
            margin-bottom: 40px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #fdfdfd;
        }

        .section h3 {
            margin-bottom: 10px;
            color: #00796b;
        }

        .section p {
            margin: 10px 0;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            background-color: #fff;
        }

        table thead {
            background-color: #b2dfdb;
        }

        table th, table td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: center;
        }

        input[type="text"],
        input[type="number"],
        input[type="date"] {
        width: 100%;
        padding: 8px;
        margin: 4px 0;
        box-sizing: border-box;
        border: 1px solid #ccc;
        border-radius: 4px;
    }

    input[type="checkbox"] {
        transform: scale(1.2);
    }

    .btn {
        padding: 10px 20px;
        background-color: #00796b;
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        margin-top: 10px;
    }

    .btn-red {
        background-color: #d32f2f;
    }

    .btn:hover {
        opacity: 0.9;
    }

    .top-right {
        text-align: right;
        margin-bottom: 20px;
    }

    td.negative {
        color: red;
    }

    td.positive {
    color: green;
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

    <h2>Meal Management Dashboard for <?php echo htmlspecialchars($username); ?></h2>

    <!-- Add person -->
    <form method="post">
        <h3>Add Person</h3>
        <input type="text" name="person_name" placeholder="Name" required>
        <button type="submit" name="add_person" class="btn">Add</button>
    </form>

    <!-- Update meals -->
    <form method="post">
        <h3>Update Meals</h3>
        <input type="date" name="meal_date" required>
        <?php foreach ($person_list as $p): ?>
            <label><?php echo $p['name']; ?></label>
            <input type="number" name="meal_counts[<?php echo $p['person_id']; ?>]" min="0" required>
        <?php endforeach; ?>
        <button type="submit" name="update_meal" class="btn">Update Meals</button>
    </form>

    <!-- Update shopping -->
    <form method="post">
        <h3>Update Shopping</h3>
        <input type="date" name="shopping_date" required>
        <?php foreach ($person_list as $p): ?>
            <label><?php echo $p['name']; ?></label>
            <input type="number" step="0.01" name="shopping_amounts[<?php echo $p['person_id']; ?>]" min="0" required>
        <?php endforeach; ?>
        <button type="submit" name="update_shopping" class="btn">Update Shopping</button>
    </form>

    <!-- Reset Button -->
    <form method="post">
        <button type="submit" name="reset_all" class="btn btn-red" onclick="return confirm('Are you sure you want to reset all meal and shopping data?');">Reset All</button>
    </form>

</div>
<div class="section"> 
    <h3>Meal & Shopping Summary</h3>
    <p><strong>Total Shopping:</strong> ৳<?= number_format($total_cost, 2) ?> |
       <strong>Total Meals:</strong> <?= $total_meal ?> |
       <strong>Meal Rate:</strong> ৳<?= number_format($meal_rate, 2) ?></p>

    <form method="post">
        <table>
            <thead>
                <tr>
                    <th>Cleared</th>
                    <th>Name</th>
                    <th>Total Meals</th>
                    <th>Total Shopping (৳)</th>
                    <th>Meal Rate (৳)</th>
                    <th>Give/Take (৳)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($summary as $row): 
                    $give_take = $row['cost'] - ($meal_rate * $row['meals']);
                ?>
                <tr>
                    <td><input type="checkbox" name="selected[]" value="<?= $row['person_id'] ?>"></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= $row['meals'] ?></td>
                    <td><?= number_format($row['cost'], 2) ?></td>
                    <td><?= number_format($meal_rate, 2) ?></td>
                    <td class="<?= $give_take < 0 ? 'negative' : 'positive' ?>">
                        <?= number_format($give_take, 2) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </form>
</div>


</body>
</html>
