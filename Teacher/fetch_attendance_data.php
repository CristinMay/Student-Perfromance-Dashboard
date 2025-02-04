<?php

session_start();
include 'config.php';

if (isset($_POST['student_id']) && isset($_POST['school_year_id'])) {
    $student_id = $_POST['student_id'];
    $school_year_id = $_POST['school_year_id'];

    // Fetch attendance records with specific dates
    $sql = "SELECT status, attendance_date
            FROM attendance
            WHERE student_id = ? AND school_year_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $student_id, $school_year_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $attendance = [
        'present' => [],
        'absent' => [],
        'late' => []
    ];

    while ($row = $result->fetch_assoc()) {
        $attendance[$row['status']][] = $row['attendance_date'];
    }

    // Return the attendance data as JSON
    echo json_encode($attendance);
} else {
    echo json_encode([]);
}
?>
