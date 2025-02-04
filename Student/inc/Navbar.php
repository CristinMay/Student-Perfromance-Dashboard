<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['first_name']) && isset($_SESSION['last_name'])) {
    $adminName = htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']);
} else {
    $adminName = 'Guest';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet">
    <title>Admin Dashboard</title>
    <style>
        /* Profile Image Styling */
        #sidebarMenu .text-center img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 5px solid green;
            margin-bottom: 10px;
        }

        #sidebarMenu .text-center span {
            font-size: 1rem;
            font-weight: bold;
        }

        /* Sidebar Styling */
        #sidebarMenu {
            z-index: 1000;
        }

        /* Toggler Button Styling */
        .toggler-btn {
            position: fixed;
            top: 80px;
            left: 20px;
            z-index: 1020;
            background: white;
            border: none;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>
<body>
<!-- Sidebar -->
<nav id="sidebarMenu" class="collapse d-lg-block sidebar bg-white">
    <div class="position-sticky">
        <!-- Profile Section -->
        <div class="text-center my-4">
            <img src="<?php echo isset($_SESSION['profile_picture']) && !empty($_SESSION['profile_picture']) ? htmlspecialchars($_SESSION['profile_picture']) : '../image/default_profile.png'; ?>" class="rounded-circle mb-2" height="80" alt="User Profile" loading="lazy" />
            <div class="text-center">
                <span><?php echo $adminName; ?></span>
            </div>
        </div>
        <!-- Navigation Links -->
        <div class="list-group list-group-flush mx-2">
            <a href="Dashboard.php" class="list-group-item list-group-item-action py-2 ripple" aria-current="true">
                <i class="bi bi-house-door me-3"></i><span>Dashboard</span>
            </a>
            <a href="Grades.php" class="list-group-item list-group-item-action py-2 ripple">
                <i class="bi bi-book me-3"></i><span>Class Record</span>
            </a>
            <a href="Attendance.php" class="list-group-item list-group-item-action py-2 ripple">
                <i class="bi bi-calendar-check me-3"></i><span>Attendance</span>
            </a>
            <a href="Profile.php" class="list-group-item list-group-item-action py-2 ripple">
                <i class="bi bi-person-circle me-3"></i><span>Profile</span>
            </a>
        </div>
    </div>
</nav>

<!-- Main Navbar -->
<nav id="main-navbar" class="navbar navbar-expand-lg navbar-light bg-white fixed-top">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
            <a class="navbar-brand" href="#">
                <img src="../image/logo.png" height="40" alt="Caniogan High School" loading="lazy" />
            </a>
            <h2 class="mb-0" style="font-size: 1rem;">𝒞𝒶𝓃𝒾𝑜𝑔𝒶𝓃 𝐻𝐼𝑔𝒽 𝒮𝒸𝒽𝑜𝑜𝓁</h2>
        </div>
        <!-- Logout Button -->
        <ul class="navbar-nav ms-auto d-flex align-items-center">
            <li class="nav-item me-2">
                <a class="nav-link" href="../logoutPage.php">
                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                </a>
            </li>
        </ul>
    </div>
</nav>

<!-- Toggler Button -->
<button class="btn toggler-btn d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-expanded="false" aria-controls="sidebarMenu">
    <i class="bi bi-list"></i>
</button>

<!-- Optional JavaScript -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
</body>
</html>
