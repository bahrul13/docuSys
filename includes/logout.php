<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require "../db/db_conn.php";
require "../function/log_handler.php";

// Log the logout action if user is logged in
if (isset($_SESSION['user_id'], $_SESSION['user_email'])) {
    $user_id = $_SESSION['user_id'];
    $user_name = $_SESSION['user_email']; // or $_SESSION['user_name'] if you store full name
    logAction(
        $conn,
        $user_id,
        'auth',
        $user_id,
        'logout',
        "User logged out: {$user_name}"
    );
}

// Destroy session
$_SESSION = [];
session_unset();
session_destroy();

// Prevent browser caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Redirect to login page
header("Location: ../index.php");
exit();
