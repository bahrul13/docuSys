<?php
session_start();
include 'db/db_conn.php';
include 'includes/register.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE-edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.8/css/line.css">
    <link rel="stylesheet" href="../css/login.css">
    <link rel="icon" type="image/png" href="/uploads/dms.png">

    <title>Register</title>
</head>
<body>
<div class="container">
    <div class="forms">
        <div class="form login">
            <div class="logo">
                <img src="../uploads/dms.png" alt="Logo" />
            </div>

            <span class="title">Create Account</span>

            <!-- <?php if ($error): ?>
                <p style="color:red; text-align:center;"><?= $error ?></p>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?> -->


            <form method="POST">
                <div class="input-field">
                    <input type="text" name="fullname" placeholder="Full Name" required>
                    <i class="uil uil-user"></i>
                </div>

                <div class="input-field">
                    <select name="department" required>
                        <option value="" disabled selected hidden>Select Department</option>
                        <option value="CETC">College of Engineering, Technology and Computing</option>
                        <option value="CTED">College of Teacher Education</option>
                        <option value="CBPA">College of Business and Public Administration</option>
                        <option value="CAS">College of Arts and Science</option>
                        <option value="CIS">College of Islamic Studies</option>
                        <option value="CAFi">College of Agriculture and Fisheries</option>
                        <option value="GS">Graduate School</option>
                    </select>
                    <i class="uil uil-building"></i>
                </div>



                <div class="input-field">
                    <input type="email" name="email" placeholder="Email Address" required>
                    <i class="uil uil-envelope"></i>
                </div>

                <div class="input-field">
                    <input type="password" name="password" placeholder="Password" required>
                    <i class="uil uil-lock icon"></i>
                </div>

                <div class="input-field button">
                    <input type="submit" value="Create Account">
                </div>
            </form>

            <div style="text-align:center; margin-top:15px;">
                <span class="text">Already have an account?</span>
                <a href="../index.php" style="color:#4070f4; font-weight:500;">
                    Login here
                </a>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($error) || !empty($success)): ?>
<div id="popupModal" class="modal">
    <div class="modal-content <?= !empty($error) ? 'error' : 'success' ?>">
        <h3>
            <?= !empty($error) ? '❌ Error' : '✅ Success' ?>
        </h3>
        <p>
            <?= htmlspecialchars(!empty($error) ? $error : $success) ?>
        </p>
        <button onclick="closePopup()">OK</button>
    </div>
</div>
<?php endif; ?>


<?php if (!empty($success)): ?>
<div id="successModal" class="modal">
    <div class="modal-content">
        <h3>✅ Success</h3>
        <p><?= htmlspecialchars($success) ?></p>
        <button onclick="closeModal()">OK</button>
    </div>
</div>
<?php endif; ?>

<script>

function closePopup() {
    document.getElementById("popupModal").style.display = "none";

    // Redirect only if success
    <?php if (!empty($success)): ?>
        window.location.href = "index.php"; // login page
    <?php endif; ?>
}


function closeModal() {
    document.getElementById("successModal").style.display = "none";
    window.location.href = "index.php"; // redirect to login page
}
</script>

</body>
</html>
