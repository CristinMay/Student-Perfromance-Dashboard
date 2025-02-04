<?php
include 'config.php'; 
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'create') {
        // Create student logic
        $student_id = $_POST['student_id'];
        $last_name = $_POST['last_name'];
        $first_name = $_POST['first_name'];
        $email = $_POST['email'];
        $middle_name = $_POST['middle_name'];
        $date_of_birth = $_POST['date_of_birth'];

        // Check if student already exists
       $sql = "INSERT INTO students (student_id, last_name, first_name, middle_name, email, date_of_birth) 
        VALUES ('$student_id', '$last_name', '$first_name', '$middle_name', '$email', '$date_of_birth')";
        if ($conn->query($sql) === TRUE) {
            $_SESSION['alert'] = ['type' => 'success', 'message' => 'Student created successfully.'];
        } else {
            $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Error creating student: ' . $conn->error];
        }

        // Assign student to selected class and school years
        $class_id = $_POST['class'];
        if (isset($_POST['school_years'])) {
            foreach ($_POST['school_years'] as $school_year_id) {
                $sql = "INSERT INTO student_class_years (student_id, class_id, school_year_id) 
                        VALUES ('$student_id', '$class_id', '$school_year_id')";
                $conn->query($sql);
            }
        }

    } elseif (isset($_POST['action']) && $_POST['action'] == 'update') {
        // Update student logic
        $id = $_POST['id']; // Original ID for reference
        $student_id = $_POST['student_id']; // New student ID
        $last_name = $_POST['last_name'];
        $first_name = $_POST['first_name'];
        $middle_name = $_POST['middle_name'];
        $email = $_POST['email'];
        $date_of_birth = $_POST['date_of_birth'];

      $sql = "UPDATE students SET student_id='$student_id', last_name='$last_name', first_name='$first_name', middle_name='$middle_name', email='$email', 
        date_of_birth='$date_of_birth' WHERE student_id='$id'";
        if ($conn->query($sql) === TRUE) {
            $conn->query("UPDATE student_class_years SET student_id='$student_id' WHERE student_id='$id'");
            $_SESSION['alert'] = ['type' => 'success', 'message' => 'Student updated successfully.'];
        } else {
            $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Error updating student: ' . $conn->error];
        }

        $conn->query("DELETE FROM student_class_years WHERE student_id='$id'");
        $class_id = $_POST['class'];
        if (isset($_POST['school_years'])) {
            foreach ($_POST['school_years'] as $school_year_id) {
                $sql = "INSERT INTO student_class_years (student_id, class_id, school_year_id) 
                        VALUES ('$student_id', '$class_id', '$school_year_id')";
                $conn->query($sql);
            }
        }
} elseif (isset($_POST['action']) && $_POST['action'] == 'promote') {
    // Promote student logic
    $student_id = $_POST['student_id'];
    $new_class_id = $_POST['new_class_id']; // New grade/class ID
    $school_years = $_POST['school_years']; // The selected school years

    // Fetch the student's current class
    $sql = "SELECT c.class_id 
            FROM student_class_years scy 
            JOIN classes c ON scy.class_id = c.class_id
            WHERE scy.student_id = '$student_id'
            ORDER BY scy.school_year_id DESC LIMIT 1"; // Get the most recent class
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $current_class_id = $result->fetch_assoc()['class_id'];

        // Compare current class with new class
        if ($current_class_id >= $new_class_id) {
            $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Student cannot be promoted to a lower class.'];
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }
    }

    // Proceed with the promotion if class levels are correct
    foreach ($school_years as $school_year_id) {
        $sql = "INSERT INTO student_class_years (student_id, class_id, school_year_id) 
                VALUES ('$student_id', '$new_class_id', '$school_year_id')";
        if ($conn->query($sql) === TRUE) {
            $_SESSION['alert'] = ['type' => 'success', 'message' => 'Student grade level has been updated successfully.'];
        } else {
            $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Error updating student grade level: ' . $conn->error];
            break;
        }
    }
}

if (isset($_POST['archive_student'])) {
    $student_id = $_POST['id']; // Get the student_id from the form
    $sql = "UPDATE students SET archived = 1 WHERE student_id = '$student_id'";
    if ($conn->query($sql) === TRUE) {
        $_SESSION['alert'] = ['type' => 'success', 'message' => 'Student archived successfully.'];
    } else {
        $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Error archiving student: ' . $conn->error];
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Unarchive student
if (isset($_POST['unarchive_student'])) {
    $student_id = $_POST['id'];
    $sql = "UPDATE students SET archived = 0 WHERE student_id = '$student_id'";
    if ($conn->query($sql) === TRUE) {
        $_SESSION['alert'] = ['type' => 'success', 'message' => 'Student unarchived successfully.'];
    } else {
        $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Error unarchiving student: ' . $conn->error];
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Fetch students, classes, and school years as before
} elseif (isset($_GET['action']) && $_GET['action'] == 'delete') {
    // Delete student logic
    $student_id = $_GET['student_id'];

    $conn->query("DELETE FROM student_class_years WHERE student_id='$student_id'");
    $sql = "DELETE FROM students WHERE student_id='$student_id'";
    if ($conn->query($sql) === TRUE) {
        $_SESSION['alert'] = ['type' => 'success', 'message' => 'Student deleted successfully.'];
    } else {
        $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Error deleting student: ' . $conn->error];
    }

    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}if (isset($_POST['action']) && $_POST['action'] == 'mass_promote') {
    // Mass promote students logic
    $student_ids = $_POST['student_ids']; // Array of student IDs
    $new_class_id = $_POST['new_class_id']; // New grade/class ID
    $school_years = $_POST['school_years']; // The selected school years

    // Loop through each student to validate and promote
    foreach ($student_ids as $student_id) {
        // Fetch the student's current class
        $sql = "SELECT c.class_id 
                FROM student_class_years scy 
                JOIN classes c ON scy.class_id = c.class_id
                WHERE scy.student_id = '$student_id'
                ORDER BY scy.school_year_id DESC LIMIT 1"; // Get the most recent class
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $current_class_id = $result->fetch_assoc()['class_id'];

            // Compare current class with new class
            if ($current_class_id >= $new_class_id) {
                // Skip this student if they cannot be promoted
                $_SESSION['alert'] = ['type' => 'danger', 'message' => "Student ID $student_id cannot be promoted to a lower class."];
                continue;
            }
        }

        // Proceed with the promotion if class levels are correct
        foreach ($school_years as $school_year_id) {
            $sql = "INSERT INTO student_class_years (student_id, class_id, school_year_id) 
                    VALUES ('$student_id', '$new_class_id', '$school_year_id')";
            if ($conn->query($sql) === TRUE) {
                $_SESSION['alert'] = ['type' => 'success', 'message' => 'Students have been promoted successfully.'];
            } else {
                $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Error promoting student: ' . $conn->error];
                break;
            }
        }
    }

    // Redirect back to the same page after processing
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}




// Fetch students with their school years and classes
$search = $_GET['search'] ?? '';
$classFilter = $_GET['class'] ?? '';
$schoolYearFilter = $_GET['school_year'] ?? '';

$filter_status = isset($_GET['filter_status']) ? intval($_GET['filter_status']) : null;

$sql = "SELECT s.student_id, s.last_name, s.first_name, s.middle_name, s.email, s.date_of_birth, 
               s.archived,
               GROUP_CONCAT(DISTINCT CONCAT(c.class_name, ' (', sy.school_year, ')') 
               ORDER BY sy.school_year) AS class_years
        FROM students s
        LEFT JOIN student_class_years scy ON s.student_id = scy.student_id
        LEFT JOIN classes c ON scy.class_id = c.class_id
        LEFT JOIN school_years sy ON scy.school_year_id = sy.school_year_id
        WHERE (s.last_name LIKE '%$search%' OR s.first_name LIKE '%$search%')
        AND (c.class_name LIKE '%$classFilter%' OR '$classFilter' = '')
        AND (sy.school_year LIKE '%$schoolYearFilter%' OR '$schoolYearFilter' = '')
        ";

// Add condition for filter status
if ($filter_status !== null) {
    $sql .= " AND s.archived = $filter_status";
}

$sql .= " GROUP BY s.student_id";



$students = $conn->query($sql);

// Fetch classes and school years
$classes = $conn->query("SELECT * FROM classes");
$school_years = $conn->query("SELECT school_year_id, school_year, quarter FROM school_years");


// Function to fetch class and school year assignments for a student
function getStudentClassYears($conn, $student_id) {
    $sql = "SELECT scy.class_id, scy.school_year_id 
            FROM student_class_years scy 
            WHERE scy.student_id = '$student_id'";
    return $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Student Management</title>
    <link rel="icon" type="image/x-icon" href="image/favicon.ico">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.5.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .search-bar {
            margin-bottom: 20px;
        }

.container {
    background-color: #f5f7fa; /* Light, cool gray for a neutral background */
    padding: 2rem; /* Add more padding */
    border-radius: 12px; /* Rounded corners */
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05); /* Soft shadow for subtle depth */
}

/* Card Modernization */
.card {
    border: none; /* Remove card border */
    border-radius: 12px; /* Rounded corners */
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05); /* Very subtle shadow */
}

.card-header {
    background-color: #81c784; /* Soft green for headers */
    color: #ffffff; /* White text for contrast */
    font-weight: bold;
    border-top-left-radius: 12px;
    border-top-right-radius: 12px;
}

/* Table Styling */
.table-striped {
    border-radius: 8px;
    overflow: hidden; /* For rounded borders */
    background-color: #ffffff; /* Soft white for table background */
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05); /* Subtle shadow */
}

.table-striped thead {
    background-color: #81c784; /* Soft green header */
    color: #ffffff; /* White text */
}

.table-striped tbody tr:hover {
    background-color: #edf5ee; /* Very light green for hover effect */
}

.table-striped th,
.table-striped td {
    padding: 4px;
    vertical-align: middle;
    border: none; /* Remove table borders */
}

/* Button Styling */
.btn-info, .btn-danger, .btn-primary, .btn-warning {
    border-radius: 6px; /* Rounded buttons */
    font-weight: 200;
    padding: 0.5rem 1rem;
}

.btn-primary {
    background-color: #81c784; /* Consistent soft green color */
    border: none;
}

.btn-info {
    background-color: #4fc3f7; /* Soft blue for info buttons */
}

.btn-danger {
    background-color: #e57373; /* Soft red for danger buttons */
}

/* Modal Styling */
.modal-content {
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); /* Subtle shadow for focus */
}

.modal-header {
    background-color: #81c784; /* Soft green header */
    color: #ffffff;
    border-top-left-radius: 12px;
    border-top-right-radius: 12px;
}

.btn-close {
    filter: invert(1); /* Make close button white */
}

    /* Ensure table text does not wrap */
    table th, table td {
        white-space: nowrap;
    }

    /* Optional: Add horizontal scrolling for smaller screens */
    .table-responsive {
        overflow-x: auto;
    }
</style>


</head>
<body style="padding-top: 80px;">

<header>
    <?php include "inc/Navbar.php"; ?>
</header>

<main class="pt-2" style="margin-top: -90px;"> 
    <div class="container pt-5" style="margin-top: 60px">
        <div class="card mb-4">
            <div class="card-header">
                Filters
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <!-- Search -->
                    <div class="col-md-4">
                        <div class="input-group">
                            <input type="text" id="searchInput" class="form-control" placeholder="Search by name">
                            <button class="btn btn-primary" id="searchButton">Search</button>
                        </div>
                    </div>

                    <!-- Class Filter -->
                    <div class="col-md-4">
                        <input type="text" id="classFilter" class="form-control" placeholder="Enter Class">
                    </div>

                    <!-- School Year Filter -->
                    <div class="col-md-4">
                        <input type="text" id="schoolYearFilter" class="form-control" placeholder="Enter School Year">
                    </div>

                    <!-- Status Filter -->
                    <div class="col-md-4">
                        <label for="filterStatus" class="form-label">Filter by Status:</label>
                        <select id="filterStatus" class="form-select">
                            <option value="">All</option>
                            <option value="0">Active</option>
                            <option value="1">Archived</option>
                        </select>
                    </div>

                    <!-- Apply Filters -->
                    <div class="col-md-4 d-flex align-items-end">
                        <button class="btn btn-primary w-100" id="filterButton">Apply Filters</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                Student List
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <!-- Create Student Button -->
                    <div class="col-md-12 text-end">
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createStudentModal">
                            Create Student
                        </button>
                    </div>
                </div>

                <!-- Alert -->
                <?php if (isset($_SESSION['alert'])): ?>
                <div class="alert alert-<?php echo $_SESSION['alert']['type']; ?> alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['alert']['message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['alert']); ?>
                <?php endif; ?>
<!-- Mass Promote Button -->
<div class="col-md-12 text-end">
    <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#massPromoteStudentModal">
        Mass Promote Students
    </button>
</div>

                <!-- Student Table -->
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th scope="col">Student ID</th>
                                <th scope="col">Last Name</th>
                                <th scope="col">First Name</th>
                                <th scope="col">Middle Name</th>
                                <th scope="col">Email</th>
                                <th scope="col">Date of Birth</th>
                                <th scope="col">Class and School Years</th>
                                <th scope="col">Status</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="studentTable">
                            <?php while ($student = $students->fetch_assoc()): ?>
                            <tr>
                                <td><?= $student['student_id'] ?></td>
                                <td><?= $student['last_name'] ?></td>
                                <td><?= $student['first_name'] ?></td>
                                <td><?= $student['middle_name'] ?></td>
                                <td><?= $student['email'] ?></td>
                                <td><?= $student['date_of_birth'] ?></td>
                                <td><?= $student['class_years'] ?? 'None'?></td>
                                <td><?php echo ($row['is_archived'] == 0) ? 'Active' : 'Archived'; ?></td>
                                <td>
                                    <button class="btn btn-info btn-sm editBtn" 
                                            data-id="<?= $student['student_id'] ?>"
                                            data-lastname="<?= $student['last_name'] ?>"
                                            data-firstname="<?= $student['first_name'] ?>"
                                            data-middle_name="<?= $student['middle_name'] ?>"
                                            data-email="<?= $student['email'] ?>"
                                            data-dob="<?= $student['date_of_birth'] ?>"
                                            
                                            data-classes="<?= htmlspecialchars(json_encode(getStudentClassYears($conn, $student['student_id'])), ENT_QUOTES) ?>">
                                        Edit
                                    </button>

                                    <button class="btn btn-warning btn-sm promoteBtn" data-id="<?= $student['student_id'] ?>">Promote</button>

                                    <?php if ($student['archived'] == 0) : ?>
                                    <button class="btn btn-danger btn-sm archive-btn" 
                                            data-bs-toggle="modal" data-bs-target="#archiveModal" 
                                            data-id="<?= $student['student_id']; ?>">Archive</button>
                                    <?php else : ?>
                                    <button class="btn btn-success btn-sm unarchive-btn" 
                                            data-bs-toggle="modal" data-bs-target="#unarchiveModal" 
                                            data-id="<?= $student['student_id']; ?>">Unarchive</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>



<!-- Archive Modal -->
<div class="modal fade" id="archiveModal" tabindex="-1" aria-labelledby="archiveModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title" id="archiveModalLabel">Archive Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="Student.php" method="POST">
                <div class="modal-body">
                    <p>Are you sure you want to archive this record?</p>
                    <input type="hidden" id="archive-id" name="id">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="archive_student" class="btn btn-warning">Archive</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Unarchive Modal -->
<div class="modal fade" id="unarchiveModal" tabindex="-1" aria-labelledby="unarchiveModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="unarchiveModalLabel">Unarchive Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="Student.php" method="POST">
                <div class="modal-body">
                    <p>Are you sure you want to unarchive this record?</p>
                    <input type="hidden" id="unarchive-id" name="id">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="unarchive_student" class="btn btn-success">Unarchive</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Mass Promote Student Modal -->
<div class="modal fade" id="massPromoteStudentModal" tabindex="-1" aria-labelledby="massPromoteStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form action="" method="POST">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="massPromoteStudentModalLabel">Mass Promote Students</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="mass_promote">
                    <div class="mb-3">
                        <label for="student_ids" class="form-label">Select Students</label>
                        <select multiple class="form-select" name="student_ids[]" id="student_ids" required>
                            <?php 
                            // Fetch all students
                            $students = $conn->query("SELECT student_id, CONCAT(last_name, ', ', first_name) AS name FROM students");
                            while ($student = $students->fetch_assoc()): ?>
                                <option value="<?= $student['student_id'] ?>"><?= $student['name'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="new_class_id" class="form-label">New Class</label>
                        <select class="form-select" name="new_class_id" id="new_class_id" required>
                            <option value="" disabled>Select Class</option>
                            <?php 
                            // Fetch classes
                            $classes = $conn->query("SELECT * FROM classes");
                            while ($class = $classes->fetch_assoc()): ?>
                                <option value="<?= $class['class_id'] ?>"><?= $class['class_name'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="school_years" class="form-label">School Years and Quarters</label>
                        <select multiple class="form-select" name="school_years[]" id="school_years" required>
                            <?php 
                            // Fetch school years
                            $school_years = $conn->query("SELECT school_year_id, school_year, quarter FROM school_years");
                            while ($school_year = $school_years->fetch_assoc()): ?>
                                <option value="<?= $school_year['school_year_id'] ?>">
                                    <?= $school_year['school_year'] ?> - <?= $school_year['quarter'] ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Promote Students</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Create Student Modal -->
<div class="modal fade" id="createStudentModal" tabindex="-1" aria-labelledby="createStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form action="" method="POST">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createStudentModalLabel">Create Student</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="student_id" class="form-label">Student ID</label>
                        <input type="text" class="form-control" name="student_id" required>
                    </div>
                    <div class="mb-3">
                        <label for="last_name" class="form-label">Last Name</label>
                        <input type="text" class="form-control" name="last_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="first_name" class="form-label">First Name</label>
                        <input type="text" class="form-control" name="first_name" required>
                    </div>
                    <div class="mb-3">
    <label for="middle_name" class="form-label">Middle Name</label>
    <input type="text" class="form-control" name="middle_name" required>
</div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="date_of_birth" class="form-label">Date of Birth</label>
                        <input type="date" class="form-control" name="date_of_birth">
                    </div>
                    <div class="mb-3">
                        <label for="class" class="form-label">Class</label>
                        <select class="form-select" name="class" required>
                            <option value="" disabled selected>Select Class</option>
                            <?php while ($class = $classes->fetch_assoc()): ?>
                                <option value="<?= $class['class_id'] ?>"><?= $class['class_name'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                        <div class="mb-3">
                            <label for="school_years" class="form-label">School Years and Quarters</label>
                            <select multiple class="form-select" name="school_years[]" required>
                                <?php while ($school_year = $school_years->fetch_assoc()): ?>
                                    <option value="<?= $school_year['school_year_id'] ?>">
                                        <?= $school_year['school_year'] ?> - <?= $school_year['quarter'] ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" name="action" value="create">Create Student</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Edit Student Modal -->
<div class="modal fade" id="editStudentModal" tabindex="-1" aria-labelledby="editStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form action="" method="POST">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editStudentModalLabel">Edit Student</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="editStudentId"> <!-- Keep this hidden for the original ID -->
                    <div class="mb-3">
                        <label for="edit_student_id" class="form-label">Student ID</label>
                        <input type="text" class="form-control" name="student_id" id="edit_student_id" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_last_name" class="form-label">Last Name</label>
                        <input type="text" class="form-control" name="last_name" id="edit_last_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_first_name" class="form-label">First Name</label>
                        <input type="text" class="form-control" name="first_name" id="edit_first_name" required>
                    </div>
                    <div class="mb-3">
    <label for="edit_middle_name" class="form-label">Middle Name</label>
    <input type="text" class="form-control" name="middle_name" id="edit_middle_name" required>
</div>

                    <div class="mb-3">
                        <label for="edit_email" class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" id="edit_email" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_date_of_birth" class="form-label">Date of Birth</label>
                        <input type="date" class="form-control" name="date_of_birth" id="edit_date_of_birth">
                    </div>
                    <div class="mb-3">
                        <label for="edit_class" class="form-label">Class</label>
                        <select class="form-select" name="class" id="edit_class" required>
                            <option value="" disabled>Select Class</option>
                            <?php 
                            // Reset classes fetch
                            $classes = $conn->query("SELECT * FROM classes");
                            while ($class = $classes->fetch_assoc()): ?>
                                <option value="<?= $class['class_id'] ?>"><?= $class['class_name'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                   <div class="mb-3">
                        <label for="edit_school_years" class="form-label">School Years and Quarters</label>
                        <select multiple class="form-select" name="school_years[]" id="edit_school_years" required>
                            <?php
                            $school_years = $conn->query("SELECT school_year_id, school_year, quarter FROM school_years");
                            while ($school_year = $school_years->fetch_assoc()): ?>
                                <option value="<?= $school_year['school_year_id'] ?>">
                                    <?= $school_year['school_year'] ?> - <?= $school_year['quarter'] ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" name="action" value="update">Update Student</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Promote Student Modal -->
<div class="modal fade" id="promoteStudentModal" tabindex="-1" aria-labelledby="promoteStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form action="" method="POST">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="promoteStudentModalLabel">Promote Student</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="student_id" id="promoteStudentId">
                    <div class="mb-3">
                        <label for="new_class_id" class="form-label">New Class</label>
                        <select class="form-select" name="new_class_id" id="new_class_id" required>
                            <option value="" disabled>Select Class</option>
                            <?php 
                            // Reset classes fetch
                            $classes = $conn->query("SELECT * FROM classes");
                            while ($class = $classes->fetch_assoc()): ?>
                                <option value="<?= $class['class_id'] ?>"><?= $class['class_name'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="promote_school_years" class="form-label">School Years and Quarters</label>
                        <select multiple class="form-select" name="school_years[]" id="promote_school_years" required>
                            <?php
                            $school_years = $conn->query("SELECT school_year_id, school_year, quarter FROM school_years");
                            while ($school_year = $school_years->fetch_assoc()): ?>
                                <option value="<?= $school_year['school_year_id'] ?>">
                                    <?= $school_year['school_year'] ?> - <?= $school_year['quarter'] ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" name="action" value="promote">Promote Student</button>
                    </div>
            </div>
        </form>
    </div>
</div>
<!-- Delete Modal -->
<div class="modal fade" id="deleteStudentModal" tabindex="-1" aria-labelledby="deleteStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteStudentModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this student?
                <p id="deleteStudentName" class="fw-bold"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteStudentForm" method="GET" action="">
                    <input type="hidden" name="student_id" id="deleteStudentId">
                    <input type="hidden" name="action" value="delete">
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.min.js"></script>
<script>
    document.getElementById('searchButton').addEventListener('click', function() {
    const searchInput = document.getElementById('searchInput').value.toLowerCase();
    const rows = document.querySelectorAll('#studentTable tr');

    rows.forEach(row => {
        const lastName = row.cells[1].textContent.toLowerCase();
        const firstName = row.cells[2].textContent.toLowerCase();
        if (lastName.includes(searchInput) || firstName.includes(searchInput)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

document.querySelectorAll('.editBtn').forEach(button => {
    button.addEventListener('click', function () {
        const row = button.closest('tr');
        document.getElementById('editStudentId').value = button.getAttribute('data-id'); // This will be the original ID for reference
        document.getElementById('edit_student_id').value = button.getAttribute('data-id'); // Populate the new ID field
        document.getElementById('edit_last_name').value = button.getAttribute('data-lastname');
        document.getElementById('edit_first_name').value = button.getAttribute('data-firstname');
         document.getElementById('edit_middle_name').value = this.getAttribute('data-middle_name');  // Set middle name
        document.getElementById('edit_email').value = button.getAttribute('data-email');
        document.getElementById('edit_date_of_birth').value = button.getAttribute('data-dob');
       
        
        // Handle class selection
        document.getElementById('edit_class').value = button.getAttribute('data-class-id');
        
        // Handle school years (JSON parsing as before)
        const classYears = JSON.parse(button.getAttribute('data-classes'));
        const editSchoolYears = document.getElementById('edit_school_years');
        for (let option of editSchoolYears.options) {
            option.selected = classYears.includes(Number(option.value));
        }

        // Show the modal
        const editModal = new bootstrap.Modal(document.getElementById('editStudentModal'));
        editModal.show();
    });
});

document.getElementById('filterButton').addEventListener('click', function() {
        const searchInput = document.getElementById('searchInput').value;
        const classFilter = document.getElementById('classFilter').value;
        const schoolYearFilter = document.getElementById('schoolYearFilter').value;

        // Redirect with the filters as query parameters
        const queryString = new URLSearchParams({
            search: searchInput,
            class: classFilter,
            school_year: schoolYearFilter
        }).toString();
        
        window.location.href = '<?= $_SERVER['PHP_SELF'] ?>?' + queryString;
    });
 
        document.querySelectorAll('.editBtn').forEach(button => {
            button.addEventListener('click', function () {
                const row = button.closest('tr');
                document.getElementById('editStudentId').value = button.getAttribute('data-id');
                document.getElementById('edit_last_name').value = button.getAttribute('data-lastname');
                document.getElementById('edit_first_name').value = button.getAttribute('data-firstname');
                   document.getElementById('edit_middle_name').value = this.getAttribute('data-middle_name');  // Set middle name
                document.getElementById('edit_email').value = button.getAttribute('data-email');
                document.getElementById('edit_date_of_birth').value = button.getAttribute('data-dob');
                
                // Handle class selection
                document.getElementById('edit_class').value = button.getAttribute('data-class-id');
                
                // Handle school years (JSON parsing as before)
                const classYears = JSON.parse(button.getAttribute('data-classes'));
                const editSchoolYears = document.getElementById('edit_school_years');
                for (let option of editSchoolYears.options) {
                    option.selected = classYears.includes(Number(option.value));
                }

                // Show the modal
                const editModal = new bootstrap.Modal(document.getElementById('editStudentModal'));
                editModal.show();
            });
        });


// Promote Student Modal - Show the modal when promote button is clicked
document.querySelectorAll('.promoteBtn').forEach(button => {
    button.addEventListener('click', function () {
        const row = button.closest('tr');
        document.getElementById('promoteStudentId').value = button.getAttribute('data-id');

        // Show the modal
        const promoteModal = new bootstrap.Modal(document.getElementById('promoteStudentModal'));
        promoteModal.show();
    });
});
    document.addEventListener("DOMContentLoaded", function () {
        // Handle delete button click
        document.querySelectorAll(".deleteBtn").forEach(function (btn) {
            btn.addEventListener("click", function () {
                const studentId = btn.getAttribute("data-id");
                const studentName = btn.closest("tr").querySelector("td:nth-child(2)").textContent + " " +
                    btn.closest("tr").querySelector("td:nth-child(3)").textContent;

                // Populate the modal fields
                document.getElementById("deleteStudentId").value = studentId;
                document.getElementById("deleteStudentName").textContent = studentName;

                // Show the modal
                new bootstrap.Modal(document.getElementById("deleteStudentModal")).show();
            });
        });
    });
document.querySelectorAll('.archive-btn').forEach(button => {
    button.addEventListener('click', function () {
        document.getElementById('archive-id').value = this.dataset.id;
    });
});

document.querySelectorAll('.unarchive-btn').forEach(button => {
    button.addEventListener('click', function () {
        document.getElementById('unarchive-id').value = this.dataset.id;
    });
});

document.getElementById('applyFilterButton').addEventListener('click', function () {
    const filterStatus = document.getElementById('filterStatus').value;
    const searchParams = new URLSearchParams(window.location.search);

    if (filterStatus) {
        searchParams.set('filter_status', filterStatus);
    } else {
        searchParams.delete('filter_status');
    }

    window.location.search = searchParams.toString();
});


</script>

</body>
</html>
