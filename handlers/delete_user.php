<?php
session_start();
require '../db/db_conn.php';
require '../function/log_handler.php';

// Get logged-in user ID and role
$user_id   = $_SESSION['user_id'] ?? null;
$user_role = $_SESSION['user_role'] ?? null;

// Validate request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {

    $id = intval($_POST['id']);

    // Get user fullname for logging
    $stmt = $conn->prepare("SELECT fullname FROM user WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {

        $row = $result->fetch_assoc();
        $fullname = $row['fullname'];

        // ✅ Deactivate instead of delete
        $updateStmt = $conn->prepare(
            "UPDATE user SET status = 'inactive' WHERE id = ?"
        );
        $updateStmt->bind_param("i", $id);

        if ($updateStmt->execute()) {

            // Prepare log message
            $logMessage = ($user_role === 'admin')
                ? "Deactivated user: {$fullname}"
                : "Deactivated a user";

            // Validate actor user ID
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

            // Log action
            logAction(
                $conn,
                $user_id,
                'user',
                $id,
                'Deactivate User',
                $logMessage
            );

            $_SESSION['flash'] = "✅ User has been deactivated successfully.";

        } else {
            $_SESSION['flash'] = "❌ Failed to deactivate the user.";
        }

        $updateStmt->close();

    } else {
        $_SESSION['flash'] = "⚠️ User not found.";
    }

    $stmt->close();

} else {
    $_SESSION['flash'] = "⚠️ Invalid request.";
}

$conn->close();
header("Location: ../users/user.php");
exit();
?>
