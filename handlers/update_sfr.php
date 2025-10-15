<?php
session_start();
require '../db/db_conn.php';

// Check if admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['flash'] = "Access denied.";
    header("Location: ../users/sfr.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $name = $_POST['program_name'];
    $survey_type = $_POST['survey_type'];
    $survey_date = $_POST['survey_date'];


    if (isset($_FILES['file_name']) && $_FILES['file_name']['error'] === 0) {
        $fileName = basename($_FILES['file_name']['name']);
        $targetDir = "../uploads/sfr/";
        $targetFile = $targetDir . $fileName;

        if (move_uploaded_file($_FILES['file_name']['tmp_name'], $targetFile)) {
            $stmt = $conn->prepare("UPDATE sfr SET program_name = ?, survey_type = ?, survey_date = ?, file_name = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $name, $survey_type, $survey_date, $fileName, $id);
        } else {
            $_SESSION['flash'] = "❌ File upload failed.";
            header("Location: ../users/sfr.php");
            exit();
        }
    } else {
        $stmt = $conn->prepare("UPDATE sfr SET program_name = ?, survey_type = ?, survey_date = ? WHERE id = ?");
        $stmt->bind_param("sssi", $name, $survey_type, $survey_date, $id);
    }

    if ($stmt->execute()) {
        $_SESSION['flash'] = "✅ SFR record updated successfully.";
    } else {
        $_SESSION['flash'] = "❌ Failed to update SFR record.";
    }

    $stmt->close();
    $conn->close();
    
    header("Location: ../users/sfr.php");
    exit();
} else {
    $_SESSION['flash'] = "⚠️ Invalid request method.";
    header("Location: ../users/sfr.php");
    exit();
}
?>
