<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require "db/db_conn.php";
require "function/log_handler.php";
require "function/csrf.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    csrf_verify();

    // Trim inputs
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Basic validation: empty fields
    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    }
    // Validate email format
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    }
    else {
        // Secure SQL query
        $stmt = $conn->prepare("SELECT * FROM user WHERE email = ?");
        if (!$stmt) {
            $error = "Database error. Try again.";
        } else {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            //  User does not exist
            if (!$user) {
                $error = "Invalid email or password.";
            }
            // User exists but not approved
            elseif ($user['status'] !== 'approved') {
                $error = "Your account is pending admin approval.";
            }
            // Password check
            elseif (!password_verify($password, $user['password'])) {
                $error = "Invalid email or password.";
            }
            // Successful login
            else {
                // Set session variables
                $_SESSION['user_id']    = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role']  = $user['role'];
                $_SESSION['user_name']  = $user['fullname'];

                // Log login action
                logAction(
                    $conn,
                    $user['id'],       // user performing the action
                    'auth',            // module
                    $user['id'],       // record ID
                    'login',           // action
                    "User logged in: {$user['fullname']}"
                );

                // Redirect to dashboard
                header("Location: ../users/dashboard.php");
                exit();
            }
        }
    }
}
?>
