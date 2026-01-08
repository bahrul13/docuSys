<?php
session_start();
require '../db/db_conn.php';
require "../function/log_handler.php"; // ✅ Include log function here

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname']);
    $dept = trim($_POST['department']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role']; // "admin" or "user"

    // Check for duplicate email
    $check = $conn->prepare("SELECT id FROM user WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $_SESSION['flash'] = "❌ Email already exists. Please use a different email.";
        $check->close();
        $conn->close();
        header("Location: ../users/user.php");
        exit();
    }
    $check->close();

    // Insert user into database
    $stmt = $conn->prepare("INSERT INTO user (fullname, dept, email, password, role) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $fullname, $dept,  $email, $password, $role);

    if ($stmt->execute()) {

        // ==============================
        // ✅ INSERT LOG HERE
        // ==============================
        $newUserId = $stmt->insert_id;

        logAction(
            $conn,
            $_SESSION['id'],     // the admin who added the user
            'user',              // module
            $newUserId,          // the newly created user ID
            'Add User',          // action
            'Added user: ' . $fullname  // description
        );

        $_SESSION['flash'] = "✅ User added successfully!";
    } else {
        $_SESSION['flash'] = "❌ Database error: Failed to add user.";
    }

    $stmt->close();
    $conn->close();

    header("Location: ../users/user.php");
    exit();
} else {
    $_SESSION['flash'] = "⚠️ Invalid request method.";
    header("Location: ../users/user.php");
    exit();
}
?>
