<?php
session_start();
include 'config.php';

// Function to validate login
function validateLogin($username, $password, $conn) {
    $stmt = $conn->prepare("SELECT user_id, first_name, last_name, username, password, role, first_login, teacher_id, student_id FROM users WHERE username = ?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if ($user && password_verify($password, $user['password'])) {
        return $user;
    }
    return false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $user = validateLogin($username, $password, $conn);

    if ($user) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['first_login'] = $user['first_login'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];
        
        // Add teacher_id or student_id to the session
        if ($user['role'] === 'teacher') {
            $_SESSION['teacher_id'] = $user['teacher_id'];

            // Fetch profile picture from the database
            $profilePictureQuery = "SELECT profile_picture FROM teachers WHERE teacher_id = ?";
            $profilePictureStmt = $conn->prepare($profilePictureQuery);
            $profilePictureStmt->bind_param("i", $user['teacher_id']);
            $profilePictureStmt->execute();
            $pictureResult = $profilePictureStmt->get_result();
            $pictureData = $pictureResult->fetch_assoc();
                        
            // Set profile picture in session
            $_SESSION['profile_picture'] = $pictureData['profile_picture'];
        } else if ($user['role'] === 'student') {
            $_SESSION['student_id'] = $user['student_id'];
            
            // Fetch profile picture from the database
            $profilePictureQuery = "SELECT profile_picture FROM students WHERE student_id = ?";
            $profilePictureStmt = $conn->prepare($profilePictureQuery);
            $profilePictureStmt->bind_param("i", $user['student_id']);
            $profilePictureStmt->execute();
            $pictureResult = $profilePictureStmt->get_result();
            $pictureData = $pictureResult->fetch_assoc();
            
            // Set profile picture in session
            $_SESSION['profile_picture'] = $pictureData['profile_picture'];
        }
        
        // Redirect based on role
        if ($user['first_login']) {
            header("Location: change_password.php");
        } else {
            if ($user['role'] === 'teacher') {
                header("Location: Teacher/Dashboard.php");
            } else if ($user['role'] === 'student') {
                header("Location: Student/Dashboard.php");
            }
        }
        exit();
    } else {
        $loginError = "Invalid username or password.";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Login</title>
    <link rel="icon" type="image/x-icon" href="image/favicon.ico">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <style>
        body {
            background: url('image/background.jpg') center/cover no-repeat;
            font-family: Arial, sans-serif;
            min-height: 100vh;
            display: flex;
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
        <h1>Login to your account</h1>

           <!-- Display error message -->
    <?php if (!empty($loginError)): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo $loginError; ?>
        </div>
    <?php endif; ?>
    
        <form id="loginForm" action="" method="post">
            <div class="form-group">
                <input type="text" id="username" name="username" class="form-control" placeholder="Username" required>
            </div>
            <div class="form-group">
                <input type="password" id="password" name="password" class="form-control" placeholder="Password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Log in</button>
            <div class="text-center mt-3">
                <p>Forgot password? <a href="forgot_password.php">Click here</a></p>
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
