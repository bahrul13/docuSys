<?php
session_start();
require '../db/db_conn.php';
require '../function/log_handler.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {

    $id = intval($_POST['id']);
    $admin_id = $_SESSION['user_id'] ?? null;

    // Get user name
    $stmt = $conn->prepare("SELECT fullname FROM user WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {

        $user = $result->fetch_assoc();
        $fullname = $user['fullname'];

        // Reactivate user
        $update = $conn->prepare(
            "UPDATE user SET status = 'approved' WHERE id = ?"
        );
        $update->bind_param("i", $id);

        if ($update->execute()) {

            logAction(
                $conn,
                $admin_id,
                'user',
                $id,
                'Reactivate User',
                "Reactivated user: {$fullname}"
            );

            $_SESSION['flash'] = "✅ User reactivated successfully.";
        } else {
            $_SESSION['flash'] = "❌ Failed to reactivate user.";
        }

        $update->close();
    }

    $stmt->close();
}

$conn->close();
header("Location: inactive_user.php");
exit();
?>
