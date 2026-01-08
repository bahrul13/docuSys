<?php
session_start();
include 'db/db_conn.php';
include 'includes/login.php';


if (!empty($error)) {
    $_SESSION['login_error_shown'] = true;
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

                <form method="POST" action="index.php">
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
                <div style="text-align: center; margin-top: 15px;">
                    <span class="text">Don't have an account?</span>
                    <a href="registration_form.php" style="color: #4070f4; font-weight: 500;">
                        Create an account
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php if (!empty($error)): ?>
        <div id="popupModal" class="modal">
            <div class="modal-content error">
                <h3>❌ Login Failed</h3>
                <p><?= htmlspecialchars($error) ?></p>
                <button onclick="closePopup()">OK</button>
            </div>
        </div>
    <?php endif; ?>
    <script>
        function closePopup() {
            document.getElementById("popupModal").style.display = "none";
        }
    </script>

    <script src="../js/login.js"></script>

</body>
</html>
