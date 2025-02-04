<?php
require 'config.php'; // Database connection
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php'); // Redirect to login if not logged in
    exit();
}

$adminId = $_SESSION['admin_id'];

// Fetch existing admin data
$stmt = $conn->prepare("SELECT firstName, lastName, email, username FROM admins WHERE id = ?");
$stmt->bind_param('i', $adminId);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($firstName, $lastName, $email, $username);
$stmt->fetch();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newFirstName = $_POST['firstName'];
    $newLastName = $_POST['lastName'];
    $newEmail = $_POST['email'];
    $newUsername = $_POST['username'];

    // Update profile in the database
    $stmt = $conn->prepare("UPDATE admins SET firstName = ?, lastName = ?, email = ?, username = ? WHERE id = ?");
    $stmt->bind_param('ssssi', $newFirstName, $newLastName, $newEmail, $newUsername, $adminId);
    if ($stmt->execute()) {
        $_SESSION['admin_firstName'] = $newFirstName; // Update session variable
        $_SESSION['admin_lastName'] = $newLastName; // Update session variable
        header('Location: profile.php'); // Redirect to profile
        exit();
    } else {
        echo 'Error updating profile: ' . $stmt->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - Admin</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css"> <!-- Adjust CSS path as needed -->
</head>
<body  style="padding-top: 80px;">

<!-- Sidebar and Navbar -->
<?php include 'sidebar.php'; ?> <!-- Include your sidebar from earlier code -->
<!-- Main Content -->
<div class="container mt-5">
    <h1 class="text-center">Edit Profile</h1>
    <div class="row justify-content-center">
        <div class="col-md-6">
            <form method="POST">
                <div class="form-group">
                    <label for="firstName">First Name</label>
                    <input type="text" name="firstName" id="firstName" class="form-control" value="<?php echo htmlspecialchars($firstName); ?>" required>
                </div>
                <div class="form-group">
                    <label for="lastName">Last Name</label>
                    <input type="text" name="lastName" id="lastName" class="form-control" value="<?php echo htmlspecialchars($lastName); ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" required>
                </div>
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" class="form-control" value="<?php echo htmlspecialchars($username); ?>" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Update Profile</button>
            </form>
        </div>
    </div>
</div>

<!-- Optional JavaScript -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
