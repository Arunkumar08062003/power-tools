<?php
// Set timezone
date_default_timezone_set("Asia/Calcutta");

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "power_tools_db";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>