<?php
session_start();
include '../db/db_conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $program_name = trim($_POST['program_name']);

    if (!empty($program_name)) {
        // Check if program_name already exists
        $check_stmt = $conn->prepare("SELECT id FROM programs WHERE name = ?");
        $check_stmt->bind_param("s", $program_name);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            $_SESSION['flash'] = "❌ Program name already exists.";
            header("Location: ../users/programs.php");
            exit();
        }

        // Proceed with insert
        $stmt = $conn->prepare("INSERT INTO programs (name) VALUES (?)");
        $stmt->bind_param("s", $program_name);

        if ($stmt->execute()) {
            $_SESSION['flash'] = "✅ Program added successfully.";
        } else {
            $_SESSION['flash'] = "❌ Error: Could not save the program.";
        }

        header("Location: ../users/programs.php");
        exit();
    } else {
        $_SESSION['flash'] = "❌ All fields are required.";
        header("Location: ../users/programs.php");
        exit();
    }
}
