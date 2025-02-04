<?php  
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    echo "You must be logged in to view this page.";
    exit;
}

// Include database connection
require 'config.php';

// Fetch admin details
$admin_id = $_SESSION['admin_id'];
$query = "
    SELECT * 
    FROM admins 
    WHERE id = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();

if (!$admin) {
    echo "Admin not found.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle profile picture upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "uploads/"; // Ensure this directory exists and is writable
        $file_extension = strtolower(pathinfo($_FILES["profile_picture"]["name"], PATHINFO_EXTENSION));
        $allowed_extensions = array("jpg", "jpeg", "png", "gif");

        // Validate file type
        if (in_array($file_extension, $allowed_extensions)) {

            // Generate a unique file name to prevent overwriting
            $new_filename = $target_dir . uniqid("profile_", true) . "." . $file_extension;

            // Move the uploaded file to the uploads directory
            if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $new_filename)) {
                // Optionally, delete the old profile picture if it exists and is not the default
                if ($admin['profile_picture'] && file_exists($admin['profile_picture']) && $admin['profile_picture'] != '../image/profile.png') {
                    unlink($admin['profile_picture']);
                }

                // Update profile picture path in the database
                $updateQuery = "UPDATE admins SET profile_picture = ? WHERE id = ?";
                $updateStmt = $conn->prepare($updateQuery);
                $updateStmt->bind_param("si", $new_filename, $admin_id);
                $updateStmt->execute();

                // Update session variable
                $_SESSION['profile_picture'] = $new_filename;

                // Update the $admin array to reflect the new profile picture
                $admin['profile_picture'] = $new_filename;
            }
        }
    }

    // Handle other upload errors
    elseif (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] != UPLOAD_ERR_NO_FILE) {
        // You can handle other upload errors here if needed
    }

    // Handle other admin details update (e.g., first name, last name, email)
    // Note: Password changes are not handled here for security reasons
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);

    // Basic validation
    if (!empty($first_name) && !empty($last_name) && !empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Update admin details in the database
        $updateDetailsQuery = "UPDATE admins SET firstName = ?, lastName = ?, email = ? WHERE id = ?";
        $updateDetailsStmt = $conn->prepare($updateDetailsQuery);
        $updateDetailsStmt->bind_param("sssi", $first_name, $last_name, $email, $admin_id);
        if ($updateDetailsStmt->execute()) {
            // Update session variables
            $_SESSION['admin_firstName'] = $first_name;
            $_SESSION['admin_lastName'] = $last_name;

            // Update the $admin array to reflect the changes
            $admin['firstName'] = $first_name;
            $admin['lastName'] = $last_name;
            $admin['email'] = $email;
        }
    }

    // Close statements
    if (isset($updateStmt)) $updateStmt->close();
    if (isset($updateDetailsStmt)) $updateDetailsStmt->close();
}

// Close the main statement
$stmt->close();

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile Page</title>
    <link rel="icon" type="image/x-icon" href="image/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.5.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css"> 
    <style>
        body {
            background-color: #f8f9fa;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 2rem;
        }
        .form-control {
            border-radius: 10px;
            transition: border-color 0.3s;
        }
        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        .btn-primary {
            border-radius: 10px;
            width: 100%; /* Make button full width */
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .profile-section {
            margin-bottom: 1.5rem;
            border: none;
        }
    </style>
</head>
<body  style="padding-top: 80px;">
    <header>
        <?php include "inc/Navbar.php"; ?>
    </header>

    <main>
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card mt-4">
                    <div class="card-body text-center">
                        <div class="profile-section">
                            <img src="<?php echo isset($admin['profile_picture']) && !empty($admin['profile_picture']) ? htmlspecialchars($admin['profile_picture']) : '../image/profile.png'; ?>" class="rounded-circle mb-3" height="150" alt="Admin Profile" loading="lazy" />
                            <h2 class="mt-2"><?php echo htmlspecialchars($admin['firstName'] . ' ' . $admin['lastName']); ?></h2>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($admin['email']); ?></p>
                            <p><strong>Username:</strong> <?php echo htmlspecialchars($admin['username']); ?></p>
                        </div>
                        <form method="post" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="first_name" class="form-label">First Name</label>
                                    <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($admin['firstName']); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="last_name" class="form-label">Last Name</label>
                                    <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($admin['lastName']); ?>" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="profile_picture" class="form-label">Profile Picture</label>
                                    <input type="file" class="form-control" id="profile_picture" name="profile_picture" accept="image/*">
                                    <small class="form-text text-muted">Accepted formats: JPG, JPEG, PNG, GIF.</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($admin['email']); ?>" required>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Update Profile</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
