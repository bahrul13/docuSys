<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- CSS -->
    <link rel="stylesheet" href="../css/style.css">

    <!-- Boxicons CSS -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    
    <title>DMS</title>
</head>
<body>
    <?php include('includes/sidebar.php'); ?>
    
    <section class="dashboard-content">
        <h1>Welcome to the Document Management System</h1>
        <div class="cards">
            <div class="card">
                <i class='bx bx-file'></i>
                <div>
                    <h3>120</h3>
                    <p>Total Documents</p>
                </div>
            </div>
            <div class="card">
                <i class='bx bx-user'></i>
                <div>
                    <h3>15</h3>
                    <p>Registered Users</p>
                </div>
            </div>
            <div class="card">
                <i class='bx bx-cloud-upload'></i>
                <div>
                    <h3>8</h3>
                    <p>Uploads Today</p>
                </div>
            </div>
        </div>
    </section>

    <script src="../js/script.js"></script>
</body>
</html>
