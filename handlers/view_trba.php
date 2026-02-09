<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../db/db_conn.php';
require_once __DIR__ . '/../function/log_handler.php';

// Get logged-in user info
$user_id   = $_SESSION['user_id'] ?? null;
$user_role = $_SESSION['user_role'] ?? null;

// Get document ID from URL safely
$doc_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($doc_id <= 0) {
    $_SESSION['flash'] = "Invalid document ID.";
    header("Location: ../users/trba.php");
    exit();
}

// Fetch document from database
$stmt = $conn->prepare("SELECT * FROM trba WHERE id = ?");
$stmt->bind_param("i", $doc_id);
$stmt->execute();
$result = $stmt->get_result();
$doc = $result->fetch_assoc();
$stmt->close();

if (!$doc) {
    $_SESSION['flash'] = "File not found.";
    header("Location: ../users/trba.php");
    exit();
}

// ✅ Filesystem path for checking
$pdfFilePath = __DIR__ . "/../uploads/trba/" . $doc['file_name'];

// ✅ Web path for iframe
$pdfFileUrl  = "../uploads/trba/" . $doc['file_name'];

if (!file_exists($pdfFilePath)) {
    $_SESSION['flash'] = "PDF file not found.";
    header("Location: ../users/trba.php");
    exit();
}

// ✅ Log view action if user is logged in
if ($user_id) {
    logAction(
        $conn,
        $user_id,
        'trba',
        $doc_id,
        'View Document',
        "Viewed Document: {$doc['program_name']}"
    );
}

// ❌ REMOVE this — PHP auto closes DB connection
// $conn->close();
?>
