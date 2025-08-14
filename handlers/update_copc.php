<?php
session_start();
require '../db/db_conn.php';

// Check if admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['flash'] = "Access denied.";
    header("Location: ../users/programs.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $program = $_POST['program'];
    $issuance_date = $_POST['issuance_date'];

    if (isset($_FILES['file_name']) && $_FILES['file_name']['error'] === 0) {
        $fileName = basename($_FILES['file_name']['name']);
        $targetDir = "../uploads/copc/";
        $targetFile = $targetDir . $fileName;

        if (move_uploaded_file($_FILES['file_name']['tmp_name'], $targetFile)) {
            $stmt = $conn->prepare("UPDATE copc SET program = ?, issuance_date = ?, file_name = ? WHERE id = ?");
            $stmt->bind_param("sssi", $program, $issuance_date, $fileName, $id);
        } else {
            $_SESSION['flash'] = "❌ File upload failed.";
            header("Location: ../users/copc.php");
            exit();
        }
    } else {
        $stmt = $conn->prepare("UPDATE copc SET program = ?, issuance_date = ? WHERE id = ?");
        $stmt->bind_param("ssi", $program, $issuance_date, $id);
    }

    if ($stmt->execute()) {
        $_SESSION['flash'] = "✅ COPC record updated successfully.";
    } else {
        $_SESSION['flash'] = "❌ Failed to update COPC record.";
    }

    $stmt->close();
    $conn->close();
    
    header("Location: ../users/copc.php");
    exit();
} else {
    $_SESSION['flash'] = "⚠️ Invalid request method.";
    header("Location: ../users/copc.php");
    exit();
}
?>
