<?php
require 'config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    if ($password !== $confirmPassword) {
        echo "<script>alert('Passwords do not match!'); window.history.back();</script>";
        exit;
    }

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Check if email already exists
    $stmt = $conn->prepare("SELECT COUNT(*) FROM admins WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
        echo "<script>alert('This email is already registered! Please use a different email.'); window.history.back();</script>";
        exit;
    }
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $conn->prepare("SELECT COUNT(*) FROM admins WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    $stmt = $conn->prepare("INSERT INTO admins (firstName, lastName, email, username, password) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param('sssss', $firstName, $lastName, $email, $username, $hashedPassword);
    $stmt->execute();
    $stmt->close();

    $token = bin2hex(random_bytes(50));

    $stmt = $conn->prepare("INSERT INTO email_verifications (email, token) VALUES (?, ?)");
    $stmt->bind_param('ss', $email, $token);
    $stmt->execute();
    $stmt->close();

    // Send verification email
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
        $mail->isHTML(true);
       $mail->Subject = 'Verify Your Email Address';

            $mail->Body = '
            <!DOCTYPE html>
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; color: #333333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #dddddd; border-radius: 5px; }
                    .header { text-align: center; background-color: #4CAF50; color: white; padding: 10px 0; font-size: 24px; }
                    .content { padding: 20px; }
                    .button { display: inline-block; padding: 10px 20px; margin-top: 20px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 5px; }
                    .footer { margin-top: 30px; font-size: 12px; color: #777777; text-align: center; }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="header">
                         Email Verification
                    </div>
                    <div class="content">
                        <p>Dear Administrator,</p>
                        <p>Thank you for registering as an administrator in our system. To complete your registration and activate your account, please verify your email address by clicking the button below:</p>
                        <a href="http://tin.free.nf/verify_emailAdmin.php?token=' . $token . '" class="button">Verify Email Address</a>
                        <p>If the button above doesn’t work, you can also verify your email by copying and pasting the following link into your web browser:</p>
                        <p><a href="http://tin.free.nf/verify_emailAdmin.php?token=' . $token . '">http://tin.free.nf/verify_emailAdmin.php?token=' . $token . '</a></p>
                        <p>If you did not initiate this request, please ignore this email.</p>
                        <p>Thank you,<br>Caniogan High School</p>
                    </div>
                    <div class="footer">
                        &copy; ' . date("Y") . ' | This is an automated message, please do not reply.
                    </div>
                </div>
            </body>
            </html>';


        $mail->send();

        // Redirect to "Please verify your email" page after the email is sent
        header('Location: Verify_email.php');
        exit;
    } catch (Exception $e) {
        echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Registration Page</title>
    <link rel="icon" type="image/x-icon" href="image/favicon.ico">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <style>
body {
    background: url('image/background.jpg') center/cover no-repeat;
    background-size: cover;
    font-family: Arial, sans-serif;
    min-height: 100vh;
    display: flex;
    justify-content: center; /* Center horizontally */
    align-items: center; /* Center vertically */
    margin: 0; /* Remove default margins */
}


.container {
    max-width: 400px;
    width: 100%;
    background-color: rgba(255, 255, 255, 0.85);
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    margin: 0 auto;
    margin-bottom: 10px; /* Remove margin at the bottom */
}
        .header-logo {
            text-align: center;
            margin-bottom: 20px;
        }
        .header-logo img {
            max-width: 100px;
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 1.8rem;
            color: #004085;
        }
        .form-group input {
            border-radius: 5px;
            margin-bottom: 15px;
            padding: 10px;
            font-size: 1rem;
            border: 1px solid #ccc;
        }
        .form-group input:focus {
            border-color: #0056b3;
            box-shadow: 0 0 5px rgba(0, 86, 179, 0.5);
        }
        button {
            border-radius: 5px;
            padding: 10px;
            font-size: 1.1rem;
            width: 100%;
            background-color: #004085;
            color: #fff;
            border: none;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #003366;
        }
        .text-center p {
            font-size: 0.9rem;
        }
        .text-center a {
            color: #004085;
            text-decoration: none;
        }
        .text-center a:hover {
            text-decoration: underline;
        }
        .forgot-password {
            margin-top: 10px;
            font-size: 0.9rem;
        }
        

        footer {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            background-color: rgba(255, 255, 255, 0.85);
            padding-bottom: 0;
            text-align: center;
            box-shadow: 0 -1px 5px rgba(0, 0, 0, 0.1);
            font-size:10px ;
        }

footer a {
    color: #004085;
    text-decoration: none;
}

footer a:hover {
    text-decoration: underline;
}
        /* Responsive adjustments */
        @media (max-width: 576px) {
            h1 {
                font-size: 1.5rem;
            }
            .header-logo img {
                max-width: 80px;
            }
            .container {
                padding: 20px;
            }
            button {
                font-size: 1rem;
            }
            .text-center p, .forgot-password {
                font-size: 0.85rem;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header-logo">
        <img src="image/logo.png" alt="Logo">
    </div>
    <h1>Admin Registration</h1>
    
    <!-- Error message container -->
    <div id="error-message" class="alert alert-danger text-center" role="alert" style="display: none;"></div>
    
    <form id="registrationForm" method="POST" action="">
        <div class="form-group">
            <input type="text" id="firstName" name="firstName" class="form-control" placeholder="First Name" required>
            <input type="text" id="lastName" name="lastName" class="form-control" placeholder="Last Name" required>
            <input type="email" id="email" name="email" class="form-control" placeholder="Email Address" required>
            <input type="text" id="username" name="username" class="form-control" placeholder="Username" required>
            <input type="password" id="password" name="password" class="form-control" placeholder="Password" required>
            <input type="password" id="confirmPassword" name="confirmPassword" class="form-control" placeholder="Confirm Password" required>
        </div>
        <button type="submit">Register</button>
        <div class="text-center mt-3">
            <p>Already have an account? <a href="loginAdmin.php">Click to sign in</a></p>
        </div>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
    document.getElementById('registrationForm').addEventListener('submit', function(event) {
        var password = document.getElementById('password').value;
        var confirmPassword = document.getElementById('confirmPassword').value;

        if (password !== confirmPassword) {
            alert("Passwords do not match!");
            event.preventDefault(); // Prevent form submission
        }
    });
</script>

    <!-- Footer -->
    <footer>
    <p>&copy; 2024 Caniogan High School. All rights reserved.</p>
</footer>


</body>
</html>
