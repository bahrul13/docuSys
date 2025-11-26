<?php
function logAction($conn, $user_id, $documents, $record_id, $action, $description) {
    // Default to admin if null
    if (is_null($user_id)) {
        $user_id = 1; // admin ID
    }

    $stmt = $conn->prepare("
        INSERT INTO transaction_logs (user_id, documents, record_id, action, description)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("isiss", $user_id, $documents, $record_id, $action, $description);
    $stmt->execute();
}

// ✅ Helper function to check if user exists
function user_id_exists_in_db($id) {
    global $conn;
    $stmt = $conn->prepare("SELECT id FROM user WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    return $result->num_rows > 0;
}
?>
