<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../db/db_conn.php';
require_once __DIR__ . '/../function/log_handler.php';

// Log the logout action if user is logged in
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Fetch the full name from database
    $stmt = $conn->prepare("SELECT fullname FROM user WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc(); // $user['fullname'] will be available
    $stmt->close();

    if ($user) {
        logAction(
            $conn, 
            $user_id, 
            'auth', 
            $user_id, 
            'logout', 
            "User logged out: {$user['fullname']}"
        );
    }
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
?>
