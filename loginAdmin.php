<?php
require 'config.php';
session_start();
$_SESSION = array();
session_destroy();
session_start();

$error_message = ''; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

  if (empty($username) || empty($password)) {
        $error_message = 'Please enter both username and password.';
    } else {
    $stmt = $conn->prepare("SELECT id, password, firstName, lastName, is_verified, profile_picture FROM admins WHERE username = ?");
    if ($stmt) {
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($adminId, $hashedPassword, $firstName, $lastName, $is_verified, $profilePicture);

        if ($stmt->num_rows === 1) {
            $stmt->fetch();

            if (password_verify($password, $hashedPassword)) {
                if ($is_verified) {
                    $_SESSION['admin_id'] = $adminId;
                    $_SESSION['admin_username'] = $username;
                    $_SESSION['admin_firstName'] = $firstName;
                    $_SESSION['admin_lastName'] = $lastName;
                    $_SESSION['role'] = 'admin';
                    $_SESSION['profile_picture'] = $profilePicture;
                    header('Location: admin/Dashboard.php');
                    exit();
             } else {
                        $error_message = 'Your account is not verified.';
                    }
                } else {
                    $error_message = 'Invalid username or password.';
                }
            } else {
                $error_message = 'No such admin found.';
            }
            $stmt->close();
        } else {
            $error_message = 'Database query error: ' . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Log-In</title>
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
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 10px;
        }
        .container {
            max-width: 400px;
            width: 100%;
            background-color: rgba(255, 255, 255, 0.85);
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 60px; /* Add some space for footer */
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

        /* Footer Styling */
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
        .alert {
            display: <?= $error_message ? 'block' : 'none' ?>;
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
    <h1>Admin Login</h1>
            <!-- Display error message -->
        <?php if ($error_message): ?>
        <div id="error-message" class="alert alert-danger text-center" role="alert">
            <?= htmlspecialchars($error_message) ?>
        </div>
        <?php endif; ?>

    
    <form id="loginForm" action="" method="post">
        <div class="form-group">
            <input type="text" id="username" name="username" class="form-control" placeholder="Username" required>
        </div>
        <div class="form-group">
            <input type="password" id="password" name="password" class="form-control" placeholder="Password" required>
        </div>
        <button type="submit">Login</button>
        <div class="text-center mt-3">
            <p>Don't have an account? <a href="adminRegistration.php">Sign up here</a></p>
            <p class="forgot-password">Forgot password? <a href="forgot_passwordAdmin.php">Reset it</a></p>
        </div>
    </form>
</div>

<!-- Footer -->
<footer>
    <p>&copy; 2024 Caniogan High School. All rights reserved.</p>
</footer>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>

