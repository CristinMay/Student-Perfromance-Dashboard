<?php
require 'config.php'; // Database connection

$school_year_id = $_POST['school_year_id'];
$grade_section_id = $_POST['grade_section_id'];
$teacher_id = $_POST['teacher_id'];

$query = "SELECT subject_id, subject_name FROM subjects WHERE school_year_id = ? AND class_id = ? AND teacher_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('iis', $school_year_id, $grade_section_id, $teacher_id);
$stmt->execute();
$result = $stmt->get_result();

$options = "";
while ($row = $result->fetch_assoc()) {
    $options .= "<option value='{$row['subject_id']}'>{$row['subject_name']}</option>";
}

echo $options;
?>
