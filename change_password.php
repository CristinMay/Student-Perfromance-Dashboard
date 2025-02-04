<?php
session_start();
include 'config.php';

// Check if user is logged in and needs to change password
if (!isset($_SESSION['user_id']) || !$_SESSION['first_login']) {
    header("Location: loginPage.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];

    if ($newPassword !== $confirmPassword) {
        $error = "Passwords do not match.";
    } else {
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

        // Update the password and first_login flag
        $stmt = $conn->prepare("UPDATE users SET password = ?, first_login = 0 WHERE user_id = ?");
        $stmt->bind_param('si', $hashedPassword, $_SESSION['user_id']);
        $stmt->execute();

        // Check if the update was successful
        if ($stmt->affected_rows > 0) {
            // Redirect to the appropriate dashboard
            if ($_SESSION['role'] === 'teacher') {
                header("Location: Teacher/Dashboard.php");
            } else if ($_SESSION['role'] === 'student') {
                header("Location: Student/Dashboard.php");
            }
            exit();
        } else {
            $error = "Failed to update the password. Please try again.";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email Page</title>
    <link rel="icon" type="image/x-icon" href="image/favicon.ico">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/Forced Change Password Page.css">
    <style>
        body {
            background: url('image/background.jpg') center/cover no-repeat;
        }
    </style>
</head>
<body>

    <header>
        <img src="image/logo.png" alt="Logo">
        <h1 class="ml-4">EduPerformance Tracker - Forces Change Password Page</h1>
    </header>

    <div class="login-container">
        <h1>Reset Password</h1>
        <p>We require you to change password for your security. Thank you!</p>
        <form method="POST" action="">
            <div class="form-group mb-3">
                <input type="password" name="password" placeholder="Password" class="form-control" id="password" required>
            </div>
            <div class="form-group mb-3">
                <input type="password" name="confirmPassword" placeholder="Confirm Password" class="form-control" id="confirmPassword" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Reset Password</button>
        </form>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.amazonaws.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
