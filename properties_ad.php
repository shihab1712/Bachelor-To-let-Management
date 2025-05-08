<?php
session_start();
require 'db.php';


if (!isset($_SESSION['username']) || $_SESSION['user_type'] == 'Bachelor') {
    header("Location: view_properties.php");
    exit();
}


