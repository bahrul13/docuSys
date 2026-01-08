<?php
// No session_start() here, already started in main file
// No db_conn include here, already included in main file

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Trim inputs
    $fullname = trim($_POST['fullname']);
    $dept = trim($_POST['department']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    // 1️⃣ Basic empty check
    if (empty($fullname) || empty($dept) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } 
    // 2️⃣ Email format
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } 
    // 3️⃣ Full name validation (letters, spaces, dash, apostrophe)
    elseif (!preg_match("/^[a-zA-Z-' ]+$/", $fullname)) {
        $error = "Full name can only contain letters, spaces, apostrophes, and dashes.";
    } 
    
    // 4️⃣ Password strength: min 8 chars, at least one letter and one number
    elseif (!preg_match("/^(?=.*[A-Za-z])(?=.*\d).{8,}$/", $password)) {
        $error = "Password must be at least 8 characters and include at least one letter and one number.";
    } 
    else {

        // Sanitize fullname for DB
        $fullname = htmlspecialchars($fullname, ENT_QUOTES, 'UTF-8');

        // 5️⃣ Check if email already exists
        $check = $conn->prepare("SELECT id FROM user WHERE email = ?");
        if (!$check) {
            $error = "Database error. Try again.";
        } else {
            $check->bind_param("s", $email);
            $check->execute();
            $check->store_result();

            if ($check->num_rows > 0) {
                $error = "Email already registered.";
            } else {

                // 6️⃣ Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // 7️⃣ Insert user with status 'pending'
                $stmt = $conn->prepare("
                    INSERT INTO user (fullname, dept, email, password, role, status)
                    VALUES (?, ?,  ?, ?, 'user', 'pending')
                ");

                if (!$stmt) {
                    $error = "Database error. Try again.";
                } else {
                    $stmt->bind_param("ssss", $fullname, $dept, $email, $hashed_password);

                    if ($stmt->execute()) {
                        $success = "Registration successful! Please wait for admin approval.";
                    } else {
                        $error = "Registration failed. Try again.";
                    }
                }
            }
        }
    }
}
?>
