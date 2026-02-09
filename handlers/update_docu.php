<?php
session_start();

require_once __DIR__ . '/../db/db_conn.php';
require_once __DIR__ . '/../function/csrf.php';
require_once __DIR__ . '/../function/log_handler.php';

// ✅ Require login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];

// ✅ Admin only
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['flash'] = "Access denied.";
    header("Location: ../users/other.php");
    exit();
}

// ✅ POST only
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['flash'] = "⚠️ Invalid request method.";
    header("Location: ../users/other.php");
    exit();
}

// ✅ CSRF verify
csrf_verify();

$id       = (int)($_POST['id'] ?? 0);
$document = trim($_POST['document'] ?? '');

if ($id <= 0 || $document === '') {
    $_SESSION['flash'] = "❌ Missing required fields.";
    header("Location: ../users/other.php");
    exit();
}

// ✅ Prevent duplicate document name (exclude current record)
$dupDoc = $conn->prepare("SELECT id FROM documents WHERE document = ? AND id != ? LIMIT 1");
$dupDoc->bind_param("si", $document, $id);
$dupDoc->execute();
$dupDoc->store_result();
if ($dupDoc->num_rows > 0) {
    $dupDoc->close();
    $_SESSION['flash'] = "❌ Document name already exists. Please use another name.";
    header("Location: ../views/update_docu_page.php?id=" . $id);
    exit();
}
$dupDoc->close();

// ✅ Get current file name
$currentStmt = $conn->prepare("SELECT file_name FROM documents WHERE id = ? LIMIT 1");
$currentStmt->bind_param("i", $id);
$currentStmt->execute();
$currentRes = $currentStmt->get_result();
$currentRow = $currentRes->fetch_assoc();
$currentStmt->close();

if (!$currentRow) {
    $_SESSION['flash'] = "⚠️ Document not found.";
    header("Location: ../users/other.php");
    exit();
}

$oldFileName = $currentRow['file_name'] ?? '';
$uploadDir   = __DIR__ . '/../uploads/other/';

$newFileUploaded = (isset($_FILES['file_name']) && $_FILES['file_name']['error'] === UPLOAD_ERR_OK);

$fileName = null;

if ($newFileUploaded) {

    // ✅ Real MIME check
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $_FILES['file_name']['tmp_name']);
    finfo_close($finfo);

    $allowedMimes = [
        'application/pdf',

        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',

        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',

        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',

        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',

        'text/plain',

        'application/zip',
        'application/x-zip-compressed',
        'application/x-rar-compressed',
        'application/vnd.rar',
        'application/x-rar'
    ];

    if (!in_array($mime, $allowedMimes, true)) {
        $_SESSION['flash'] = "❌ File type not allowed.";
        header("Location: ../views/update_docu_page.php?id=" . $id);
        exit();
    }

    // ✅ Safe filename
    $fileName = basename($_FILES['file_name']['name']);
    $fileName = preg_replace('/[^A-Za-z0-9._-]/', '_', $fileName);

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // ✅ Prevent duplicate file_name in DB (exclude current record)
    $dupFile = $conn->prepare("SELECT id FROM documents WHERE file_name = ? AND id != ? LIMIT 1");
    $dupFile->bind_param("si", $fileName, $id);
    $dupFile->execute();
    $dupFile->store_result();
    if ($dupFile->num_rows > 0) {
        $dupFile->close();
        $_SESSION['flash'] = "❌ A file with the same name already exists in records. Please rename your file.";
        header("Location: ../views/update_docu_page.php?id=" . $id);
        exit();
    }
    $dupFile->close();

    $targetFile = $uploadDir . $fileName;

    if (!move_uploaded_file($_FILES['file_name']['tmp_name'], $targetFile)) {
        $_SESSION['flash'] = "❌ File upload failed.";
        header("Location: ../views/update_docu_page.php?id=" . $id);
        exit();
    }

    // ✅ Update DB with file change
    $stmt = $conn->prepare("UPDATE documents SET document = ?, file_name = ? WHERE id = ?");
    $stmt->bind_param("ssi", $document, $fileName, $id);

} else {

    // ✅ Update DB without changing file
    $stmt = $conn->prepare("UPDATE documents SET document = ? WHERE id = ?");
    $stmt->bind_param("si", $document, $id);
}

if ($stmt->execute()) {

    // ✅ Delete old file if replaced
    if ($newFileUploaded && $fileName && $fileName !== $oldFileName && !empty($oldFileName)) {
        $oldPath = $uploadDir . $oldFileName;
        if (file_exists($oldPath)) {
            unlink($oldPath);
        }
    }

    $_SESSION['flash'] = "✅ Document record updated successfully.";

    $logMessage = "Updated Document record: {$document}";
    if ($newFileUploaded && $fileName) {
        $logMessage .= " (Replaced file with: {$fileName})";
    }

    logAction($conn, $user_id, 'documents', $id, 'Update Document', $logMessage);

} else {

    // ✅ If DB update failed but file uploaded, remove it
    if ($newFileUploaded && $fileName) {
        $newPath = $uploadDir . $fileName;
        if (file_exists($newPath)) {
            unlink($newPath);
        }
    }

    $_SESSION['flash'] = "❌ Failed to update Document record.";
}

$stmt->close();

header("Location: ../users/other.php");
exit();
?>
