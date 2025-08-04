<?php
include 'db/db_conn.php'; // Make sure this defines $conn

$name = "Bahrul";
$email = "ungadbahrul94@gmail.com";
$password = "password"; // Plain text password

// ✅ Hash the password before storing
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// ✅ Define the role
$role = "admin"; // Can be 'admin' or 'user'

// ✅ Prepare and execute the INSERT query
$stmt = $conn->prepare("INSERT INTO user (fullname, email, password, role) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $name, $email, $hashed_password, $role);

if ($stmt->execute()) {
    echo "Admin user inserted successfully.";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
