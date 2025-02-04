<?php
session_start();
$teacher_id = $_SESSION['teacher_id'];

// Database connection
include 'config.php';
if ($conn->connect_error) {
    die("Connection failed: " . htmlspecialchars($conn->connect_error));
}

// Fetch school years
$school_years = $conn->query("SELECT * FROM school_years");

// Fetch classes assigned to the teacher
$classes = $conn->query("SELECT DISTINCT c.class_id, c.class_name FROM classes c
                          JOIN class_per_subject cps ON c.class_id = cps.class_id
                          WHERE cps.teacher_id = '" . $conn->real_escape_string($teacher_id) . "'");

// Initialize variables for selected values
$selected_date = date('Y-m-d');
$selected_school_year = null;
$selected_class = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $selected_date = $conn->real_escape_string($_POST['attendance_date']);
    $selected_school_year = $conn->real_escape_string($_POST['school_year_id']);
    $selected_class = $conn->real_escape_string($_POST['class_id']);

    // Fetch attendance history based on selected school year, class, and date
    $attendance_history = $conn->query("SELECT a.*, s.first_name, s.last_name FROM attendance a
                                         JOIN students s ON a.student_id = s.student_id
                                         WHERE a.attendance_date = '$selected_date'
                                         AND a.school_year_id = '$selected_school_year'
                                         AND a.class_id = '$selected_class'");

    // Check if the attendance history query was successful
    if (!$attendance_history) {
        die("Error fetching attendance history: " . $conn->error);
    }

    // Fetch attendance status counts
    $status_count_query = $conn->query("SELECT status, COUNT(*) as count FROM attendance 
                                         WHERE attendance_date = '$selected_date'
                                         AND school_year_id = '$selected_school_year'
                                         AND class_id = '$selected_class'
                                         GROUP BY status");

    // Fetch school year details
    $school_year_query = $conn->query("SELECT * FROM school_years WHERE school_year_id = '$selected_school_year'");
    $school_year_details = $school_year_query->fetch_assoc();

    // Check if the school year query was successful
    if (!$school_year_details) {
        die("Error fetching school year details: " . $conn->error);
    }

    // Fetch class details
    $class_query = $conn->query("SELECT * FROM classes WHERE class_id = '$selected_class'");
    $class_details = $class_query->fetch_assoc();

    // Check if the class query was successful
    if (!$class_details) {
        die("Error fetching class details: " . $conn->error);
    }

    // Fetch subject details if you have a mapping of classes to subjects
    $subject_query = $conn->query("SELECT s.subject_name FROM subjects s
                                     JOIN class_per_subject cps ON s.subject_id = cps.subject_id
                                     WHERE cps.class_id = '$selected_class'");
    $subject_details = $subject_query->fetch_assoc();

    // Check if the subject query was successful
    if (!$subject_details) {
        die("Error fetching subject details: " . $conn->error);
    }

    // Fetch teacher details
    $teacher_query = $conn->query("SELECT first_name, last_name FROM teachers WHERE teacher_id = '" . $conn->real_escape_string($teacher_id) . "'");
    $teacher_details = $teacher_query->fetch_assoc();

    // Check if the teacher query was successful
    if (!$teacher_details) {
        die("Error fetching teacher details: " . $conn->error);
    }
}

// HTML form to display school years and classes
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance History</title>
    <link rel="icon" type="image/x-icon" href="image/favicon.ico">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.5.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="../css/style.css">
    <!-- Print Styles -->
   <!-- Print Styles -->
<style>
    /* Print styles */
    @media print {
        body * {
            visibility: hidden;
        }
        .print-area, .print-area * {
            visibility: visible;
        }
        .print-area {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
        header, footer, .btn, .navbar, .fixed-top, .shadow-sm {
            display: none !important;
        }
        .print-header {
            display: block;
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 8px 12px;
            border: 1px solid #000;
        }
        
        /* Page break for attendance summary */
        .attendance-summary {
            page-break-before: always; /* Forces a page break before this element */
        }
    }
    @media screen {
        .print-header {
            display: none;
        }
    }


.form-control {
    background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 8 8"><path d="M2 3l3 3 3-3H2z"/></svg>');
    background-repeat: no-repeat;
    background-position: right 10px center;
    background-size: 12px; /* Adjust the size of the icon */
    padding-right: 30px; /* Ensure space for the icon */
}

</style>

</head>

<body style="padding-top: 80px;">
<header class="fixed-top bg-light shadow-sm">
   <?php include "inc/Navbar.php"; ?>
</header>

<main style="margin-top: 50px;">    
    <div class="container mt-3">
        <h2>Attendance History</h2>

        <form method="post" class="mb-4">
            <div class="p-4 border rounded bg-light shadow-sm">
                <div class="form-group mb-3">
                    <label for="school_year_id">Select School Year</label>
                    <select name="school_year_id" id="school_year_id" class="form-control" required>
                        <option value="">Select School Year</option>
                        <?php while ($row = $school_years->fetch_assoc()): ?>
                            <option value="<?= htmlspecialchars($row['school_year_id']); ?>" <?= ($selected_school_year == $row['school_year_id']) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($row['school_year'] . ' - ' . $row['quarter']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group mb-3">
                    <label for="class_id">Select Class</label>
                    <select name="class_id" id="class_id" class="form-control" required>
                        <option value="">Select Class</option>
                        <?php while ($row = $classes->fetch_assoc()): ?>
                            <option value="<?= htmlspecialchars($row['class_id']); ?>" <?= ($selected_class == $row['class_id']) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($row['class_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group mb-3">
                    <label for="attendance_date">Select Date</label>
                    <input type="date" name="attendance_date" id="attendance_date" class="custom-select" value="<?= htmlspecialchars($selected_date); ?>" required>
                </div>

                <div class="d-flex justify-content-start">
                    <button type="submit" class="btn btn-primary me-2">View Attendance</button>
                    <?php if (isset($attendance_history) && $attendance_history->num_rows > 0): ?>
                        <button type="button" onclick="printAttendance()" class="btn btn-secondary">
                            <i class="bi bi-printer"></i> Print Attendance
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </form>

        <div class="print-area">
            <?php if (isset($attendance_history) && $attendance_history->num_rows > 0): ?>
                <div class="print-header">
                    <img src="image/logo.png" alt="School Logo" style="width: 100px; height: 100px;">
                    <h1>Caniogan High School</h1>
                </div>
                <div class="row">
                    <div class="col text-center mb-4">
                        <table class="table table-bordered text-center">
                            <thead>
                                <tr>
                                    <th>School Year</th>
                                    <th>Class</th>
                                    <th>Subject</th>
                                    <th>Teacher</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><?= htmlspecialchars($school_year_details['school_year'] . ' - ' . $school_year_details['quarter']); ?></td>
                                    <td><?= htmlspecialchars($class_details['class_name']); ?></td>
                                    <td><?= htmlspecialchars($subject_details['subject_name']); ?></td>
                                    <td><?php echo htmlspecialchars($teacher_details['first_name']) . ' ' . htmlspecialchars($teacher_details['last_name']); ?></td>
                                    <td><?= htmlspecialchars($selected_date); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <h4>Attendance Records for <?= htmlspecialchars($selected_date); ?></h4>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $attendance_history->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['student_id']); ?></td>
                                <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                <td><?= htmlspecialchars($row['status']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

                <h4>Attendance Summary</h4>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($status_row = $status_count_query->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($status_row['status']); ?></td>
                                <td><?= htmlspecialchars($status_row['count']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No attendance records found for the selected date.</p>
            <?php endif; ?>
        </div>
    </div>
</main>

<!-- JavaScript for Print Function -->
<script>
    function printAttendance() {
        window.print();
    }
</script>

<!-- Bootstrap JS and Dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>
