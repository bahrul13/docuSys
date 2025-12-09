<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../db/db_conn.php';
require_once '../function/log_handler.php';

$user_id = $_SESSION['user_id'] ?? 1;

$file = basename($_GET['file'] ?? '');
$folder = $_GET['folder'] ?? 'other';

// Whitelist folders
$allowedFolders = ['other', 'copc', 'sfr', 'trba'];
if (!in_array($folder, $allowedFolders)) {
    die("❌ Invalid folder.");
}

$filePath = "../uploads/$folder/$file";

if ($file && file_exists($filePath)) {

    // Log download
    logAction(
        $conn,
        $user_id,
        'documents',
        0,
        'Download',
        "Downloaded file: $file from folder: " . strtoupper($folder)
    );

    // Serve file
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $filePath);
    finfo_close($finfo);

    header('Content-Description: File Transfer');
    header('Content-Type: ' . $mime);
    header('Content-Disposition: attachment; filename="' . basename($file) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($filePath));
    readfile($filePath);
    exit;
} else {
    echo "❌ File not found: $filePath";
}
?>
