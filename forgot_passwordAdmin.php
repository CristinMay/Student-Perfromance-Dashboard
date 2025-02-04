<?php
require 'config.php'; // Include your database configuration file
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

session_start();

// Initialize message variables
$message = '';
$success = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input
    $email = trim($_POST['email']);

    // Validate email
    if (empty($email)) {
        $message = 'Please enter your email.';
    } else {
        // Check if email exists in the database
        $stmt = $conn->prepare("SELECT id FROM admins WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($userId);
            $stmt->fetch();

            // Generate a unique token
            $token = bin2hex(random_bytes(50));

            // Store token in the database with an expiry time (optional)
            $expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));
            $stmt = $conn->prepare("INSERT INTO password_resets (user_id, token, expiry) VALUES (?, ?, ?)");
            $stmt->bind_param('iss', $userId, $token, $expiry);
            $stmt->execute();

            // Send reset email
            $resetLink = "http://tin.free.nf/reset_passwordAdmin.php?token=$token"; // Replace with your domain
            $mail = new PHPMailer(true);

            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';  // Specify your SMTP server
                $mail->SMTPAuth = true;
                $mail->Username = 'cristinmaygojocruz04@gmail.com';  // Your Gmail address
                $mail->Password = 'mrpsnebelljbiexa';  // Your Gmail password or app-specific password
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;
        
                $mail->setFrom('cristinmaygojocruz04@gmail.com', 'EduPerformance Tracker');
                $mail->addAddress($email);
                // Content
                $mail->isHTML(true);
                $mail->Subject = 'Password Reset Request';
                $mail->Body = '
<html>
<head>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            background-color: #f4f4f4; 
            color: #333; 
            padding: 20px; 
            margin: 0;
        }
        .container { 
            width: 100%; 
            max-width: 600px; 
            margin: 0 auto; 
            background-color: #ffffff; 
            border-radius: 8px; 
            padding: 20px; 
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); 
        }
        h1 { 
            color: #007bff; 
            text-align: center; 
        }
        .content { 
            font-size: 16px; 
            line-height: 1.5; 
            margin-bottom: 20px;
        }
        .reset-link { 
            display: inline-block; 
            background-color: #007bff; 
            color: #fff; 
            padding: 10px 20px; 
            border-radius: 5px; 
            text-decoration: none; 
            font-size: 16px;
        }
        .footer { 
            font-size: 14px; 
            color: #666; 
            text-align: center; 
            margin-top: 20px; 
        }
        .footer a { 
            color: #007bff; 
            text-decoration: none; 
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Password Reset Request</h1>
        <p class="content">Hello,</p>
        <p class="content">We received a request to reset your password. Please click the link below to reset it:</p>
        <p class="content" style="text-align: center;">
            <a href="' . $resetLink . '" class="reset-link">Reset Your Password</a>
        </p>
        <p class="footer">
            If you did not request a password reset, please ignore this email.<br>
            <a href="http://yourwebsite.com">Visit our website</a> for more information.
        </p>
    </div>
</body>
</html>';

                $mail->send();
                $success = true;
                $message = 'A password reset link has been sent to your email.';
            } catch (Exception $e) {
                $message = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            $message = 'No account found with that email.';
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="icon" type="image/x-icon" href="image/favicon.ico">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: url('image/background.jpg') center/cover no-repeat;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
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
    <a href="loginAdmin.php" class="back-link">
                 <i class="fas fa-arrow-left"></i> Back
    </a>
        <h1>Forgot Your Password?</h1>
        <p class="text-center">Please enter your email to receive a password reset link.</p>
        
        <?php if ($message): ?>
            <div class="alert <?= $success ? 'alert-success' : 'alert-danger' ?>" role="alert">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <form action="forgot_passwordAdmin.php" method="post">
            <div class="form-group">
                <label for="email">Enter your email:</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Reset Password</button>
            
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
