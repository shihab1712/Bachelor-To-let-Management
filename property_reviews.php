<?php
session_start();
require 'db.php';



if (!isset($_GET['property_id'])) {
    die("Property ID is missing.");
}

$property_id = $_GET['property_id'];

// Fetch property info
$prop_stmt = $conn->prepare("SELECT * FROM properties WHERE id = ?");
$prop_stmt->bind_param("i", $property_id);
$prop_stmt->execute();
$prop_result = $prop_stmt->get_result();
$property = $prop_result->fetch_assoc();

if (!$property) {
    die("Property not found.");
}

// Fetch reviews
$review_stmt = $conn->prepare("
    SELECT r.outer_env, r.landlord_mgmt, r.room_condition, r.amenities, r.bachelor_friendly,
           r.location_access, r.value_money, r.review_text, r.created_at, u.username
    FROM property_reviews r
    JOIN users u ON r.bachelor_id = u.id
    WHERE r.property_id = ?
    ORDER BY r.created_at DESC
");
$review_stmt->bind_param("i", $property_id);
$review_stmt->execute();
$reviews = $review_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Property Reviews</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(to right, #f1f8e9, #c8e6c9);
            padding: 30px;
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
            color: #2e7d32;
        }

        .property-info {
            background: #e8f5e9;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .review {
            border: 1px solid #a5d6a7;
            padding: 20px;
            margin-bottom: 15px;
            border-radius: 10px;
            background: #f9fbe7;
        }

        .review strong {
            color: #2e7d32;
        }

        .review p {
            margin: 6px 0;
        }

        .no-review {
            text-align: center;
            color: #555;
            font-style: italic;
        }

        .back-link {
            margin-bottom: 20px;
        }

        .back-link a {
            color: #1b5e20;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="back-link"><a href="properties_ad.php">← Back to Browse Properties</a></div>
    <h2>Reviews for Property in <?= htmlspecialchars($property['location']) ?></h2>

    <div class="property-info">
        <p><strong>Rent:</strong> <?= htmlspecialchars($property['rent']) ?> BDT</p>
        <p><strong>Rooms:</strong> <?= htmlspecialchars($property['rooms']) ?></p>
        <p><strong>Features:</strong> <?= htmlspecialchars($property['features']) ?></p>
    </div>

    <?php if ($reviews->num_rows > 0): ?>
        <?php while ($row = $reviews->fetch_assoc()): ?>
            <div class="review">
                <p><strong>Reviewer:</strong> <?= htmlspecialchars($row['username']) ?></p>
                <p><strong>Outer Environment:</strong> <?= $row['outer_env'] ?>/5</p>
                <p><strong>Landlord/Management:</strong> <?= $row['landlord_mgmt'] ?>/5</p>
                <p><strong>Room/Flat Condition:</strong> <?= $row['room_condition'] ?>/5</p>
                <p><strong>Amenities & Facilities:</strong> <?= $row['amenities'] ?>/5</p>
                <p><strong>Bachelor-Friendliness:</strong> <?= $row['bachelor_friendly'] ?>/5</p>
                <p><strong>Accessibility & Location:</strong> <?= $row['location_access'] ?>/5</p>
                <p><strong>Value for Money:</strong> <?= $row['value_money'] ?>/5</p>
                <p><strong>Review:</strong> <?= nl2br(htmlspecialchars($row['review_text'])) ?></p>
                <p><em>Posted on <?= $row['created_at'] ?></em></p>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p class="no-review">No reviews yet for this property.</p>
    <?php endif; ?>
</div>

</body>
</html>
