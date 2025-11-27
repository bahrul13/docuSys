<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require "db/db_conn.php";
require "function/log_handler.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Secure SQL query using prepared statements
    $stmt = $conn->prepare("SELECT * FROM user WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {

        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['fullname']; // ✅ Store full name for logging

        // Log login action
        logAction(
            $conn,
            $user['id'],       // user performing the action
            'auth',            // module
            $user['id'],       // record ID (same as user ID for auth)
            'login',           // action
            "User logged in: {$user['fullname']}" // description
        );

        // Redirect based on role
        header("Location: ../users/dashboard.php");
        exit();

    } else {
        $error = "Invalid email or password.";
    }
}
