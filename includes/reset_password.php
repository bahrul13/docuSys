<?php
include '../db/db_conn.php';
date_default_timezone_set('Asia/Manila');

$token = $_GET['token'] ?? '';
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'];
    $password = $_POST['password'];
    $confirm  = $_POST['confirm'];

    // ✅ Backend validation (8–12 alphanumeric ONLY)
    if (empty($password) || empty($confirm)) {
        $errors[] = "Please fill in both password fields.";
    } 
    elseif ($password !== $confirm) {
        $errors[] = "Passwords do not match.";
    } 
    elseif (!preg_match(
    '/^(?=.*[A-Za-z])(?=.*\d)(?=.*[^A-Za-z0-9])[A-Za-z\d^$*.[]{}()?"!@#%&\/\\\\,><\':;|_~`+=-]{8,12}$/', $password)) {
    $errors[] = "Password must be 8–12 characters and include letters, numbers, and special characters.";
    } 
    else {
        $stmt = $conn->prepare(
            "SELECT id FROM user 
             WHERE reset_token = ? 
             AND reset_expiry >= NOW()"
        );
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $errors[] = "Invalid or expired reset link.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);

            $update = $conn->prepare(
                "UPDATE user 
                 SET password = ?, reset_token = NULL, reset_expiry = NULL 
                 WHERE reset_token = ?"
            );
            $update->bind_param("ss", $hashed, $token);
            $update->execute();

            $success = true;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="/uploads/dms.png">
    <link rel="stylesheet" href="../css/resetpass.css">
</head>
<body>

<?php if (!empty($errors)): ?>
    <?php foreach ($errors as $error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endforeach; ?>
<?php endif; ?>

<form method="POST">
    <h2>Reset Your Password</h2>

    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

    <!-- Password -->
    <input
        type="password"
        id="password"
        name="password"
        placeholder="8–20 chars (letters, numbers & special characters)"
        required
    >


    <!-- Strength Indicator -->
    <div class="password-strength">
        <div id="strengthBars" class="strength-bars">
            <span></span><span></span><span></span><span></span>
        </div>
        <div id="strengthText" class="strength-text">
            8–20 characters, letters & numbers only
        </div>
    </div>

    <!-- Confirm -->
    <input
        type="password"
        name="confirm"
        placeholder="Confirm Password"
        required
    >

    <input type="submit" value="Reset Password">
</form>

<?php if ($success): ?>
<div id="successModal" class="modal">
    <div class="modal-content">
        <h3>Password Reset Successful!</h3>
        <p><a href="../index.php">Click here to Login</a></p>
    </div>
</div>

<script>
    window.onload = () => {
        document.getElementById("successModal").style.display = "block";
    };
</script>
<?php endif; ?>

<!-- ✅ Password Strength Script -->
<script>
const passwordInput = document.getElementById("password");
const bars = document.getElementById("strengthBars");
const text = document.getElementById("strengthText");

passwordInput.addEventListener("input", () => {
    const pwd = passwordInput.value;

    bars.className = "strength-bars";
    text.style.color = "#444";

    if (pwd.length === 0) {
        text.textContent = "8–20 chars, letters, numbers & special characters";
        return;
    }

    const regex = /^(?=.*[A-Za-z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,20}$/;

    if (!regex.test(pwd)) {
        bars.classList.add("weak");
        text.textContent = "❌ Must include letters, numbers & special characters";
        text.style.color = "#e74c3c";
        return;
    }

    let strength = 0;
    if (/[A-Z]/.test(pwd)) strength++;
    if (/\d/.test(pwd)) strength++;
    if (/[^A-Za-z0-9]/.test(pwd)) strength++;
    if (pwd.length >= 10) strength++;

    if (strength <= 2) {
        bars.classList.add("medium");
        text.textContent = "Medium password";
        text.style.color = "#f39c12";
    } else {
        bars.classList.add("strong");
        text.textContent = "Strong password";
        text.style.color = "#2ecc71";
    }
});
</script>

</body>
</html>
