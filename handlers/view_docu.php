<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require "../db/db_conn.php";
require "../function/log_handler.php";

// Get logged-in user info
$user_id   = $_SESSION['user_id'] ?? null;
$user_role = $_SESSION['user_role'] ?? null;

// Get document ID from URL safely
$doc_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($doc_id <= 0) {
    $_SESSION['flash'] = "Invalid document ID.";
    header("Location: ../users/other.php");
    exit();
}

// Fetch document from database
$stmt = $conn->prepare("SELECT * FROM documents WHERE id = ?");
$stmt->bind_param("i", $doc_id);
$stmt->execute();
$result = $stmt->get_result();
$doc = $result->fetch_assoc();
$stmt->close();

if (!$doc) {
    $_SESSION['flash'] = "Document not found.";
    header("Location: ../users/other.php");
    exit();
}

// ✅ Filesystem path for checking
$pdfFilePath = __DIR__ . "/../uploads/other/" . $doc['file_name'];

// ✅ Web path for iframe
$pdfFileUrl  = "../uploads/other/" . $doc['file_name'];

if (!file_exists($pdfFilePath)) {
    $_SESSION['flash'] = "PDF file not found.";
    header("Location: ../users/other.php");
    exit();
}

// ✅ Log view action if user is logged in
if ($user_id) {
    logAction(
        $conn,
        $user_id,
        'documents',
        $doc_id,
        'View Document',
        "Viewed Document: {$doc['document']} (File: {$doc['file_name']})"
    );
}

// ❌ REMOVE this — PHP auto closes DB connection
// $conn->close();
?>
