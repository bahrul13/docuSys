<?php

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    require_once __DIR__ . '/../function/csrf.php';
    csrf_verify();

    // Trim inputs
    $fullname = trim($_POST['fullname']);
    $dept = trim($_POST['department']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Empty check
    if (empty($fullname) || empty($dept) || empty($email) || empty($password) || empty($confirm_password)) {
    $error = "All fields are required.";
    }

    // Email format
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } 
    // Full name validation (letters, spaces, dash, apostrophe)
    elseif (!preg_match("/^[a-zA-Z-' ]+$/", $fullname)) {
        $error = "Full name can only contain letters, spaces, apostrophes, and dashes.";
    } 

    // Confirm password check
    elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    }

    elseif (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)(?=.*[^A-Za-z0-9])[^\s]{8,20}$/', $password)) {
    $error = "Password must be 8–12 characters long and must not contain spaces.";
    }

    else {

        // Sanitize fullname for DB
        $fullname = htmlspecialchars($fullname, ENT_QUOTES, 'UTF-8');

        // Check if email already exists
        $check = $conn->prepare("SELECT id FROM user WHERE email = ?");
        if (!$check) {
            $error = "Database error. Try again.";
        } else {
            $check->bind_param("s", $email);
            $check->execute();
            $check->store_result();

            if ($check->num_rows > 0) {
                $error = "Email already registered.";
            } else {

                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insert user with status 'pending'
                $stmt = $conn->prepare("
                    INSERT INTO user (fullname, dept, email, password, role, status)
                    VALUES (?, ?,  ?, ?, 'user', 'pending')
                "); 

                if (!$stmt) {
                    $error = "Database error. Try again.";
                } else {
                    $stmt->bind_param("ssss", $fullname, $dept, $email, $hashed_password);

                    if ($stmt->execute()) {
                        $success = "Registration successful! Please wait for admin approval.";
                    } else {
                        $error = "Registration failed. Try again.";
                    }
                }
            }
        }
    }
}
?>
