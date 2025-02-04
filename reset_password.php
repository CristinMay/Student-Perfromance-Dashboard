<?php
require 'config.php'; // Include your database connection

// Initialize message variables
$message = '';
$success = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    // Check if the passwords match
    if ($password !== $confirmPassword) {
        $message = 'Passwords do not match.';
    } else {
        // Check if token is valid and not expired
        $stmt = $conn->prepare("SELECT user_id, expiry FROM password_resets WHERE token = ?");
        $stmt->bind_param('s', $token);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($userId, $expiry);
            $stmt->fetch();

            // Check if token has expired
            if (strtotime($expiry) > time()) {
                // Hash the new password
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);


                // Update the user's password
                $updateStmt = $conn->prepare("UPDATE admins SET password = ? WHERE id = ?");
                $updateStmt->bind_param('si', $hashedPassword, $userId);
                $updateStmt->execute();

                // Delete the token after successful password reset
                $deleteStmt = $conn->prepare("DELETE FROM password_resets WHERE token = ?");
                $deleteStmt->bind_param('s', $token);
                $deleteStmt->execute();

                // Redirect to login page after successful reset with token
                header('Location: loginPage.php?token=' . urlencode($token)); // Include the token in the redirect
                exit();

            } else {
                $message = 'This token has expired. Please request a new password reset.';
            }
        } else {
            $message = 'Invalid token. Please try again.';
        }
        $stmt->close();
    }
}

// Check if token is set via GET parameter
if (!isset($_GET['token'])) {
    die('No token provided.');
}
$token = $_GET['token'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="icon" type="image/x-icon" href="image/favicon.ico">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: url('image/background.jpg') center/cover no-repeat;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background-size: cover;
        }
        .container {
            background-color: rgba(255, 255, 255, 0.8);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }
        .btn-block {
            display: block;
            width: 100%;
        }
    </style>
</head>
<body>

    <div class="container col-md-4">
        <h1>Reset Your Password</h1>
        <p class="text-center">Please enter your new password.</p>

        <?php if ($message): ?>
            <div class="alert <?= $success ? 'alert-success' : 'alert-danger' ?>" role="alert">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <form action="reset_password.php" method="post">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
            <div class="form-group">
                <label for="password">New Password:</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Enter your new password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm New Password:</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm your new password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Reset Password</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
