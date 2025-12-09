<?php

// Main logAction Function
function logAction($conn, $user_id, $module, $record_id, $action, $description) {

    // If user is NULL (tab close with expired session), default to admin
    if (is_null($user_id) || !user_id_exists_in_db($user_id)) {
        $user_id = 1; // fallback admin ID
    }

    // Insert into correct columns (module instead of documents)
    $stmt = $conn->prepare("
        INSERT INTO transaction_logs (user_id, documents, record_id, action, description)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("isiss", $user_id, $module, $record_id, $action, $description);
    $stmt->execute();
}

// Check if user ID exists
function user_id_exists_in_db($id) {
    global $conn;
    if (!$id) return false;

    $stmt = $conn->prepare("SELECT id FROM user WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    
    return $result->num_rows > 0;
}

// NEW: Background "tab close/logout" logging function
function logTabCloseLogout($conn) {

    // Session may or may not exist during tab close
    $user_id = isset($_SESSION['id']) ? $_SESSION['id'] : null;

    logAction(
        $conn,
        $user_id,
        'auth',                  // module
        $user_id,                // record affected
        'logout_tab_close',      // action
        'User closed the browser/tab' // description
    );

    // Optional session destruction
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_unset();
        session_destroy();
    }
}
?>
