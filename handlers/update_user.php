<?php
session_start();
require '../db/db_conn.php';
require '../function/log_handler.php';

// Get logged-in user ID
$user_id = $_SESSION['user_id'] ?? null;

// Check if admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['flash'] = "Access denied.";
    header("Location: ../users/user.php");
    exit();
}

// Validate correct input fields
if (isset($_POST['id'], $_POST['fullname'], $_POST['email'], $_POST['role'])) {

    $id = intval($_POST['id']);
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $role = trim($_POST['role']);

    // Handle password condition
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

        $_SESSION['flash'] = "✅ User Updated successfully.";

        // LOG ACTION
        $logMessage = "Updated User: $fullname";

        // Validate user exists (prevent FK issues)
        if ($user_id) {
            $checkUser = $conn->prepare("SELECT id FROM user WHERE id = ?");
            $checkUser->bind_param("i", $user_id);
            $checkUser->execute();
            $checkResult = $checkUser->get_result();
            $checkUser->close();

            if ($checkResult->num_rows === 0) {
                $user_id = null;
            }
        }

        logAction($conn, $user_id, 'user', $id, 'Update User', $logMessage);

    } else {
        $_SESSION['flash'] = "❌ Failed to update the User.";
    }

    $stmt->close();

} else {
    $_SESSION['flash'] = "⚠️ All fields are required.";
}

$conn->close();
header("Location: ../users/user.php");
exit();
