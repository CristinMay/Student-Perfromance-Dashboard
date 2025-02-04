<?php 
session_start();

// Check if the user is logged in
if (!isset($_SESSION['student_id'])) {
    echo "You must be logged in to view this page.";
    exit;
}

// Include database connection
include 'config.php';

// Fetch student details along with class information
$student_id = $_SESSION['student_id'];
$query = "
    SELECT s.*, c.class_name 
    FROM students s 
    LEFT JOIN student_class_years scy ON s.student_id = scy.student_id
    LEFT JOIN classes c ON scy.class_id = c.class_id
    WHERE s.student_id = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

// Check if student record was found
if (!$student) {
    echo "No student found.";
    exit;
}

// Handle profile picture upload
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
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
                $updateQuery = "UPDATE students SET profile_picture = ? WHERE student_id = ?";
                $updateStmt = $conn->prepare($updateQuery);
                $updateStmt->bind_param("si", $target_file, $student_id);
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

    // Handle other student details update (e.g., name, email)
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];

    $updateDetailsQuery = "UPDATE students SET first_name = ?, last_name = ?, email = ? WHERE student_id = ?";
    $updateDetailsStmt = $conn->prepare($updateDetailsQuery);
    $updateDetailsStmt->bind_param("sssi", $first_name, $last_name, $email, $student_id);
    $updateDetailsStmt->execute();

    // Update session variables
    $_SESSION['first_name'] = $first_name;
    $_SESSION['last_name'] = $last_name;

    // Reload student details after update
    $stmt->execute();
    $result = $stmt->get_result();
    $student = $result->fetch_assoc();
}

// Display student profile
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Page</title>
    <link rel="icon" type="image/x-icon" href="image/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.5.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css"> 
    <link rel="stylesheet" href="css/student.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .profile-section img {
            border: 3px solid #007bff;
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
        }
    </style>
</head>
<body>
    <header class="fixed-top bg-light shadow-sm">
        <?php include "inc/Navbar.php"; ?>
    </header>

    <main class="container mt-8 pt-5 mb-3">
    <div class="row justify-content-end">
            <div class="col-md-10">
                <div class="card mt-4 mb-1.5">
                <div class="card-body text-center">
                        <div class="profile-section">
                             <img src="<?php echo isset($_SESSION['profile_picture']) ? $_SESSION['profile_picture'] : '../image/default_profile.png'; ?>" class="rounded-circle" height="150" alt="User Profile" loading="lazy" />
                            <h2 class="mt-2"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></h2>
                            <p><strong>Student ID:</strong> <?php echo htmlspecialchars($student['student_id']); ?></p>
                            <p><?php echo htmlspecialchars($student['class_name']); ?></p>
                        </div>
                            <form method="post" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="first_name" class="form-label">First Name</label>
                                    <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($student['first_name']); ?>" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="last_name" class="form-label">Last Name</label>
                                    <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($student['last_name']); ?>" readonly>
                                </div>
                            </div>
                            <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($student['email']); ?>" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="date_of_birth" class="form-label">Date of Birth</label>
                                <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" value="<?php echo htmlspecialchars($student['date_of_birth']); ?>" readonly>
                            </div>
                           </div>
                           
                            <div class="col-md-6 mb-3">
                                <label for="profile_picture" class="form-label">Profile Picture</label>
                                <input type="file" class="form-control" id="profile_picture" name="profile_picture" accept="image/*">
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

