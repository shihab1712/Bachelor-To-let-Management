<?php
session_start();
require 'db.php';

if (!isset($_SESSION['username'])) {
    header("Location: index.html");
    exit();
}

$username = $_SESSION['username'];
$updateMsg = '';
$deleteMsg = '';

// Fetch user info
$query = $conn->prepare("SELECT * FROM users WHERE username = ?");
$query->bind_param("s", $username);
$query->execute();
$result = $query->get_result();
$user = $result->fetch_assoc();

// Handle update
if (isset($_POST['update'])) {
    $phone = $_POST['phone_number'];
    $location = $_POST['preferred_location'];

    $updateStmt = $conn->prepare("UPDATE users SET phone_number = ?, preferred_location = ? WHERE username = ?");
    $updateStmt->bind_param("sss", $phone, $location, $username);
    if ($updateStmt->execute()) {
        $updateMsg = "Profile updated successfully!";
        // Refresh user data
        $query->execute();
        $result = $query->get_result();
        $user = $result->fetch_assoc();
    } else {
        $updateMsg = "Failed to update profile.";
    }
}

// Handle delete
if (isset($_POST['delete'])) {
    $deleteStmt = $conn->prepare("DELETE FROM users WHERE username = ?");
    $deleteStmt->bind_param("s", $username);
    if ($deleteStmt->execute()) {
        session_destroy();
        header("Location: index.html");
        exit();
    } else {
        $deleteMsg = "Account deletion failed.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Profile</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(to right, #e0f7fa, #80deea);
            padding: 20px;
        }

        .container {
            max-width: 600px;
            margin: auto;
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            color: #00796b;
        }

        label {
            display: block;
            margin: 10px 0 4px;
            font-weight: bold;
        }

        input[type="text"], input[type="email"], select {
            width: 100%;
            padding: 8px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }

        .btn {
            margin-top: 15px;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
        }

        .btn-update {
            background-color: #00796b;
            color: white;
        }

        .btn-delete {
            background-color: #d32f2f;
            color: white;
        }

        .message {
            margin-top: 15px;
            text-align: center;
            color: green;
        }

        .error {
            color: red;
        }

        .top-link {
            text-align: right;
            margin-bottom: 10px;
        }

        .top-link a {
            color: #006064;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="top-link">
        <a href="home.php">← Back to Home</a>
    </div>
    <h2>Your Profile</h2>

    <form method="post">
        <label>Username</label>
        <input type="text" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>

        <label>Email</label>
        <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>

        <label>NID</label>
        <input type="text" value="<?php echo htmlspecialchars($user['nid']); ?>" disabled>

        <label>User Type</label>
        <input type="text" value="<?php echo htmlspecialchars($user['user_type']); ?>" disabled>

        <label>Phone Number</label>
        <input type="text" name="phone_number" value="<?php echo htmlspecialchars($user['phone_number']); ?>" required>

        <label>Preferred Location</label>
        <input type="text" name="preferred_location" value="<?php echo htmlspecialchars($user['preferred_location']); ?>" required>

        <button type="submit" name="update" class="btn btn-update">Update Profile</button>
        <button type="submit" name="delete" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete your account? This action is irreversible.');">Delete Account</button>
    </form>

    <!-- Change Password Button -->
    <button onclick="document.getElementById('changePassModal').style.display='block'" style="margin-top:20px;">
      Change Password
    </button>

    <!-- Change Password Modal/Form -->
    <div id="changePassModal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.5); align-items:center; justify-content:center;">
      <div style="background:#fff; color:#222; padding:30px; border-radius:10px; max-width:350px; margin:auto; position:relative;">
        <span onclick="document.getElementById('changePassModal').style.display='none'" style="position:absolute; top:10px; right:15px; cursor:pointer; font-size:20px;">&times;</span>
        <h3 style="margin-bottom:15px;">Change Password</h3>
        <form action="change_password.php" method="POST">
          <input type="password" name="current_password" placeholder="Current Password" required style="width:100%; margin-bottom:10px; padding:8px;">
          <input type="password" name="new_password" placeholder="New Password" required style="width:100%; margin-bottom:10px; padding:8px;">
          <input type="password" name="confirm_password" placeholder="Confirm New Password" required style="width:100%; margin-bottom:15px; padding:8px;">
          <button type="submit" style="width:100%;">Update Password</button>
        </form>
      </div>
    </div>

    <script>
      // Optional: Close modal when clicking outside the modal box
      window.onclick = function(event) {
        var modal = document.getElementById('changePassModal');
        if (event.target == modal) {
          modal.style.display = "none";
        }
      }
    </script>

    <?php if (isset($_GET['passmsg'])): ?>
        <p class="message <?php echo isset($_GET['passerr']) ? 'error' : ''; ?>">
        <?php echo htmlspecialchars($_GET['passmsg']); ?></p>
    <?php endif; ?>

    <?php if ($updateMsg): ?>
        <p class="message"><?php echo $updateMsg; ?></p>
    <?php endif; ?>

    <?php if ($deleteMsg): ?>
        <p class="message error"><?php echo $deleteMsg; ?></p>
    <?php endif; ?>
</div>

</body>
</html>
