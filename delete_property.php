<?php
session_start();
require 'db.php';

// if (!isset($_SESSION['username']) || $_SESSION['user_type'] !== 'Owner') {
//     header("Location: index.html");
//     exit();
// }

$id = $_GET['id'];
$username = $_SESSION['username'];

$del = $conn->prepare("DELETE FROM properties WHERE id = ? AND owner_username = ?");
$del->bind_param("is", $id, $username);
$del->execute();

header("Location: view_posted_properties.php");
exit();
