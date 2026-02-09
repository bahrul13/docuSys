<?php
session_start();

require_once __DIR__ . '/../db/db_conn.php';
require_once __DIR__ . '/../function/log_handler.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$user_role = $_SESSION['user_role'] ?? 'user';

// Inputs
$folder = $_GET['folder'] ?? '';
$file   = $_GET['file'] ?? '';
$doc_id = isset($_GET['doc_id']) ? (int)$_GET['doc_id'] : 0;

// Whitelist folders
$allowedFolders = ['sfr', 'trba', 'copc', 'other'];
if (!in_array($folder, $allowedFolders, true)) {
    http_response_code(400);
    die("Invalid folder.");
}

// Block path traversal / weird filenames
// only allow letters, numbers, dot, underscore, dash
if ($file === '' || !preg_match('/^[A-Za-z0-9._-]+$/', $file)) {
    http_response_code(400);
    die("Invalid file name.");
}

if ($doc_id <= 0) {
    http_response_code(400);
    die("Invalid document id.");
}

// Map folder -> table (so we can verify ownership by record id)
$map = [
    'copc' => ['table' => 'copc',      'file_col' => 'file_name'],
    'trba' => ['table' => 'trba',      'file_col' => 'file_name'],
    'sfr'  => ['table' => 'sfr',       'file_col' => 'file_name'],
    'other'=> ['table' => 'documents', 'file_col' => 'file_name'],
];

$table   = $map[$folder]['table'];
$fileCol = $map[$folder]['file_col'];

// Verify that (doc_id + file_name) exists in DB
// This prevents someone from downloading random files by guessing names.
$sql = "SELECT {$fileCol} AS file_name FROM {$table} WHERE id = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $doc_id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res ? $res->fetch_assoc() : null;
$stmt->close();

if (!$row) {
    http_response_code(404);
    die("Record not found.");
}

$dbFile = $row['file_name'] ?? '';
if (!hash_equals($dbFile, $file)) {
    http_response_code(403);
    die("File mismatch.");
}

// Build final path (now safe)
$filePath = realpath(__DIR__ . "/../uploads/$folder/$file");

// Ensure resolved path is inside expected folder
$baseDir = realpath(__DIR__ . "/../uploads/$folder");
if (!$filePath || !$baseDir || strpos($filePath, $baseDir) !== 0) {
    http_response_code(403);
    die("Invalid file path.");
}

if (!is_file($filePath) || !file_exists($filePath)) {
    http_response_code(404);
    die("File not found.");
}

// Log download (only after all checks)
logAction(
    $conn,
    $user_id,
    $folder,
    $doc_id,
    'Download Document',
    "Downloaded file: {$file}"
);

// Content-Type (basic)
$ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
$mimeMap = [
    'pdf'  => 'application/pdf',
    'doc'  => 'application/msword',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'xls'  => 'application/vnd.ms-excel',
    'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'ppt'  => 'application/vnd.ms-powerpoint',
    'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    'png'  => 'image/png',
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'gif'  => 'image/gif',
    'txt'  => 'text/plain',
    'zip'  => 'application/zip',
    'rar'  => 'application/x-rar-compressed',
];

$contentType = $mimeMap[$ext] ?? 'application/octet-stream';

// Clean output buffer (avoid corrupt downloads)
if (ob_get_length()) {
    ob_end_clean();
}

// Force download
header('Content-Description: File Transfer');
header('Content-Type: ' . $contentType);
header('Content-Disposition: attachment; filename="' . basename($file) . '"');
header('Content-Length: ' . filesize($filePath));
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

readfile($filePath);
exit;
