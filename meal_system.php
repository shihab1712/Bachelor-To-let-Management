<?php
// meal_system.php - Main page for meal management (Bachelor user)

session_start();
require 'db.php';



$username = $_SESSION['username'];

// Fetch user ID
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$user_id = $user['id'];

// Insert Meal Record
if (isset($_POST['add_meal'])) {
    $meal_date = $_POST['meal_date'];
    $meal_count = $_POST['meal_count'];

    $insert = $conn->prepare("INSERT INTO meals (user_id, meal_date, meal_count) VALUES (?, ?, ?)");
    $insert->bind_param("isi", $user_id, $meal_date, $meal_count);
    $insert->execute();
}

// Insert Shopping Record
if (isset($_POST['add_shopping'])) {
    $shopping_date = $_POST['shopping_date'];
    $amount = $_POST['amount'];

    $insert = $conn->prepare("INSERT INTO shopping (user_id, shopping_date, amount) VALUES (?, ?, ?)");
    $insert->bind_param("isd", $user_id, $shopping_date, $amount);
    $insert->execute();
}

// Fetch meal summary
$meal_summary = $conn->query("SELECT SUM(meal_count) as total_meals FROM meals WHERE user_id = $user_id")->fetch_assoc();
$shopping_summary = $conn->query("SELECT SUM(amount) as total_shopping FROM shopping WHERE user_id = $user_id")->fetch_assoc();

$total_meals = $meal_summary['total_meals'] ?? 0;
$total_shopping = $shopping_summary['total_shopping'] ?? 0.00;

// You may add cost-per-meal and due calculation here later
?>
<!DOCTYPE html>
<html>
<head>
    <title>Meal System</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f1f8e9;
            padding: 30px;
        }
        .container {
            max-width: 900px;
            margin: auto;
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 0 10px #ccc;
        }
        h2 {
            text-align: center;
            color: #33691e;
        }
        form {
            margin: 20px 0;
        }
        label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
        }
        input[type="date"], input[type="number"] {
            padding: 8px;
            width: 100%;
            margin-bottom: 10px;
        }
        button {
            padding: 10px 20px;
            background-color: #558b2f;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .summary {
            margin-top: 30px;
            background-color: #f0f4c3;
            padding: 15px;
            border-radius: 8px;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Meal Management System</h2>

    <form method="POST">
        <h3>Add Meal</h3>
        <label>Date:</label>
        <input type="date" name="meal_date" required>

        <label>Meal Count:</label>
        <input type="number" name="meal_count" min="0" required>

        <button type="submit" name="add_meal">Add Meal</button>
    </form>

    <form method="POST">
        <h3>Add Shopping</h3>
        <label>Date:</label>
        <input type="date" name="shopping_date" required>

        <label>Amount:</label>
        <input type="number" step="0.01" name="amount" required>

        <button type="submit" name="add_shopping">Add Shopping</button>
    </form>

    <div class="summary">
        <h3>Your Summary</h3>
        <p><strong>Total Meals:</strong> <?php echo $total_meals; ?></p>
        <p><strong>Total Shopping:</strong> ৳<?php echo number_format($total_shopping, 2); ?></p>
    </div>

    <div style="margin-top: 20px; text-align: center;">
        <a href="home.php" style="text-decoration: none; color: #33691e;">← Back to Home</a>
    </div>
</div>
</body>
</html>
