<?php
include '../db/db_conn.php';
date_default_timezone_set('Asia/Manila');

$token = $_GET['token'] ?? '';
$errors = [];
$success = false; // Flag for modal display

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'];
    $password = $_POST['password'];
    $confirm = $_POST['confirm'];

    if (empty($password) || empty($confirm)) {
        $errors[] = "Please fill in both password fields.";
    } elseif ($password !== $confirm) {
        $errors[] = "Passwords do not match.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM user WHERE reset_token = ? AND reset_expiry >= NOW()");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $errors[] = "Invalid or expired token.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE user SET password = ?, reset_token = NULL, reset_expiry = NULL WHERE reset_token = ?");
            $update->bind_param("ss", $hashed, $token);
            $update->execute();

            $success = true;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #800000;
            padding: 50px;
            text-align: center;
            color: white;
        }
        form {
            background: white;
            padding: 30px;
            max-width: 400px;
            margin: auto;
            border-radius: 8px;
            box-shadow: 0px 2px 8px rgba(0,0,0,0.2);
            color: black;
        }
        input[type="password"], input[type="submit"] {
            font-family: 'Poppins', sans-serif;
            padding: 10px;
            width: 90%;
            margin: 10px 0;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
        input[type="submit"] {
            background-color: #800000;
            color: white;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #a00000;
        }
        .error {
            color: yellow;
        }
        .time-display {
            margin-top: 20px;
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: #fff;
            margin: 15% auto;
            padding: 20px;
            border-radius: 8px;
            width: 80%;
            max-width: 400px;
            text-align: center;
            color: black;
        }
        .modal-content a {
            color: #800000;
            font-weight: bold;
            text-decoration: none;
        }
    </style>
</head>
<body>



<?php
if (!empty($errors)) {
    foreach ($errors as $error) {
        echo "<div class='error'>{$error}</div>";
    }
}
?>

<form method="POST" action="">
    <h2>Reset Your Password</h2>
    <br>
    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
    <input type="password" name="password" placeholder="New Password" required>
    <input type="password" name="confirm" placeholder="Confirm Password" required>
    <input type="submit" value="Reset Password">
</form>

<!-- <div class="time-display">
    <p>Server Time (Asia/Manila): <strong><?php echo date("Y-m-d H:i:s"); ?></strong></p>
</div> -->

<?php if ($success): ?>
<!-- Modal HTML -->
<div id="successModal" class="modal">
    <div class="modal-content">
        <h3>Password Reset Successful!</h3>
        <p><a href="../login.php">Click here to Login</a></p>
    </div>
</div>

<!-- Modal Script -->
<script>
    window.onload = function () {
        document.getElementById("successModal").style.display = "block";
    };
</script>
<?php endif; ?>

</body>
</html>
