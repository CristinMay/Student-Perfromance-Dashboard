<?php
session_start();
if (!isset($_SESSION['teacher_id'])) {
    die('Teacher ID not set in session');
}


// Include database connection
include 'config.php';

// Check if POST data is set
if (isset($_POST['school_year_id']) && isset($_POST['class_id']) && isset($_POST['subject_id'])) {
    $school_year_id = $_POST['school_year_id'];
    $class_id = $_POST['class_id'];
    $subject_id = $_POST['subject_id'];

    // Fetch student rankings
    $sql = "SELECT s.student_id, CONCAT(s.first_name, ' ', s.last_name) AS name, fg.final_grade
            FROM final_grades fg
            JOIN students s ON fg.student_id = s.student_id
            WHERE fg.school_year_id = ? AND fg.class_id = ? AND fg.subject_id = ?
            ORDER BY fg.final_grade DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $school_year_id, $class_id, $subject_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $students = [];
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }

    // Return the result as JSON
    echo json_encode($students);
} else {
    echo json_encode([]);
}
?>
