<?php
session_start();
require '../db/db_conn.php';
require '../function/log_handler.php';

$user_id = $_SESSION['user_id'] ?? null;

$file   = basename($_GET['file'] ?? '');
$folder = $_GET['folder'] ?? '';
$doc_id = isset($_GET['doc_id']) ? (int)$_GET['doc_id'] : 0;

// Whitelist folders
$allowedFolders = ['sfr', 'trba', 'copc', 'other'];
if (!in_array($folder, $allowedFolders)) {
    die("Invalid folder.");
}

$filePath = "../uploads/$folder/$file";

if (!file_exists($filePath)) {
    die("File not found.");
}

// ✅ LOG DOWNLOAD
logAction(
    $conn,
    $user_id,
    $folder,
    $doc_id,
    'Download Document',
    "Downloaded file: $file"
);

// Force download
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $file . '"');
header('Content-Length: ' . filesize($filePath));
readfile($filePath);
exit;
