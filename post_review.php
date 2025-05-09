<?php
session_start();
require 'db.php';


if (!isset($_SESSION['username'])) {
    header("Location: index.html");
    exit();
}


// Fetch bachelor id
$username = $_SESSION['username'];
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND user_type = 'Bachelor'");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$bachelor_id = $user['id'] ?? null;


// Fetch properties for dropdown
$properties = $conn->query("SELECT id, location FROM properties");


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $property_id = $_POST['property_id'];
    $outer_env = $_POST['outer_env'];
    $landlord_mgmt = $_POST['landlord_mgmt'];
    $room_condition = $_POST['room_condition'];
    $amenities = $_POST['amenities'];
    $bachelor_friendly = $_POST['bachelor_friendly'];
    $location_access = $_POST['location_access'];
    $value_money = $_POST['value_money'];
    $review_text = $_POST['review_text'];


    $stmt = $conn->prepare("INSERT INTO property_reviews (bachelor_id, property_id, outer_env, landlord_mgmt, room_condition, amenities, bachelor_friendly, location_access, value_money, review_text) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iiiiiiiiis", $bachelor_id, $property_id, $outer_env, $landlord_mgmt, $room_condition, $amenities, $bachelor_friendly, $location_access, $value_money, $review_text);
    $stmt->execute();


    $msg = "Review submitted successfully!";
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Post Review</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f0f8ff;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #333;
        }
        label {
            font-weight: bold;
        }
        select, textarea, input[type=number] {
            width: 100%;
            margin-bottom: 15px;
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        button {
            background-color: #00796b;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .message {
            text-align: center;
            color: green;
            font-weight: bold;
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
    <h2>Post Property Review</h2>
    <?php if (isset($msg)) echo "<p class='message'>$msg</p>"; ?>
    <form method="POST">
        <label>Property Location</label>
        <select name="property_id" required>
            <?php while($prop = $properties->fetch_assoc()): ?>
                <option value="<?= $prop['id'] ?>"><?= htmlspecialchars($prop['location']) ?></option>
            <?php endwhile; ?>
        </select>


        <?php
        $criteria = [
            'outer_env' => 'Outer Environment',
            'landlord_mgmt' => 'Landlord/Management',
            'room_condition' => 'Room/Flat Condition',
            'amenities' => 'Amenities & Facilities',
            'bachelor_friendly' => 'Bachelor-Friendliness',
            'location_access' => 'Accessibility & Location',
            'value_money' => 'Value for Money'
        ];
        foreach ($criteria as $field => $label): ?>
            <label><?= $label ?></label>
            <select name="<?= $field ?>" required>
                <option value="">-- Select Rating --</option>
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <option value="<?= $i ?>"><?= str_repeat('★', $i) ?></option>
                <?php endfor; ?>
            </select>
        <?php endforeach; ?>


        <label>Additional Comments</label>
        <textarea name="review_text" rows="4" placeholder="Write your review here..."></textarea>


        <button type="submit">Submit Review</button>
    </form>
</div>
</body>
</html>
