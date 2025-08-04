<?php
// Set PHP timezone
date_default_timezone_set('Asia/Manila');

// Database credentials
$host = 'localhost';
$user = 'root';
$pass = '';
$db_name = 'docusys_db';

// Create connection
$conn = new mysqli($host, $user, $pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Set MySQL timezone to match PHP (Asia/Manila = UTC+08:00)
$conn->query("SET time_zone = '+08:00'");
?>
