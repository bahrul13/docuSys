<?php
include '../db/db_conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $program_name = trim($_POST['program_name']);

    if (!empty($program_name) && !empty($program)) {
        // Check if program_name already exists
        $check_stmt = $conn->prepare("SELECT id FROM programs WHERE name = ?");
        $check_stmt->bind_param("s", $program_name);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            // Program name already exists
            header("Location: ../users/programs.php?message=Error: Program name already exists.");
            exit();
        }

        // Proceed with insert
        $stmt = $conn->prepare("INSERT INTO programs (name) VALUES (?)");
        $stmt->bind_param("s", $program_name);

        if ($stmt->execute()) {
            header("Location: ../users/programs.php?message=Program added successfully");
            exit();
        } else {
            header("Location: ../users/programs.php?message=Error: Could not save the program.");
            exit();
        }
    } else {
        header("Location: ../users/programs.php?message=Error: All fields are required.");
        exit();
    }
}
?>
