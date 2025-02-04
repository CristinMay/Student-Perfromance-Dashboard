<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verified - Admin</title>
    <link rel="icon" type="image/x-icon" href="image/favicon.ico">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito+Sans:400,400i,700,900&display=swap" rel="stylesheet">
    <style>
        body {
            background: url('image/background.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #333;
        }
        .card {
            background-color: rgba(255, 255, 255, 0.9); /* Slightly transparent background */
        }
        .checkmark {
            font-size: 60px;
            color: #28a745;
        }
        header img {
            max-height: 80px;
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">

    <header class="text-center py-4">
        <img src="image/logo.png" alt="Logo" class="img-fluid">
        <h1 class="mt-3">Performance Tracker</h1>
    </header>

    <div class="container d-flex flex-column justify-content-center align-items-center flex-grow-1">
        <div class="card text-center p-4" style="max-width: 600px;">
            <div class="checkmark mb-4">
                ✓
            </div>
            <h1 class="mb-3">Please Verify Your Account</h1>
            <p>We’ve sent a verification link to your email address. Please check your inbox and verify your account.</p>
            <a href="loginAdmin.php" class="btn btn-primary">Go to Admin Login</a>
        </div>
    </div>

    <footer class="text-center py-3 bg-light mt-auto">
        <p class="mb-0">© 2024 EduPerformance Tracker. All rights reserved.</p>
    </footer>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
