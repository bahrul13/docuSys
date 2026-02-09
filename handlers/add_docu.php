<?php
session_start();
require_once __DIR__ . '/../db/db_conn.php';
require_once __DIR__ . '/../function/csrf.php';
require_once __DIR__ . '/../function/log_handler.php';

// Must be logged in (recommended)
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Ensure user_id exists for logging
$user_id = (int)($_SESSION['user_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['flash'] = "⚠️ Invalid request method.";
    header("Location: ../users/other.php");
    exit();
}

// ✅ CSRF check (do this only on POST)
csrf_verify();

$program = trim($_POST['documentName'] ?? '');
$file    = $_FILES['file_name'] ?? null;

// ======= BASIC VALIDATION =======
if ($program === '' || empty($file) || empty($file['name'])) {
    $_SESSION['flash'] = "❌ Document name and file are required.";
    header("Location: ../users/other.php");
    exit();
}

// Optional: limit title length
if (mb_strlen($program) > 150) {
    $_SESSION['flash'] = "❌ Document name is too long (max 150 characters).";
    header("Location: ../users/other.php");
    exit();
}

// ======= UPLOAD ERROR CHECK =======
if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['flash'] = "❌ File upload failed. Error code: " . ($file['error'] ?? 'unknown');
    header("Location: ../users/other.php");
    exit();
}

// Optional: file size limit (example 20MB)
$maxBytes = 20 * 1024 * 1024;
if (!empty($file['size']) && $file['size'] > $maxBytes) {
    $_SESSION['flash'] = "❌ File is too large. Max 20MB.";
    header("Location: ../users/other.php");
    exit();
}

// ======= FILE TYPE VALIDATION (REAL MIME CHECK) =======
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime  = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

$allowedMimes = [
    // PDF
    'application/pdf',

    // Word
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',

    // Excel
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',

    // PowerPoint
    'application/vnd.ms-powerpoint',
    'application/vnd.openxmlformats-officedocument.presentationml.presentation',

    // Images
    'image/jpeg',
    'image/png',
    'image/gif',

    // Text
    'text/plain',

    // ZIP / RAR
    'application/zip',
    'application/x-rar-compressed'
];

if (!in_array($mime, $allowedMimes, true)) {
    $_SESSION['flash'] = "❌ File type not allowed.";
    header("Location: ../users/other.php");
    exit();
}

// ======= SAFE FILE NAME =======
$originalFileName = basename($file['name']);
$originalFileName = preg_replace('/[^A-Za-z0-9._-]/', '_', $originalFileName);

// ======= DUPLICATE DOCUMENT NAME VALIDATION =======
$checkDoc = $conn->prepare("SELECT id FROM documents WHERE document = ?");
$checkDoc->bind_param("s", $program);
$checkDoc->execute();
$checkDoc->store_result();

if ($checkDoc->num_rows > 0) {
    $_SESSION['flash'] = "❌ Document name already exists. Please choose a different name.";
    $checkDoc->close();
    header("Location: ../users/other.php");
    exit();
}
$checkDoc->close();

// ======= DUPLICATE FILE NAME VALIDATION =======
$checkFile = $conn->prepare("SELECT id FROM documents WHERE file_name = ?");
$checkFile->bind_param("s", $originalFileName);
$checkFile->execute();
$checkFile->store_result();

if ($checkFile->num_rows > 0) {
    $_SESSION['flash'] = "❌ A file with the same name already exists. Please rename your file.";
    $checkFile->close();
    header("Location: ../users/other.php");
    exit();
}
$checkFile->close();

// ======= UPLOAD DIRECTORY =======
$uploadDir = '../uploads/other/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// ======= MOVE FILE =======
$uploadPath = $uploadDir . $originalFileName;

if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
    $_SESSION['flash'] = "❌ File upload failed.";
    header("Location: ../users/other.php");
    exit();
}

// ======= INSERT INTO DATABASE =======
$stmt = $conn->prepare("INSERT INTO documents (document, file_name) VALUES (?, ?)");
$stmt->bind_param("ss", $program, $originalFileName);

if ($stmt->execute()) {

    $newRecordId = $stmt->insert_id;

    // ======= LOG ACTION =======
    logAction(
        $conn,
        $user_id,
        'accreditation',
        (int)$newRecordId,
        'Add Accreditation-Related Document',
        "Added Accreditation-Related document: $program"
    );

    $_SESSION['flash'] = "✅ Document uploaded successfully.";

} else {

    // Remove uploaded file if DB insert failed
    if (file_exists($uploadPath)) {
        unlink($uploadPath);
    }

    $_SESSION['flash'] = "❌ Database error: Failed to save document.";
}

$stmt->close();

header("Location: ../users/other.php");
exit();
?>
