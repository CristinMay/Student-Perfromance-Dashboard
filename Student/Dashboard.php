<?php
// dashboard.php

session_start();

// Check if the student is logged in
if (!isset($_SESSION['student_id'])) {
    // Redirect to login page or show an error
    echo "You must be logged in to view this page.";
    exit;
}

$student_id = $_SESSION['student_id'];

// Include database connection
include 'config.php';

// Fetch student's class assignments
// Assuming that a student can have multiple class assignments per school year
// For simplicity, let's assume one class per student per school year

// Fetch all school years
$school_years = [];
$school_year_query = "SELECT * FROM school_years ORDER BY school_year_id DESC";
$result = $conn->query($school_year_query);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $school_years[] = $row;
    }
}

// Handle form submission
$selected_year_id = null;
$selected_subject_id = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['selected_year_id'])) {
        $selected_year_id = intval($_POST['selected_year_id']); // Sanitize input
    }
    if (isset($_POST['selected_subject_id'])) {
        $selected_subject_id = intval($_POST['selected_subject_id']); // Sanitize input
    }
} else {
    // If not a POST request, set the latest school year as default
    if (!empty($school_years)) {
        $selected_year_id = $school_years[0]['school_year_id'];
    }
}

// Fetch the student's class for the selected school year
$class_id = null;
$class_name = '';
if ($selected_year_id) {
    $class_query = "SELECT c.class_id, c.class_name 
                   FROM student_class_years scy
                   JOIN classes c ON scy.class_id = c.class_id
                   WHERE scy.student_id = ? AND scy.school_year_id = ?";
    $stmt = $conn->prepare($class_query);
    $stmt->bind_param("ii", $student_id, $selected_year_id);
    $stmt->execute();
    $class_result = $stmt->get_result();
    if ($class_result && $class_result->num_rows > 0) {
        $class = $class_result->fetch_assoc();
        $class_id = $class['class_id'];
        $class_name = $class['class_name'];
    }
    $stmt->close();
}

// Fetch subjects based on selected year and class
$subjects = [];
if ($class_id && $selected_year_id) {
    $subject_query = "SELECT s.subject_id, s.subject_name 
                      FROM class_per_subject cps
                      JOIN subjects s ON cps.subject_id = s.subject_id
                      WHERE cps.class_id = ? AND cps.school_year_id = ?";
    $stmt = $conn->prepare($subject_query);
    $stmt->bind_param("ii", $class_id, $selected_year_id);
    $stmt->execute();
    $subject_result = $stmt->get_result();
    if ($subject_result) {
        while ($subject = $subject_result->fetch_assoc()) {
            $subjects[] = $subject;
        }
    }
    $stmt->close();
}

// If no subject is selected yet, set the first available subject as default
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !isset($_POST['selected_subject_id'])) {
    if (!empty($subjects)) {
        $selected_subject_id = $subjects[0]['subject_id'];
    }
}

// If a subject is selected, fetch related data
$written_scores = [];
$performance_scores = [];
$assessment_scores = [];
$final_grade = [];
$attendance_summary = [];
$progress_percentage = 0;
$excessive_absences = false;

if ($selected_subject_id) {
    // Fetch latest written scores
    $written_query = "SELECT w1, w2, w3, w4, w5, w6, w7, w8, w9, w10, total_score, percentage_score, weighted_score
                     FROM written_scores
                     WHERE student_id = ? AND class_id = ? AND subject_id = ? AND school_year_id = ?
                     ORDER BY id DESC LIMIT 1";
    $stmt = $conn->prepare($written_query);
    $stmt->bind_param("iiii", $student_id, $class_id, $selected_subject_id, $selected_year_id);
    $stmt->execute();
    $written_result = $stmt->get_result();
    if ($written_result && $written_result->num_rows > 0) {
        $written_scores = $written_result->fetch_assoc();
    }
    $stmt->close();

    // Fetch latest performance scores
    $performance_query = "SELECT p1, p2, p3, p4, p5, p6, p7, p8, p9, p10, total_score, percentage_score, weighted_score
                         FROM performance_scores
                         WHERE student_id = ? AND class_id = ? AND subject_id = ? AND school_year_id = ?
                         ORDER BY id DESC LIMIT 1";
    $stmt = $conn->prepare($performance_query);
    $stmt->bind_param("iiii", $student_id, $class_id, $selected_subject_id, $selected_year_id);
    $stmt->execute();
    $performance_result = $stmt->get_result();
    if ($performance_result && $performance_result->num_rows > 0) {
        $performance_scores = $performance_result->fetch_assoc();
    }
    $stmt->close();

    // Fetch latest assessment scores
    $assessment_query = "SELECT assessment_score, total_score, percentage_score, weighted_score
                         FROM assessment_scores
                         WHERE student_id = ? AND class_id = ? AND subject_id = ? AND school_year_id = ?
                         ORDER BY id DESC LIMIT 1";
    $stmt = $conn->prepare($assessment_query);
    $stmt->bind_param("iiii", $student_id, $class_id, $selected_subject_id, $selected_year_id);
    $stmt->execute();
    $assessment_result = $stmt->get_result();
    if ($assessment_result && $assessment_result->num_rows > 0) {
        $assessment_scores = $assessment_result->fetch_assoc();
    }
    $stmt->close();

    // Fetch latest final grade
    $final_grade_query = "SELECT final_grade
                          FROM final_grades
                          WHERE student_id = ? AND class_id = ? AND subject_id = ? AND school_year_id = ?
                          ORDER BY id DESC LIMIT 1";
    $stmt = $conn->prepare($final_grade_query);
    $stmt->bind_param("iiii", $student_id, $class_id, $selected_subject_id, $selected_year_id);
    $stmt->execute();
    $final_grade_result = $stmt->get_result();
    if ($final_grade_result && $final_grade_result->num_rows > 0) {
        $final_grade = $final_grade_result->fetch_assoc();
    }
    $stmt->close();

    // Fetch progress data (e.g., number of assessments completed)
    $progress_query = "SELECT COUNT(*) as completed_assessments 
                       FROM assessment_scores
                       WHERE student_id = ? AND class_id = ? AND subject_id = ? AND school_year_id = ?";
    $stmt = $conn->prepare($progress_query);
    $stmt->bind_param("iiii", $student_id, $class_id, $selected_subject_id, $selected_year_id);
    $stmt->execute();
    $progress_result = $stmt->get_result();
    if ($progress_result && $progress_result->num_rows > 0) {
        $progress = $progress_result->fetch_assoc();
        $completed_assessments = $progress['completed_assessments'];
        $total_assessments = 10; // Adjust as needed
        $progress_percentage = ($completed_assessments / $total_assessments) * 100;
    }
    $stmt->close();

    // Fetch attendance summary
    $attendance_summary_query = "SELECT 
                                    SUM(status = 'present') AS present,
                                    SUM(status = 'absent') AS absent,
                                    SUM(status = 'late') AS late
                                 FROM attendance
                                 WHERE student_id = ? AND class_id = ? AND subject_id = ? AND school_year_id = ?";
    $stmt = $conn->prepare($attendance_summary_query);
    $stmt->bind_param("iiii", $student_id, $class_id, $selected_subject_id, $selected_year_id);
    $stmt->execute();
    $attendance_summary_result = $stmt->get_result();
    if ($attendance_summary_result && $attendance_summary_result->num_rows > 0) {
        $attendance_summary = $attendance_summary_result->fetch_assoc();
        $excessive_absences = ($attendance_summary['absent'] > 3);
    }
    $stmt->close();
}

// Close database connection
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="icon" type="image/x-icon" href="image/favicon.ico">
    <!-- Highcharts -->
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <!-- FullCalendar CSS and JS -->
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.5.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css"> 
    <link rel="stylesheet" href="css/student.css">
</head>
<body>
<header class="fixed-top bg-light shadow-sm">
    <?php include "inc/Navbar.php"; ?> <!-- Adjust the path as needed -->
</header>
<main class="container mt-5 pt-5">
    <div class="row justify-content-start">
        <div class="col-md-10 offset-md-2"> <!-- Adjust the offset as needed -->
            <!-- Container for dropdowns -->
            <h1>Dashboard</h1>
            <div class="p-4 border rounded bg-light shadow-sm">
                <form method="POST" id="dashboardForm" class="mb-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="schoolYear" class="form-label">Select School Year/Quarter:</label>
                            <select id="schoolYear" name="selected_year_id" class="form-select" onchange="document.getElementById('dashboardForm').submit();">
                                <?php foreach ($school_years as $year): ?>
                                    <option value="<?php echo htmlspecialchars($year['school_year_id']); ?>" <?php if ($year['school_year_id'] == $selected_year_id) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($year['school_year'] . ' - ' . $year['quarter']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="subject" class="form-label">Select Subject:</label>
                            <select id="subject" name="selected_subject_id" class="form-select" onchange="document.getElementById('dashboardForm').submit();" <?php if (empty($subjects)) echo 'disabled'; ?>>
                                <option value="">Select a subject</option>
                                <?php if (!empty($subjects)): ?>
                                    <?php foreach ($subjects as $subject): ?>
                                        <option value="<?php echo htmlspecialchars($subject['subject_id']); ?>" <?php if ($subject['subject_id'] == $selected_subject_id) echo 'selected'; ?>>
                                            <?php echo htmlspecialchars($subject['subject_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="">No subjects available</option>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                </form>
            </div>

            <?php if ($selected_subject_id && !empty($class_id)): ?>
                <div class="row mb-2 mt-3">
                    <!-- Grades Summary Cards -->
                    <div class="col-md-3 ">
                        <div class="card text-white bg-success mb-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <h5 class="card-title">Written Tasks</h5>
                                    <i class="bi bi-pencil-square" style="font-size: 2rem;"></i>
                                </div>
                                <p class="card-text">Score: <?php echo isset($written_scores['percentage_score']) ? htmlspecialchars($written_scores['percentage_score']) . '%' : 'N/A'; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-primary mb-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <h5 class="card-title">Performance Tasks</h5>
                                    <i class="bi bi-bar-chart" style="font-size: 2rem;"></i>
                                </div>
                                <p class="card-text">Score: <?php echo isset($performance_scores['percentage_score']) ? htmlspecialchars($performance_scores['percentage_score']) . '%' : 'N/A'; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-warning mb-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <h5 class="card-title">Assessments</h5>
                                    <i class="bi bi-clipboard-check" style="font-size: 2rem;"></i>
                                </div>
                                <p class="card-text">Score: <?php echo isset($assessment_scores['percentage_score']) ? htmlspecialchars($assessment_scores['percentage_score']) . '%' : 'N/A'; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-danger mb-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <h5 class="card-title">Final Grade</h5>
                                    <i class="bi bi-award" style="font-size: 2rem;"></i>
                                </div>
                                <p class="card-text">Grade: <?php echo isset($final_grade['final_grade']) ? htmlspecialchars($final_grade['final_grade']) : 'N/A'; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mb-4">
                    <!-- Grades Overview Chart -->
                    <div class="col-md-6">
                        <h4>Grades Overview</h4>
                        <div id="gradesChart" style="height: 400px;"></div>
                    </div>

                    <!-- Attendance Summary Pie Chart -->
                    <div class="col-md-6">
                        <h4>Attendance Summary</h4>
                        <div id="attendancePieChart" style="height: 400px;"></div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<!-- Highcharts and Calendar Scripts -->
<?php if ($selected_subject_id && !empty($class_id)): ?>
<script>
// Grades Overview Chart
Highcharts.chart('gradesChart', {
    chart: {
        type: 'column'
    },
    title: {
        text: 'Latest Grades Overview'
    },
    xAxis: {
        categories: ['Written Tasks', 'Performance Tasks', 'Assessments', 'Final Grade'],
        crosshair: true
    },
    yAxis: {
        min: 0,
        title: {
            text: 'Percentage Score (%)'
        }
    },
    tooltip: {
        headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
        pointFormat:
            '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
            '<td style="padding:0"><b>{point.y:.2f}%</b></td></tr>',
        footerFormat: '</table>',
        shared: true,
        useHTML: true
    },
    series: [{
        name: 'Score',
        data: [
            <?php echo isset($written_scores['percentage_score']) ? htmlspecialchars($written_scores['percentage_score']) : '0'; ?>,
            <?php echo isset($performance_scores['percentage_score']) ? htmlspecialchars($performance_scores['percentage_score']) : '0'; ?>,
            <?php echo isset($assessment_scores['percentage_score']) ? htmlspecialchars($assessment_scores['percentage_score']) : '0'; ?>,
            <?php echo isset($final_grade['final_grade']) ? htmlspecialchars($final_grade['final_grade']) : '0'; ?>
        ]
    }]
});

// Attendance Summary Pie Chart
Highcharts.chart('attendancePieChart', {
    chart: {
        type: 'pie'
    },
    title: {
        text: 'Attendance Summary'
    },
    series: [{
        name: 'Days',
        colorByPoint: true,
        data: [{
            name: 'Present',
            y: <?php echo isset($attendance_summary['present']) ? htmlspecialchars($attendance_summary['present']) : '0'; ?>,
            color: '#28a745'
        }, {
            name: 'Absent',
            y: <?php echo isset($attendance_summary['absent']) ? htmlspecialchars($attendance_summary['absent']) : '0'; ?>,
            color: '#dc3545'
        }, {
            name: 'Late',
            y: <?php echo isset($attendance_summary['late']) ? htmlspecialchars($attendance_summary['late']) : '0'; ?>,
            color: '#ffc107'
        }]
    }]
});
</script>
<?php endif; ?>

<!-- Bootstrap Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- FullCalendar JS -->
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>

</body>
</html>
