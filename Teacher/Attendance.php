<?php
session_start();
$teacher_id = $_SESSION['teacher_id'];

// Database connection
include 'config.php';
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch school years
$school_years = $conn->query("SELECT * FROM school_years");

// Fetch classes assigned to the teacher
$classes = $conn->query("SELECT DISTINCT c.class_id, c.class_name 
                          FROM class_per_subject cps
                          JOIN classes c ON cps.class_id = c.class_id
                          WHERE cps.teacher_id = $teacher_id");

// Fetch subjects assigned to the teacher
$subjects = $conn->query("SELECT DISTINCT s.subject_id, s.subject_name 
                          FROM class_per_subject cps
                          JOIN subjects s ON cps.subject_id = s.subject_id
                          WHERE cps.teacher_id = $teacher_id");

// Initialize variables for selected values
$selected_date = date('Y-m-d'); // default to today's date

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $selected_school_year = $_POST['school_year_id'];
    $selected_class = $_POST['class_id'];
    $selected_subject = $_POST['subject_id'];
    $selected_date = $_POST['attendance_date']; // get selected date

    // Fetch students based on selected class and school year
    $students = $conn->query("SELECT s.* 
                              FROM students s
                              JOIN student_class_years sy ON s.student_id = sy.student_id
                              WHERE sy.class_id = $selected_class 
                              AND sy.school_year_id = $selected_school_year
                              ORDER BY s.last_name, s.first_name"); // Order by last name, then first name

    if (!$students) {
        die("Error fetching students: " . $conn->error);
    }

    // Fetch attendance records for selected date
    $attendance_records = $conn->query("SELECT * FROM attendance 
                                         WHERE school_year_id = $selected_school_year 
                                         AND class_id = $selected_class 
                                         AND subject_id = $selected_subject 
                                         AND attendance_date = '$selected_date'");
                                         
    if (!$attendance_records) {
        die("Error fetching attendance records: " . $conn->error);
    }

    $attendance_data = [];
    while ($record = $attendance_records->fetch_assoc()) {
        $attendance_data[$record['student_id']] = $record['status'];
    }

    // Fetch the selected school year details for display
    $school_year_details = $conn->query("SELECT * FROM school_years WHERE school_year_id = $selected_school_year");
    if ($school_year_details) {
        $school_year_details = $school_year_details->fetch_assoc();
    }

    $class_details = $conn->query("SELECT * FROM classes WHERE class_id = $selected_class");
    if ($class_details) {
        $class_details = $class_details->fetch_assoc();
    }

    $subject_details = $conn->query("SELECT * FROM subjects WHERE subject_id = $selected_subject");
    if ($subject_details) {
        $subject_details = $subject_details->fetch_assoc();
    }
}

// HTML Form and other code to display the fetched data should go here

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Attendance Page</title>
    <link rel="icon" type="image/x-icon" href="image/favicon.ico">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.5.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">

    <script>
        function toggleDropdowns() {
            const schoolYearSelect = document.getElementById('school_year_id');
            const classSelect = document.getElementById('class_id');
            const subjectSelect = document.getElementById('subject_id');

            // Enable class dropdown if school year is selected
            classSelect.disabled = !schoolYearSelect.value;
            // Enable subject dropdown if class is selected
            subjectSelect.disabled = !classSelect.value;
        }
    </script>

    <style>
        .form-control {
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 8 8"><path d="M2 3l3 3 3-3H2z"/></svg>');
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 12px; /* Adjust the size of the icon */
            padding-right: 30px; /* Ensure space for the icon */
        }
            </style>
</head>
<body style="padding-top: 50px;">

<header class="fixed-top bg-light shadow-sm">
   <?php include "inc/Navbar.php"; ?>
</header>

<main >

<div class="container mt-5 pt-5">
    <h2>Teacher Attendance Page</h2>

    <form method="post" class="mb-4">
        <div class="p-4 border rounded bg-light shadow-sm">
        <div class="row">
            <div class="col-md-4">
                <label for="school_year_id" >School Year</label>
                <select name="school_year_id" id="school_year_id" class="form-control" required onchange="toggleDropdowns()">
                    <option value="">Select School Year</option>
                    <?php while ($row = $school_years->fetch_assoc()): ?>
                        <option value="<?= $row['school_year_id']; ?>"><?= $row['school_year'] . ' - ' . $row['quarter']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label for="class_id">Class</label>
                <select name="class_id" id="class_id" class="form-control" required disabled onchange="toggleDropdowns()">
                    <option value="">Select Class</option>
                    <?php while ($row = $classes->fetch_assoc()): ?>
                        <option value="<?= $row['class_id']; ?>"><?= $row['class_name']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label for="subject_id">Subject</label>
                <select name="subject_id" id="subject_id" class="form-control" required disabled>
                    <option value="">Select Subject</option>
                    <?php while ($row = $subjects->fetch_assoc()): ?>
                        <option value="<?= $row['subject_id']; ?>"><?= $row['subject_name']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>

        <div class="col-md-5 mt-3">

            <label for="attendance_date">Attendance Date</label>
            <input type="date" name="attendance_date" class= "custom-select" id="attendance_date"  value="<?= $selected_date; ?>" required>
        </div>

        <div class="col-md-5 mt-3">                     
        <button type="submit" class="btn btn-primary">View Students</button>
        <a href="attendance_history.php" class="btn btn-info">View Attendance History</a>
        </div>
    </div>

    </form>

    



<?php if (isset($students)): ?>
    <div class="row">
            <div class="col text-center mb-9">
                <table class="table table-bordered text-center">
                <thead>
                    <tr>
                        <th>School Year</th>
                        <th>Class</th>
                        <th>Subject</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <p><strong>School Year:</strong> <?= $school_year_details['school_year'] . ' - ' . $school_year_details['quarter']; ?>
                        </td>
                        <td>
                            <strong>Class:</strong> <?= $class_details['class_name']; ?>
                        </td>
                        <td>
                            <strong>Subject:</strong> <?= $subject_details['subject_name']; ?>
                        </td>
                        <td>
                             <strong>Date:</strong> <?= $selected_date; ?></p>
                        </td>
                    </tr>
                </tbody>
            </table>
            </div>
        </div>
        
        <h4>Students List</h4>

        <form action="save_attendance.php" method="post">
        <div class="p-4 border rounded bg-light shadow-sm">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Attendance</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $students->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['student_id']; ?></td>
                            <td><?= $row['first_name'] . ' ' . $row['last_name']; ?></td>
                            <td>
                                <select name="attendance[<?= $row['student_id']; ?>]" class="form-control">
                                    <option value="absent" <?= isset($attendance_data[$row['student_id']]) && $attendance_data[$row['student_id']] === 'absent' ? 'selected' : ''; ?>>Absent</option>
                                    <option value="present" <?= isset($attendance_data[$row['student_id']]) && $attendance_data[$row['student_id']] === 'present' ? 'selected' : ''; ?>>Present</option>
                                    <option value="late" <?= isset($attendance_data[$row['student_id']]) && $attendance_data[$row['student_id']] === 'late' ? 'selected' : ''; ?>>Late</option>
                                </select>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <input type="hidden" name="school_year_id" value="<?= $selected_school_year; ?>">
            <input type="hidden" name="class_id" value="<?= $selected_class; ?>">
            <input type="hidden" name="subject_id" value="<?= $selected_subject; ?>">
            <input type="hidden" name="attendance_date" value="<?= $selected_date; ?>">
            <button type="submit" class="btn btn-success">Save Attendance</button>
             </div>
        </form>
    <?php endif; ?>
</div>
</main>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>
