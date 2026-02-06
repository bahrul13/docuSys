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

// ✅ Detect file extension
$fileName = $doc['file_name'] ?? '';
$fileExt  = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

// ✅ Default iframe source uses local web path (works for PDF)
$iframeSrc = $pdfFileUrl;

// ✅ If Word file, use Google Docs Viewer (requires public URL)
if (in_array($fileExt, ['doc', 'docx'])) {

    // 🔥 CHANGE THIS to your real public domain / public IP
    // Example: https://yourdomain.com/uploads/other/filename.docx
    $publicUrl = "https://yourdomain.com/uploads/other/" . rawurlencode($fileName);

    $iframeSrc = "https://docs.google.com/gview?url={$publicUrl}&embedded=true";
}

if (!file_exists($pdfFilePath)) {
    $_SESSION['flash'] = "File not found.";
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
        "Viewed Document: {$doc['document']}"
    );
}

// ❌ REMOVE this — PHP auto closes DB connection
// $conn->close();
?>
