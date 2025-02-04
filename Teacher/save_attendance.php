<?php
session_start();
$teacher_id = $_SESSION['teacher_id'];

// Database connection
include 'config.php';
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $school_year_id = $_POST['school_year_id'];
    $class_id = $_POST['class_id'];
    $subject_id = $_POST['subject_id'];
    $attendance_date = $_POST['attendance_date'];

    foreach ($_POST['attendance'] as $student_id => $status) {
        // Check if the attendance record already exists
        $result = $conn->query("SELECT * FROM attendance WHERE student_id = $student_id AND school_year_id = $school_year_id AND class_id = $class_id AND subject_id = $subject_id AND attendance_date = '$attendance_date'");
        
        if ($result->num_rows > 0) {
            // Update existing record
            $conn->query("UPDATE attendance SET status = '$status' WHERE student_id = $student_id AND school_year_id = $school_year_id AND class_id = $class_id AND subject_id = $subject_id AND attendance_date = '$attendance_date'");
        } else {
            // Insert new record
            $conn->query("INSERT INTO attendance (student_id, school_year_id, class_id, subject_id, attendance_date, status) VALUES ($student_id, $school_year_id, $class_id, $subject_id, '$attendance_date', '$status')");
        }
    }

    $_SESSION['message'] = "Attendance saved successfully!";
    header('Location: Attendance.php');
    exit;
}
?>
