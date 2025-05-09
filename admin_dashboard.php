<?php
session_start();
require 'db.php';


// Get all users
$query = $conn->prepare("
    SELECT id, username, email, phone_number, nid, user_type, created_at 
    FROM users 
    WHERE user_type != 'Admin'
    ORDER BY created_at DESC
");
$query->execute();
$users = $query->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #3c504c, #0d1225);
            padding: 20px;
            color: #333;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        h2 {
            color: #00796b;
            margin: 0;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #00796b;
        }

        .stat-card h3 {
            margin: 0 0 10px 0;
            color: #00796b;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #00796b;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        .user-type {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.9em;
        }

        .type-bachelor {
            background-color: #e3f2fd;
            color: #1976d2;
        }

        .type-owner {
            background-color: #e8f5e9;
            color: #2e7d32;
        }

        .back-btn {
            padding: 10px 20px;
            background-color: #00796b;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .back-btn:hover {
            background-color: #004d40;
        }

        .search-box {
            margin-bottom: 20px;
        }

        .search-box input {
            padding: 8px;
            width: 300px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .delete-btn {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .delete-btn:hover {
            background-color: #c82333;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border-radius: 5px;
            width: 80%;
            max-width: 500px;
        }

        .modal-buttons {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Admin Dashboard</h2>
            <a href="home.php" class="back-btn">← Back to Home</a>
        </div>

        <div class="stats">
            <?php
            // Get user type counts
            $stats = $conn->query("
                SELECT user_type, COUNT(*) as count 
                FROM users 
                WHERE user_type != 'Admin'
                GROUP BY user_type
            ")->fetch_all(MYSQLI_ASSOC);

            foreach ($stats as $stat): ?>
                <div class="stat-card">
                    <h3><?= htmlspecialchars($stat['user_type']) ?>s</h3>
                    <p><?= htmlspecialchars($stat['count']) ?> registered</p>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Search users...">
        </div>

        <table id="usersTable">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>NID</th>
                    <th>User Type</th>
                    <th>Joined Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = $users->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= htmlspecialchars($user['phone_number']) ?></td>
                        <td><?= htmlspecialchars($user['nid']) ?></td>
                        <td>
                            <span class="user-type type-<?= strtolower($user['user_type']) ?>">
                                <?= htmlspecialchars($user['user_type']) ?>
                            </span>
                        </td>
                        <td><?= date('Y-m-d', strtotime($user['created_at'])) ?></td>
                        <td>
                            <button class="delete-btn" 
                                    onclick="deleteUser(<?= $user['id'] ?>, '<?= htmlspecialchars($user['username']) ?>')">
                                Delete
                            </button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <script>
        const searchInput = document.getElementById('searchInput');
        const table = document.getElementById('usersTable');
        const rows = table.getElementsByTagName('tr');

        searchInput.addEventListener('keyup', function(e) {
            const searchText = e.target.value.toLowerCase();

            for (let i = 1; i < rows.length; i++) {
                const rowData = rows[i].textContent.toLowerCase();
                rows[i].style.display = rowData.includes(searchText) ? '' : 'none';
            }
        });

        function deleteUser(userId, username) {
            if (confirm(`Are you sure you want to delete user "${username}"? This action cannot be undone.`)) {
                fetch('delete_user.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `user_id=${userId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.error || 'Error deleting user');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error deleting user');
                });
            }
        }
    </script>
</body>
</html>