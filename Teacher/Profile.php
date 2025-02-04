<?php  
session_start();

// Check if the user is logged in
if (!isset($_SESSION['teacher_id'])) {
    echo "You must be logged in to view this page.";
    exit;
}

// Include database connection
include 'config.php';

// Fetch teacher details
$teacher_id = $_SESSION['teacher_id'];
$query = "
    SELECT * 
    FROM teachers 
    WHERE teacher_id = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$result = $stmt->get_result();
$teacher = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle profile picture upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["profile_picture"]["name"]);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if the file is an image
        $check = getimagesize($_FILES["profile_picture"]["tmp_name"]);
        if ($check !== false) {
            // Move the uploaded file to the uploads directory
            if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
                // Update profile picture path in the database
                $updateQuery = "UPDATE teachers SET profile_picture = ? WHERE teacher_id = ?";
                $updateStmt = $conn->prepare($updateQuery);
                $updateStmt->bind_param("si", $target_file, $teacher_id);
                $updateStmt->execute();

                // Update session variable
                $_SESSION['profile_picture'] = $target_file;

                echo "Profile picture updated successfully.";
            } else {
                echo "Sorry, there was an error uploading your file.";
            }
        } else {
            echo "File is not an image.";
        }
    } elseif (isset($_FILES['profile_picture'])) {
        // Handle the error case
        echo "Error uploading file: " . $_FILES['profile_picture']['error'];
    }

    // Handle other teacher details update (e.g., name, email)
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];

    $updateDetailsQuery = "UPDATE teachers SET first_name = ?, last_name = ?, email = ? WHERE teacher_id = ?";
    $updateDetailsStmt = $conn->prepare($updateDetailsQuery);
    $updateDetailsStmt->bind_param("sssi", $first_name, $last_name, $email, $teacher_id);
    $updateDetailsStmt->execute();

    // Update session variables
    $_SESSION['first_name'] = $first_name;
    $_SESSION['last_name'] = $last_name;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Page</title>
    <link rel="icon" type="image/x-icon" href="image/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
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
<body style="padding-top: 80px;">
    <header class="fixed-top bg-light shadow-sm">
        <?php include "inc/Navbar.php"; ?>
    </header>

    <main class="container mt-4 pt-5 mb-3">
        <div class="row justify-content-end">
            <div class="col-md-10">
                <div class="card mt-4 mb-1.5">
                    <div class="card-body text-center">
                        <div class="profile-section">
                            <img src="<?php echo isset($_SESSION['profile_picture']) ? $_SESSION['profile_picture'] : '../image/default_profile.png'; ?>" class="rounded-circle" height="150" alt="User Profile" loading="lazy" />
                            <h2 class="mt-2"><?php echo htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']); ?></h2>
                            <p><strong>Teacher ID:</strong> <?php echo htmlspecialchars($teacher['teacher_id']); ?></p>
                        </div>
                        <form method="post" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="first_name" class="form-label">First Name</label>
                                    <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($teacher['first_name']); ?>" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="last_name" class="form-label">Last Name</label>
                                    <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($teacher['last_name']); ?>" readonly>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="profile_picture" class="form-label">Profile Picture</label>
                                    <input type="file" class="form-control" id="profile_picture" name="profile_picture" accept="image/*">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($teacher['email']); ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <button type="submit" class="btn btn-primary ms-2">Update Profile</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
