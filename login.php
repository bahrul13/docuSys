<?php
session_start();
include 'db/db_conn.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Secure SQL query using prepared statements
    $stmt = $conn->prepare("SELECT * FROM user WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role']; // ✅ Store user role

        // ✅ Redirect based on role
        if ($user['role'] === 'admin') {
            header("Location: ../users/dashboard.php");
        } else {
            header("Location: ../users/dashboard.php");
        }
        exit();
    } else {
        $error = "Invalid email or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE-edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.8/css/line.css">
    <link rel="stylesheet" href="../css/login.css">
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/uploads/dms.png">
    
    <title>Login</title>
</head>
<body>
    <div class="container">
        <div class="forms">
            <div class="form login">
                <div class="logo">
                    <img src="../uploads/dms.png" alt="Logo" />
                </div>
                <span class="title">Login</span>

                <?php if ($error): ?>
                    <p style="color: red; text-align: center;"><?= $error ?></p>
                <?php endif; ?>

                <form method="POST" action="login.php">
                    <div class="input-field">
                        <input type="email" name="email" placeholder="Enter email" required>
                        <i class="uil uil-envelope"></i>
                    </div>
                    <div class="input-field">
                        <input type="password" name="password" placeholder="Enter password" required>
                        <i class="uil uil-lock icon"></i>
                        <i class="uil uil-eye-slash showHidePw"></i>
                    </div>
                    <div class="checkbox-text">
                        <div class="checkbox-content">    
                            <input type="checkbox" id="logCheck">
                            <label for="logCheck" class="text">Remember me</label>
                        </div>
                        <a href="../includes/forgot_password.php" class="text">Forgot password?</a>
                    </div>
                    <div class="input-field button">
                        <input type="submit" value="Login">
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../js/login.js"></script>
</body>
</html>
