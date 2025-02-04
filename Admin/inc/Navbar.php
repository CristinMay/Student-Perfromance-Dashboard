<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

if (isset($_SESSION['admin_firstName']) && isset($_SESSION['admin_lastName'])) {
    $adminName = htmlspecialchars($_SESSION['admin_firstName'] . ' ' . $_SESSION['admin_lastName']);
} else {
    $adminName = 'Guest'; // Default name if the user is not logged in
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
        /* Make profile picture circular */
        #sidebarMenu .text-center img {
            width: 80px;
            height: 80px;
            border-radius: 50%; /* Ensure the picture is circular */
            margin-bottom: 10px;
        }

        #sidebarMenu .text-center span {
            font-size: 1rem;
            font-weight: bold;
        }

        #main-navbar .btn-outline-danger {
            font-size: 0.875rem;
        }

        /* Ensure the sidebar is collapsible */
        #sidebarMenu {
            z-index: 1000; /* Adjust z-index if necessary */
        }
    </style>
</head>
<body>
<!-- Sidebar -->
<nav id="sidebarMenu" class="collapse d-lg-block sidebar bg-white">
    <div class="position-sticky">
      
        <!-- Profile Section -->
        <div class="text-center my-4">
            <img src="<?php echo isset($_SESSION['profile_picture']) && !empty($_SESSION['profile_picture']) ? htmlspecialchars($_SESSION['profile_picture']) : '../image/profile.png'; ?>" class="rounded-circle mb-2" height="80" alt="User Profile" loading="lazy" />
            <div class="text-center">
                <span><?php echo $adminName; ?></span>
            </div>
        </div>
        <!-- Navigation Links -->
        <div class="list-group list-group-flush mx-2">
            <a href="Dashboard.php" class="list-group-item list-group-item-action py-1 ripple" aria-current="true">
                <i class="bi bi-house-door me-3"></i><span>Dashboard</span>
            </a>
            <a href="Profile.php" class="list-group-item list-group-item-action py-1 ripple">
                <i class="bi bi-person-circle me-3"></i><span>Profile</span>
            </a>
            <a href="class.php" class="list-group-item list-group-item-action py-1 ripple">
                <i class="bi bi-grid me-3"></i><span>Class</span>
            </a>
            <a href="Subject.php" class="list-group-item list-group-item-action py-1 ripple">
                <i class="bi bi-book me-3"></i><span>Subject</span>
            </a>
            <a href="School_year.php" class="list-group-item list-group-item-action py-1 ripple">
                <i class="bi bi-calendar me-3"></i><span>School Year</span>
            </a>
            <a href="class_per_subject.php" class="list-group-item list-group-item-action py-1 ripple">
                <i class="bi bi-file-earmark-text me-3"></i><span>Class per Subject</span>
            </a>
            <a href="Student.php" class="list-group-item list-group-item-action py-1 ripple">
                <i class="bi bi-person-badge me-3"></i><span>Students</span>
            </a>
            <a href="Teacher.php" class="list-group-item list-group-item-action py-1 ripple">
                <i class="bi bi-person-badge me-3"></i><span>Teachers</span>
            </a>
            <a href="user_creation.php" class="list-group-item list-group-item-action py-1 ripple">
                <i class="bi bi-people me-3"></i><span>Accounts</span>
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
        <div>
            <a href="../logoutAdmin.php" class="btn btn-outline-danger">Logout</a>
        </div>
    </div>
</nav>
 <!-- Toggler Button -->
 <button class="btn toggler-btn" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-expanded="false" aria-controls="sidebarMenu">
            <i class="bi bi-list"></i>
        </button>
        

    <!-- Optional JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
</body>
</html>
