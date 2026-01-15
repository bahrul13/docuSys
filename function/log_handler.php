<?php

// ✅ Main logAction Function
function logAction(mysqli $conn, ?int $user_id, string $module, int $record_id, string $action, string $description): void
{
    // If user is NULL or not found, fallback to admin ID = 1
    if (empty($user_id) || !userIdExists($conn, $user_id)) {
        $user_id = 1;
    }

    $stmt = $conn->prepare("
        INSERT INTO transaction_logs (user_id, documents, record_id, action, description)
        VALUES (?, ?, ?, ?, ?)
    ");

    if (!$stmt) {
        // Optional: don’t break page if logging fails
        return;
    }

    $stmt->bind_param("isiss", $user_id, $module, $record_id, $action, $description);
    $stmt->execute();
    $stmt->close(); // ✅ close statement
}

// ✅ Check if user ID exists (NO global conn)
function userIdExists(mysqli $conn, int $id): bool
{
    $stmt = $conn->prepare("SELECT id FROM user WHERE id = ?");
    if (!$stmt) return false;

    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->store_result();
    $exists = $stmt->num_rows > 0;
    $stmt->close();

    return $exists;
}

// ✅ Background "tab close/logout" logging function
function logTabCloseLogout(mysqli $conn): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Use your real session key (you use user_id in other files)
    $user_id = $_SESSION['user_id'] ?? null;

    logAction(
        $conn,
        $user_id,
        'auth',
        (int)($user_id ?? 0),
        'logout_tab_close',
        'User closed the browser/tab'
    );

    if (session_status() === PHP_SESSION_ACTIVE) {
        session_unset();
        session_destroy();
    }
}
