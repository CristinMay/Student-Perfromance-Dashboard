<?php
// Start the session to store role information
session_start();

// Check if a role is selected
if (isset($_GET['role'])) {
    $role = $_GET['role'];
    $_SESSION['role'] = $role;

    // Redirect based on role
    if ($role === 'admin') {
        header("Location: loginAdmin.php"); // Admin login page
        exit();
    } elseif ($role === 'student' || $role === 'teacher') {
        header("Location: loginPage.php"); // Shared login page for student/teacher
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>EduPerformance Tracker - Login</title>
  <link rel="icon" type="image/x-icon" href="image/favicon.ico">
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/User Role Page.css">
  <style>
    body {
      background-image: url('image/background.jpg');
      background-size: cover;
      background-repeat: no-repeat;
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      color: #fff;
      position: relative;
    }

    /* Overlay to darken the background for readability */
    body::before {
      content: "";
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5); /* Adjust opacity as needed */
      z-index: -1;
    }

    .container {
      background: rgba(255, 255, 255, 0.8);
      padding: 40px;
      border-radius: 8px;
      max-width: 800px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
      display: flex;
    }

    .logo-section {
      flex: 1;
      display: flex;
      justify-content: center;
      align-items: center;
      text-align: center;
      padding-right: 20px;
    }

    .logo-section img {
      max-width: 100%;
      height: auto;
    }

    .login-section {
      flex: 1;
      text-align: center;
      padding-left: 20px;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .login-section h1 {
      font-size: 2.5rem;
      font-weight: bold;
      color: #333;
      margin-bottom: 30px;
    }

    .btn-custom {
      color: #fff;
      background-color: #007bff;
      border: none;
      border-radius: 5px;
      padding: 12px 20px;
      transition: background-color 0.3s, transform 0.2s;
      margin-top: 10px;
    }

    .btn-custom:hover {
      background-color: #0056b3;
      transform: translateY(-2px);
    }

    .btn-custom:focus {
      box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.5);
    }
  </style>
</head>
<body>
  <div class="container">
    <!-- Left side: Logo Section -->
    <div class="logo-section">
      <img src="image/logo.png" alt="School Logo">
    </div>

    <!-- Right side: Login Section -->
    <div class="login-section">
      <h1>Class GradeScope Hub</h1>
      <div class="row">
        <div class="col-12 mb-3">
          <form action="" method="get">
            <input type="hidden" name="role" value="student">
            <button type="submit" class="btn btn-custom btn-lg btn-block">Teacher/Student Login</button>
          </form>
        </div>
        <div class="col-12 mb-3">
          <form action="" method="get">
            <input type="hidden" name="role" value="admin">
            <button type="submit" class="btn btn-custom btn-lg btn-block">Admin</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
