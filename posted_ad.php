<?php
session_start();
require 'db.php';

if (!isset($_SESSION['username'])) {
    header("Location: index.html");
    exit();
}

$username = $_SESSION['username'];

// Get properties posted by this bachelor
$query = $conn->prepare("
    SELECT * FROM vacancy_ads 
    WHERE bachelor_username = ?
    ORDER BY created_at DESC
");
$query->bind_param("s", $username);
$query->execute();
$ads = $query->get_result();

// Get rental requests for this bachelor's ads
$requestsQuery = $conn->prepare("
    SELECT vr.*, u.username as requester_username, u.phone_number, u.email, u.nid,
           va.title as ad_title, va.location
    FROM vacancy_requests vr
    JOIN users u ON vr.requester_id = u.id
    JOIN vacancy_ads va ON vr.ad_id = va.id
    WHERE va.bachelor_username = ?
    ORDER BY vr.request_date DESC
");
$requestsQuery->bind_param("s", $username);
$requestsQuery->execute();
$requests = $requestsQuery->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Posted Vacancy Ads</title>
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
        .ad-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            border-left: 4px solid #00796b;
        }
        .action-buttons {
            margin-top: 10px;
            display: flex;
            gap: 10px;
        }
        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            color: white;
            font-size: 14px;
        }
        .btn-edit {
            background-color: #0288d1;
        }
        .btn-delete {
            background-color: #f44336;
        }
        .btn-approve {
            background-color: #4caf50;
        }
        .btn-reject {
            background-color: #f44336;
        }
        .section-divider {
            margin: 40px 0;
            border-top: 2px solid #00796b;
            padding-top: 20px;
        }
        .requester-info {
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

    <h2>My Posted Vacancy Ads</h2>
    
    <?php if ($ads->num_rows > 0): ?>
        <?php while ($ad = $ads->fetch_assoc()): ?>
            <div class="ad-card">
                <h3><?= htmlspecialchars($ad['title']) ?></h3>
                <p><strong>Location:</strong> <?= htmlspecialchars($ad['location']) ?></p>
                <p><strong>Rent:</strong> ৳<?= htmlspecialchars($ad['rent']) ?></p>
                <p><strong>Status:</strong> <?= htmlspecialchars($ad['status']) ?></p>
                <div class="action-buttons">
                    <a href="edit_ad.php?id=<?= $ad['id'] ?>" class="btn btn-edit">Edit</a>
                    <button onclick="deleteAd(<?= $ad['id'] ?>)" class="btn btn-delete">Delete</button>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No ads posted yet.</p>
    <?php endif; ?>

    <!-- Rental Requests Section -->
    <div class="section-divider">
        <h2>Rental Requests</h2>
        
        <?php if ($requests->num_rows > 0): ?>
            <?php while ($request = $requests->fetch_assoc()): ?>
                <div class="ad-card">
                    <div class="requester-info">
                        <h4>Ad: <?= htmlspecialchars($request['ad_title']) ?></h4>
                        <p>Location: <?= htmlspecialchars($request['location']) ?></p>
                        <strong>Requester: <?= htmlspecialchars($request['requester_username']) ?></strong>
                        <div>Phone: <?= htmlspecialchars($request['phone_number']) ?></div>
                        <div>Email: <?= htmlspecialchars($request['email']) ?></div>
                        <div>NID: <?= htmlspecialchars($request['nid']) ?></div>
                    </div>
                    <div style="margin-top: 10px;">
                        <strong>Status: </strong>
                        <span class="status-<?= strtolower($request['status']) ?>">
                            <?= htmlspecialchars($request['status']) ?>
                        </span>
                    </div>
                    <?php if ($request['status'] === 'Pending'): ?>
                        <div class="action-buttons">
                            <form method="post" action="process_vacancy_request.php" style="display:inline;">
                                <input type="hidden" name="request_id" value="<?= $request['id'] ?>">
                                <button type="submit" name="action" value="approve" 
                                    onclick="return confirm('Are you sure you want to approve this request?')"
                                    class="btn btn-approve">
                                    Approve
                                </button>
                                <button type="submit" name="action" value="reject"
                                    onclick="return confirm('Are you sure you want to reject this request?')"
                                    class="btn btn-reject">
                                    Reject
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No rental requests yet.</p>
        <?php endif; ?>
    </div>
</div>

<script>
function deleteAd(adId) {
    if (confirm('Are you sure you want to delete this ad? This action cannot be undone.')) {
        window.location.href = `delete_ad.php?id=${adId}`;
    }
}
</script>
</body>
</html>
