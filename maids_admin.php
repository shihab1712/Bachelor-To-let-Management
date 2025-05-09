<?php
session_start();
require 'db.php';


// Add
if (isset($_POST['add'])) {
    $stmt = $conn->prepare("INSERT INTO maids (name, phone, location, experience_years, working_hours, availability) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssiss", $_POST['name'], $_POST['phone'], $_POST['location'], $_POST['experience'], $_POST['working_hours'], $_POST['availability']);
    $stmt->execute();
}

// Update
if (isset($_POST['update'])) {
    $stmt = $conn->prepare("UPDATE maids SET name=?, phone=?, location=?, experience_years=?, working_hours=?, availability=? WHERE id=?");
    $stmt->bind_param("sssissi", $_POST['name'], $_POST['phone'], $_POST['location'], $_POST['experience'], $_POST['working_hours'], $_POST['availability'], $_POST['id']);
    $stmt->execute();
}

// Delete
if (isset($_POST['delete'])) {
    $stmt = $conn->prepare("DELETE FROM maids WHERE id=?");
    $stmt->bind_param("i", $_POST['id']);
    $stmt->execute();
}

$maids = $conn->query("SELECT * FROM maids");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin - Manage Maids</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f0f8ff;
            margin: 20px;
        }

        h2 {
            text-align: center;
            color: #006064;
        }

        form, table {
            max-width: 900px;
            margin: auto;
        }

        input, select {
            padding: 6px;
            margin: 4px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        button {
            padding: 6px 12px;
            margin: 4px;
            border: none;
            background: #00796b;
            color: white;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background: #004d40;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            margin-top: 20px;
        }

        th, td {
            padding: 10px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        th {
            background: #00838f;
            color: white;
        }
    </style>
</head>
<body>
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
    <h2>Admin - Manage Maids</h2>
    <form method="POST">
        <input type="text" name="name" placeholder="Name" required>
        <input type="text" name="phone" placeholder="Phone">
        <input type="text" name="location" placeholder="Location">
        <input type="number" name="experience" placeholder="Experience (yrs)">
        <input type="number" name="working_hours" placeholder="Working Hours">
        <select name="availability">
            <option value="Available">Available</option>
            <option value="Unavailable">Unavailable</option>
        </select>
        <button type="submit" name="add">Add Maid</button>
    </form>

    <table>
        <tr>
            <th>Name</th><th>Phone</th><th>Location</th><th>Experience</th><th>Working Hours</th><th>Availability</th><th>Actions</th>
        </tr>
        <?php while ($maid = $maids->fetch_assoc()): ?>
        <form method="POST">
            <input type="hidden" name="id" value="<?= $maid['id'] ?>">
            <tr>
                <td><input name="name" value="<?= $maid['name'] ?>"></td>
                <td><input name="phone" value="<?= $maid['phone'] ?>"></td>
                <td><input name="location" value="<?= $maid['location'] ?>"></td>
                <td><input name="experience" type="number" value="<?= $maid['experience_years'] ?>"></td>
                <td><input name="working_hours" type="number" value="<?= $maid['working_hours'] ?>"></td>
                <td>
                    <select name="availability">
                        <option <?= $maid['availability']=='Available'?'selected':'' ?>>Available</option>
                        <option <?= $maid['availability']=='Unavailable'?'selected':'' ?>>Unavailable</option>
                    </select>
                </td>
                <td>
                    <button name="update">Update</button>
                    <button name="delete" onclick="return confirm('Delete maid?')">Delete</button>
                </td>
            </tr>
        </form>
        <?php endwhile; ?>
    </table>
</body>
</html>
