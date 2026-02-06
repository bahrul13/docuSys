<?php
session_start();
require '../db/db_conn.php';
require '../function/log_handler.php';
require '../function/csrf.php';

// ✅ Get logged-in user id (for logs)
$user_id = $_SESSION['user_id'] ?? null;

// ✅ Admin only (since archived page is admin)
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['flash'] = "Access denied.";
    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? "../users/other.php"));
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['module'])) {

    csrf_verify();

    $id     = (int)$_POST['id'];
    $module = trim($_POST['module']);

    // ✅ Map module to table + upload folder + log label
    $map = [
        'copc' => [
            'table' => 'copc',
            'folder' => '../uploads/copc/',
            'title' => 'program',
            'log_action' => 'Delete COPC'
        ],
        'trba' => [
            'table' => 'trba',
            'folder' => '../uploads/trba/',
            'title' => 'program_name',
            'log_action' => 'Delete TRBA'
        ],
        'sfr' => [
            'table' => 'sfr',
            'folder' => '../uploads/sfr/',
            'title' => 'program_name',
            'log_action' => 'Delete SFR'
        ],
        'accreditation' => [
            'table' => 'documents',
            'folder' => '../uploads/other/',
            'title' => 'document',
            'log_action' => 'Delete Accreditation'
        ],
    ];

    if (!isset($map[$module])) {
        $_SESSION['flash'] = "⚠️ Invalid module.";
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? "../users/other.php"));
        exit();
    }

    $table     = $map[$module]['table'];
    $folder    = $map[$module]['folder'];
    $titleCol  = $map[$module]['title'];
    $logAction = $map[$module]['log_action'];

    // ✅ Get file name and title first
    $sql = "SELECT file_name, {$titleCol} AS title FROM {$table} WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {

        $row      = $result->fetch_assoc();
        $fileName = $row['file_name'] ?? '';
        $docTitle = $row['title'] ?? '';

        $filePath = $folder . $fileName;

        // ✅ Delete from database
        $deleteSql = "DELETE FROM {$table} WHERE id = ?";
        $deleteStmt = $conn->prepare($deleteSql);
        $deleteStmt->bind_param("i", $id);

        if ($deleteStmt->execute()) {

            // ✅ Delete physical file if it exists
            if (!empty($fileName) && file_exists($filePath)) {
                unlink($filePath);
            }

            // ✅ Log delete
            logAction(
                $conn,
                $user_id,
                $module,
                $id,
                $logAction,
                "Deleted archived document: {$docTitle}"
            );

            $_SESSION['flash'] = "✅ Document deleted successfully.";

        } else {
            $_SESSION['flash'] = "❌ Failed to delete the document from database.";
        }

        $deleteStmt->close();

    } else {
        $_SESSION['flash'] = "⚠️ Document not found.";
    }

    $stmt->close();

} else {
    $_SESSION['flash'] = "⚠️ Invalid request.";
}

header("Location: " . ($_SERVER['HTTP_REFERER'] ?? "../admin/archived_documents.php"));
exit();
?>
