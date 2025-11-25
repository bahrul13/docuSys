<?php
session_start();
require '../db/db_conn.php';

// Check if admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['flash'] = "Access denied.";
    header("Location: ../users/user.php");
    exit();
}

// UPDATE USER
if (isset($_POST['update_user'])) {
    $id = intval($_POST['id']);
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];

    // If password is NOT empty, update it; otherwise keep old password.
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $query = "UPDATE user SET fullname=?, email=?, password=?, role=? WHERE id=?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssi", $fullname, $email, $password, $role, $id);
    } else {
        $query = "UPDATE user SET fullname=?, email=?, role=? WHERE id=?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssi", $fullname, $email, $role, $id);
    }

    if ($stmt->execute()) {
        // ✅ Set flash message on success
        $_SESSION['flash_success'] = "✅ User updated successfully!";
    } else {
        // ✅ Set flash message on failure
        $_SESSION['flash_error'] = "❌ Failed to update user: " . $stmt->error;
    }

    $stmt->close();

    // Redirect to avoid form resubmission
    header("Location: ../users/user.php");
    exit();
}
else {
    $_SESSION['flash'] = "⚠️ Invalid request method.";
    header("Location: ../users/user.php");
    exit();
}

