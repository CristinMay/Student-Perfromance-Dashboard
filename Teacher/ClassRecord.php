<?php
include 'config.php';
session_start();

if (!isset($_SESSION['teacher_id'])) {
    die("No teacher ID found.");
}

$teacher_id = $_SESSION['teacher_id'];

// Check if class_id and subject_id are set
$class_id = isset($_GET['class_id']) ? intval($_GET['class_id']) : 0;
$subject_id = isset($_GET['subject_id']) ? intval($_GET['subject_id']) : 0;
$school_year_id = isset($_GET['school_year_id']) ? intval($_GET['school_year_id']) : 0;

// Fetch class and subject details
function getClassDetails($class_id, $subject_id, $school_year_id) {
    global $conn;
    $sql = "SELECT c.class_name, s.subject_name, sy.quarter, sy.school_year, t.first_name, t.last_name
            FROM classes c
            JOIN class_per_subject cps ON c.class_id = cps.class_id
            JOIN subjects s ON cps.subject_id = s.subject_id
            JOIN school_years sy ON cps.school_year_id = sy.school_year_id
            JOIN teachers t ON cps.teacher_id = t.teacher_id
            WHERE c.class_id = ? AND s.subject_id = ? AND cps.school_year_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $class_id, $subject_id, $school_year_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function getStudentsInClass($class_id, $school_year_id) {
    global $conn;
    $sql = "SELECT s.* 
        FROM students s 
        JOIN student_class_years scy ON s.student_id = scy.student_id 
        WHERE scy.class_id = ? AND scy.school_year_id = ?
        ORDER BY s.last_name, s.first_name";

    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Database error: " . $conn->error);
    }
    $stmt->bind_param("ii", $class_id, $school_year_id);
    $stmt->execute();
    return $stmt->get_result();
}

// Fetch existing scores for students
function getScores($class_id, $subject_id, $student_id, $table, $school_year_id) {
    global $conn;
    $sql = "SELECT * FROM $table WHERE student_id = ? AND class_id = ? AND subject_id = ? AND school_year_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Database error: " . $conn->error);
    }
    $stmt->bind_param("iiii", $student_id, $class_id, $subject_id, $school_year_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function getMaxWrittenScores($class_id, $subject_id, $school_year_id) {
    global $conn;
    $sql = "SELECT * FROM max_written_scores WHERE class_id = ? AND subject_id = ? AND school_year_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $class_id, $subject_id, $school_year_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function getMaxPerformanceScores($class_id, $subject_id, $school_year_id) {
    global $conn;
    $sql = "SELECT * FROM max_performance_scores WHERE class_id = ? AND subject_id = ? AND school_year_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $class_id, $subject_id, $school_year_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}
function getMaxAssessmentScores($class_id, $subject_id, $school_year_id) {
    global $conn;
    $sql = "SELECT * FROM max_assessment_scores WHERE class_id = ? AND subject_id = ? AND school_year_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $class_id, $subject_id, $school_year_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['save_written'])) {
        // Save highest possible written scores
        $sql = "REPLACE INTO max_written_scores (class_id, subject_id, school_year_id, w1, w2, w3, w4, w5, w6, w7, w8, w9, w10, total_score, percentage_score, weighted_score) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "iiiddddddddddddd",
            $class_id,
            $subject_id,
            $school_year_id,
            $_POST['max_written_1'],
            $_POST['max_written_2'],
            $_POST['max_written_3'],
            $_POST['max_written_4'],
            $_POST['max_written_5'],
            $_POST['max_written_6'],
            $_POST['max_written_7'],
            $_POST['max_written_8'],
            $_POST['max_written_9'],
            $_POST['max_written_10'],
            $_POST['total_max_written'],
            $_POST['ps_written'],
            $_POST['ws_written']
        );
        $stmt->execute();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['save_performance'])) {
        // Save highest possible written scores
        $sql = "REPLACE INTO max_performance_scores (class_id, subject_id,school_year_id, p1, p2, p3, p4, p5, p6, p7, p8, p9, p10, total_score, percentage_score, weighted_score) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "iiiddddddddddddd",
            $class_id,
            $subject_id,
            $school_year_id,
            $_POST['max_performance_1'],
            $_POST['max_performance_2'],
            $_POST['max_performance_3'],
            $_POST['max_performance_4'],
            $_POST['max_performance_5'],
            $_POST['max_performance_6'],
            $_POST['max_performance_7'],
            $_POST['max_performance_8'],
            $_POST['max_performance_9'],
            $_POST['max_performance_10'],
            $_POST['total_max_performance'],
            $_POST['ps_performance'],
            $_POST['ws_performance']
        );
        $stmt->execute();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['save_assessment'])) {
        // Prepare to save highest possible written scores
        $max_assessment = isset($_POST['max_assessment']) ? $_POST['max_assessment'] : 0;
        $total_max_assessment = isset($_POST['total_max_assessment']) ? $_POST['total_max_assessment'] : 0;
        $ps_assessment = isset($_POST['ps_assessment']) ? $_POST['ps_assessment'] : 0;
        $ws_assessment = isset($_POST['ws_assessment']) ? $_POST['ws_assessment'] : 0;

        $sql = "REPLACE INTO max_assessment_scores (class_id, subject_id, school_year_id, assessment_score, total_score, percentage_score, weighted_score) 
                VALUES (?, ?, ?, ?, ?, ?,?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "iiidddd",
            $class_id,
            $subject_id,
            $school_year_id,
            $max_assessment,
            $total_max_assessment,
            $ps_assessment,
            $ws_assessment
        );
        $stmt->execute();
    }
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['save_all'])) {
        // Save Written Scores
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'written_score_') !== false) {
                $parts = explode('_', $key);
                $student_id = $parts[2];
                $index = intval($parts[3]);

                if ($value === '') {
                    $value = NULL;
                }

                $existing_scores = getScores($class_id, $subject_id, $student_id, 'written_scores', $school_year_id);

                if ($existing_scores) {
                    $sql = "UPDATE written_scores SET w$index = ?, total_score = ?, percentage_score = ?, weighted_score = ? WHERE student_id = ? AND class_id = ? AND subject_id = ? AND school_year_id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("dddiisii", $value, $_POST['total_written_' . $student_id], $_POST['percentage_written_' . $student_id], $_POST['ws_written'], $student_id, $class_id, $subject_id, $school_year_id);
                } else {
                    $sql = "INSERT INTO written_scores (student_id, class_id, subject_id, w$index, total_score, percentage_score, weighted_score, school_year_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("iiidddsi", $student_id, $class_id, $subject_id, $value, $_POST['total_written_' . $student_id], $_POST['percentage_written_' . $student_id], $_POST['ws_written'], $school_year_id);
                }
                $stmt->execute();
            }
        }

        // Save Performance Scores
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'performance_score_') !== false) {
                $parts = explode('_', $key);
                $student_id = $parts[2];
                $index = intval($parts[3]);

                if ($value === '') {
                    $value = NULL;
                }

                $existing_scores = getScores($class_id, $subject_id, $student_id, 'performance_scores', $school_year_id);

                if ($existing_scores) {
                    $sql = "UPDATE performance_scores SET p$index = ?, total_score = ?, percentage_score = ?, weighted_score = ? WHERE student_id = ? AND class_id = ? AND subject_id = ? AND school_year_id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("dddiisii", $value, $_POST['total_performance_' . $student_id], $_POST['percentage_performance_' . $student_id], $_POST['ws_performance'], $student_id, $class_id, $subject_id, $school_year_id);
                } else {
                    $sql = "INSERT INTO performance_scores (student_id, class_id, subject_id, p$index, total_score, percentage_score, weighted_score, school_year_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("iiidddsi", $student_id, $class_id, $subject_id, $value, $_POST['total_performance_' . $student_id], $_POST['percentage_performance_' . $student_id], $_POST['ws_performance'], $school_year_id);
                }
                $stmt->execute();
            }
        }

        // Save Assessment Scores
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'assessment_score_') !== false) {
                $parts = explode('_', $key);
                $student_id = $parts[2];

                if ($value === '') {
                    $value = NULL;
                }

                $existing_scores = getScores($class_id, $subject_id, $student_id, 'assessment_scores', $school_year_id);

                $total_score = isset($_POST['total_assessment_' . $student_id]) ? $_POST['total_assessment_' . $student_id] : 0;
                $percentage_score = isset($_POST['percentage_assessment_' . $student_id]) ? $_POST['percentage_assessment_' . $student_id] : 0;
                $weighted_score = isset($_POST['ws_assessment']) ? $_POST['ws_assessment'] : 0;

                if ($existing_scores) {
                    $sql = "UPDATE assessment_scores SET assessment_score = ?, total_score = ?, percentage_score = ?, weighted_score = ? WHERE student_id = ? AND class_id = ? AND subject_id = ? AND school_year_id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("dddiisii", $value, $total_score, $percentage_score, $weighted_score, $student_id, $class_id, $subject_id, $school_year_id);
                } else {
                    $sql = "INSERT INTO assessment_scores (student_id, class_id, subject_id, assessment_score, total_score, percentage_score, weighted_score, school_year_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("iiiddddi", $student_id, $class_id, $subject_id, $value, $total_score, $percentage_score, $weighted_score, $school_year_id);
                }
                $stmt->execute();
            }
        }

        // Save Final Grades
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'final_grade_') !== false) {
                $parts = explode('_', $key);
                $student_id = $parts[2];

                $existing_scores = getScores($class_id, $subject_id, $student_id, 'final_grades', $school_year_id);

                if ($existing_scores) {
                    $sql = "UPDATE final_grades SET final_grade = ? WHERE student_id = ? AND class_id = ? AND subject_id = ? AND school_year_id =?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("diiii", $value, $student_id, $class_id, $subject_id, $school_year_id);
                } else {
                    $sql = "INSERT INTO final_grades (student_id, class_id, subject_id, final_grade, school_year_id) VALUES (?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("iiidi", $student_id, $class_id, $subject_id, $value, $school_year_id);
                }
                $stmt->execute();
            }
        }

        // After saving, redirect to the same page to refresh scores
        header("Location: " . $_SERVER['PHP_SELF'] . "?class_id=$class_id&subject_id=$subject_id&school_year_id=$school_year_id");
        exit();
    }
}


$classDetails = getClassDetails($class_id, $subject_id, $school_year_id);
$students = getStudentsInClass($class_id, $school_year_id);
$existingw_max_scores = getMaxWrittenScores($class_id, $subject_id, $school_year_id);
$existingp_max_scores = getMaxPerformanceScores($class_id, $subject_id, $school_year_id);
$existinga_max_scores = getMaxAssessmentScores($class_id, $subject_id, $school_year_id);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Record - <?php echo htmlspecialchars($classDetails['class_name']); ?></title>
    <link rel="icon" type="image/x-icon" href="image/favicon.ico">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.5.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <style>
    @media print {
        .page-break {
            page-break-before: always; /* Page break before each new section */
        }
        /* Optionally hide buttons during printing */
        .btn {
            display: none;
        }
    }
</style>
</head>
<body>
<header class="fixed-top bg-light shadow-sm">
    <?php include "inc/Navbar.php"; ?>
</header>

<main style="margin-top: 100px;">
    <div class="container mt-4 ml-auto">
      <div class="container mt-4">
        <!-- Class Information Section -->
        <div class="row">
            <div class="col text-center mb-9">
                <table class="table table-bordered text-center">
                <thead>
                    <tr>
                        <th>Academic Year/Quarter</th>
                        <th>Grade and Section</th>
                        <th>Teacher</th>
                        <th>Subject</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <?php echo htmlspecialchars($classDetails['school_year']) . ' - ' . htmlspecialchars($classDetails['quarter']); ?>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($classDetails['class_name']); ?>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($classDetails['first_name']) . ' ' . htmlspecialchars($classDetails['last_name']); ?>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($classDetails['subject_name']); ?>
                        </td>
                    </tr>
                </tbody>
            </table>
            </div>
        </div>

    <form method="POST" action="">
        
        <!-- Nav tabs for different task types -->
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="written-tab" data-toggle="tab" href="#written" role="tab" aria-controls="written" aria-selected="true">Written Works</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="performance-tab" data-toggle="tab" href="#performance" role="tab" aria-controls="performance" aria-selected="false">Performance Tasks</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="assessment-tab" data-toggle="tab" href="#assessment" role="tab" aria-controls="assessment" aria-selected="false">Assessment Tasks</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="final-grades-tab" data-toggle="tab" href="#final-grades" role="tab" aria-controls="final-grades" aria-selected="false">Final Grades</a>
            </li>
        </ul>

        <div class="tab-content" id="myTabContent">
            <!-- Written Tasks Tab -->
            <div class="tab-pane fade show active" id="written" role="tabpanel" aria-labelledby="written-tab">
                <div class="table-responsive mt-3">
                    <table id="written-table" class="table table-bordered minimalistBlack">
                        <thead>
                            <tr>
                                <th rowspan="2">Student ID</th>
                                <th rowspan="2" style="width: 100px">Last Name</th>
                                <th rowspan="2" style="width: 150px">First Name</th>
                                <th colspan="10">Written Works</th>
                                <th rowspan="2">Total Score</th>
                                <th rowspan="2">Percentage Score (PS)</th>
                                <th rowspan="2">Weighted Score (WS)</th>
                            </tr>
                            <tr>
                                <?php for ($i = 1; $i <= 10; $i++): ?>
                                    <th>W<?php echo $i; ?></th>
                                <?php endfor; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="highest-possible-score-row">
                                    <td colspan="3"><strong>Highest Possible Score</strong></td>
                                    <?php for ($i = 1; $i <= 10; $i++): ?>
                                        <td>
                                            <input type="text" min="0" step="any" class="form-control max-written p-1 mb-1" name="max_written_<?php echo $i; ?>" value="<?php echo htmlspecialchars($existingw_max_scores["w{$i}"] ?? ''); ?>" oninput="calculateWrittenScores()">
                                        </td>
                                    <?php endfor; ?>
                                    <td>
                                    <input type="text" class="form-control p-1 mb-1" id="total_max_written" name="total_max_written" readonly value="<?php echo htmlspecialchars($existingw_max_scores['total_score'] ?? ''); ?>">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control p-1 mb-1" id="ps_written" name="ps_written" readonly value="100">
                                    </td>
                                    <td>
                                        <input type="text" min="0" max="100" step="any" id="ws_written_input" name="ws_written" class="form-control p-1 mb-1" value="<?php echo htmlspecialchars($existingw_max_scores['weighted_score'] ?? ''); ?>" oninput="calculateFinalGrades()">
                                    </td>
                                </tr>
                            <?php foreach ($students as $student): 
                                // Fetch existing scores for this student
                                $existing_scores = getScores($class_id, $subject_id, $student['student_id'], 'written_scores', $school_year_id);
                            ?>
                                <tr data-student-id="<?php echo htmlspecialchars($student['student_id']); ?>">
                                    <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                                    <td><?php echo htmlspecialchars($student['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($student['first_name']); ?></td>
                                    <?php for ($i = 1; $i <= 10; $i++): ?>
                                        <td>
                                            <input type="text" min="0" step="any" class="form-control written-score p-1 mb-1" name="written_score_<?php echo $student['student_id']; ?>_<?php echo $i; ?>" value="<?php echo htmlspecialchars($existing_scores["w{$i}"] ?? ''); ?>" oninput="calculateWrittenScores()">
                                        </td>
                                    <?php endfor; ?>
                                    <td><input type="text" class="form-control total_written p-1 mb-1" name="total_written_<?php echo $student['student_id']; ?>" value="<?php echo htmlspecialchars($existing_scores['total_score'] ?? ''); ?>" readonly></td>
                                    <td><input type="text" class="form-control percentage_written p-1 mb-1" name="percentage_written_<?php echo $student['student_id']; ?>" value="<?php echo htmlspecialchars($existing_scores['percentage_score'] ?? ''); ?>" readonly></td>
                                    <td><input type="text" class="form-control weighted_written p-1 mb-1" name="weighted_written_<?php echo $student['student_id']; ?>" value="<?php echo htmlspecialchars($existing_scores['weighted_score'] ?? ''); ?>" readonly></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="text-end mb-2">
         <input type="submit" name="save_all" class="btn btn-primary" value="Save All">
        <button type="button" class="btn btn-secondary ms-2" onclick="printClassRecord()">Print Class Record</button>
    </div>
            </div>
            <!-- Performance Tasks Tab -->
            <div class="tab-pane fade" id="performance" role="tabpanel" aria-labelledby="performance-tab">
                <div class="table-responsive mt-3">
                    <table id="performance-table" class="table table-bordered minimalistBlack">
                        <thead>
                        <tr>
                                <th rowspan="2">Student ID</th>
                                <th rowspan="2" style="width: 100px">Last Name</th>
                                <th rowspan="2" style="width: 150px">First Name</th>
                                <th colspan="10">Written Works</th>
                                <th rowspan="2">Total Score</th>
                                <th rowspan="2">Percentage Score (PS)</th>
                                <th rowspan="2">Weighted Score (WS)</th>
                            </tr>
                            <tr>
                                <?php for ($i = 1; $i <= 10; $i++): ?>
                                    <th>P<?php echo $i; ?></th>
                                <?php endfor; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="highest-possible-score-row">
                                <td colspan="3"><strong>Highest Possible Score</strong></td>
                                <?php for ($i = 1; $i <= 10; $i++): ?>
                                    <td><input type="text" min="0" step="any" class="form-control max-performance p-1 mb-1" name="max_performance_<?php echo $i; ?>"value="<?php echo htmlspecialchars($existingp_max_scores["p{$i}"] ?? ''); ?>" oninput="calculateWrittenScores()">

                                <?php endfor; ?>
                                <td><input type="text" class="form-control p-1 mb-1" id="total_max_performance" name="total_max_performance" readonly value="<?php echo htmlspecialchars($existingp_max_scores['total_score'] ?? ''); ?>"></td>
                                <td><input type="text" class="form-control p-1 mb-1" id="ps_performance" name="ps_performance" readonly  value="100">
                                </td>
                                <td><input type="text" min="0" max="100" step="any" id="ws_performance_input" name="ws_performance" placeholder="Edit WS %" class="form-control p-1 mb-1" value="<?php echo htmlspecialchars($existingp_max_scores['weighted_score'] ?? ''); ?>" oninput="calculateFinalGrades()"></td>
                            </tr>
                            <?php
                            foreach ($students as $student): 
                                $existing_scores = getScores($class_id, $subject_id, $student['student_id'], 'performance_scores', $school_year_id);
                                ?>
                                <tr data-student-id="<?php echo htmlspecialchars($student['student_id']); ?>">
                                    <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                                    <td><?php echo htmlspecialchars($student['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($student['first_name']); ?></td>
                                    <?php for ($i = 1; $i <= 10; $i++): ?>
                                        <td><input type="text" min="0" step="any" class="form-control performance-score p-1 mb-1" name="performance_score_<?php echo $student['student_id']; ?>_<?php echo $i; ?>" value="<?php echo htmlspecialchars($existing_scores["p{$i}"] ?? ''); ?>" oninput="calculatePerformanceScores()"></td>
                                    <?php endfor; ?>
                                    <td><input type="text" class="form-control total_performance p-1 mb-1" name="total_performance_<?php echo $student['student_id']; ?>" value="<?php echo htmlspecialchars($existing_scores['total_score'] ?? ''); ?>" readonly></td>

                                    <td><input type="text" class="form-control percentage_performance p-1 mb-1" name="percentage_performance_<?php echo $student['student_id']; ?>"value="<?php echo htmlspecialchars($existing_scores['percentage_score'] ?? ''); ?>" readonly></td>

                                    <td><input type="text" class="form-control weighted_performance p-1 mb-1" name="weighted_performance_<?php echo $student['student_id']; ?>"value="<?php echo htmlspecialchars($existing_scores['weighted_score'] ?? ''); ?>" readonly></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div class="text-end mb-2">
         <input type="submit" name="save_all" class="btn btn-primary" value="Save All">
        <button type="button" class="btn btn-secondary ms-2" onclick="printClassRecord()">Print Class Record</button>
    </div>
            </div>
                                    </div>

            <!-- Assessment Tasks Tab -->
            <div class="tab-pane fade" id="assessment" role="tabpanel" aria-labelledby="assessment-tab">
                <div class="table-responsive mt-3">
                    <table id="assessment-table" class="table table-bordered minimalistBlack">
                        <thead>
                            <tr>
                                <th rowspan="2">Student ID</th>
                                <th rowspan="2" style="width: 100px">Last Name</th>
                                <th rowspan="2" style="width: 150px">First Name</th>
                                <th rowspan="2">Assessment Score</th>
                                <th rowspan="2">Total Score</th>
                                <th rowspan="2">Percentage Score (PS)</th>
                                <th rowspan="2">Weighted Score (WS)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="highest-possible-score-row">
                                <td colspan="3"><strong>Highest Possible Score</strong></td>
                                <td><input type="text" min="0" step="any" class="form-control max-assessment p-1 mb-1" name="max_assessment" value="<?php echo htmlspecialchars($existinga_max_scores["assessment_score"] ?? ''); ?>" oninput="calculateAssessmentScores()"></td>
                                <td><input type="text" class="form-control p-1 mb-1" id="total_max_assessment" name="total_max_assessment" readonly value="<?php echo htmlspecialchars($existinga_max_scores['total_score'] ?? ''); ?>"></td>
                                <td><input type="text" class="form-control p-1 mb-1" id="ps_assessment" name="ps_assessment" readonly  value="100"></td>
                                <td><input type="text" min="0" max="100" step="any" id="ws_assessment_input" name="ws_assessment" placeholder="Edit WS %" class="form-control" value="<?php echo htmlspecialchars($existinga_max_scores['weighted_score'] ?? ''); ?>" oninput="calculateFinalGrades()"></td>
                            </tr>
                            <?php
                            foreach ($students as $student):
                                $existing_scores = getScores($class_id, $subject_id, $student['student_id'], 'assessment_scores', $school_year_id); 
                            ?>
                                <tr data-student-id="<?php echo htmlspecialchars($student['student_id']); ?>">
                                    <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                                    <td><?php echo htmlspecialchars($student['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($student['first_name']); ?></td>
                                    <td><input type="text" min="0" step="any" class="form-control assessment-score p-1 mb-1" name="assessment_score_<?php echo $student['student_id']; ?>" value="<?php echo htmlspecialchars($existing_scores["assessment_score"] ?? ''); ?>" oninput="calculateAssessmentScores()"></td>
                                    <td><input type="text" class="form-control total_assessment p-1 mb-1" name="total_assessment_<?php echo $student['student_id']; ?>" value="<?php echo htmlspecialchars($existing_scores['total_score'] ?? ''); ?>" readonly></td>
                                    <td><input type="text" class="form-control percentage_assessment p-1 mb-1" name="percentage_assessment_<?php echo $student['student_id']; ?>" value="<?php echo htmlspecialchars($existing_scores['percentage_score'] ?? ''); ?>" readonly></td>
                                    <td><input type="text" class="form-control weighted_assessment p-1 mb-1" name="weighted_assessment_<?php echo $student['student_id']; ?>" value="<?php echo htmlspecialchars($existing_scores['weighted_score'] ?? ''); ?>" readonly></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div class="text-end mb-2">
        <input type="submit" name="save_all" class="btn btn-primary" value="Save All">
        <button type="button" class="btn btn-secondary ms-2" onclick="printClassRecord()">Print Class Record</button>
    </div>                </div>
            </div>

            <!-- Final Grades Tab -->
            <div class="tab-pane fade" id="final-grades" role="tabpanel" aria-labelledby="final-grades-tab">
                <div class="table-responsive mt-3">
                    <table id="final-grades-table" class="table table-bordered minimalistBlack">
                        <thead>
                            <tr>
                                <th>Student ID</th>
                                <th>Last Name</th>
                                <th>First Name</th>
                                <th>Final Grade</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($students as $student):
                             $existing_scores = getScores($class_id, $subject_id, $student['student_id'], 'final_grades', $school_year_id); 
                             ?>
                                <tr data-student-id="<?php echo htmlspecialchars($student['student_id']); ?>">
                                    <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                                    <td><?php echo htmlspecialchars($student['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($student['first_name']); ?></td>
                                    <td><input type="text" class="form-control final-grade p-1 mb-1" name="final_grade_<?php echo $student['student_id']; ?>" value="<?php echo htmlspecialchars($existing_scores['final_grade'] ?? ''); ?>"></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div class="text-end mb-2">
        <button type="button" class="btn btn-secondary ms-2" onclick="printClassRecord()">Print Class Record</button>
    </div>                </div>
                
            </div>
        </div>
                                
            </form>
        </div>
    </div>
</main>

<script>


function printClassRecord() {
    // Create a new window for printing
    var printWindow = window.open('', '', 'height=600,width=800');

    // Get the teacher's name from the page (modify the selector as needed)
    var teacherName = document.querySelectorAll("td")[2].innerText; // Assuming the teacher's name is in the third <td>

    // Construct the HTML content for printing
    var content = `
        <html>
            <head>
                <title>Print Class Record</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
                <style>
                    body { font-size: 12px; }
                    .table { margin-bottom: 20px; }
                    .signature { margin-top: 50px; text-align: center; }
                    .signature-line { border-top: 1px solid black; width: 200px; margin: 0 auto; }

                </style>
            </head>
            <body>
                <h3 class="text-center">Class Record - ${document.title}</h3>
                <h5>Academic Year: ${document.querySelector("td").innerText}</h5>
                <h5>Grade and Section: ${document.querySelectorAll("td")[1].innerText}</h5>
                <h5>Teacher: ${teacherName}</h5>
                <h5>Subject: ${document.querySelectorAll("td")[3].innerText}</h5>
                
                <h4>Written Works</h4>
                ${document.getElementById('written-table').outerHTML}
                <div style="page-break-after: always;"></div>
                
                <h4>Performance Tasks</h4>
                ${document.getElementById('performance-table').outerHTML}
                <div style="page-break-after: always;"></div>
                
                <h4>Assessment Tasks</h4>
                ${document.getElementById('assessment-table').outerHTML}
                <div style="page-break-after: always;"></div>
                
                <h4>Final Grades</h4>
                ${document.getElementById('final-grades-table').outerHTML}
                
                <div class="signature">
                <p>Submitted by:</p>
                    <div class="signature-line"></div>
                    <p>${teacherName}</p> 
                    <p>Subject teacher</p>
                    <br>
                    <p>Submitted by:</p>
                    <div class="signature-line"></div>
                    <p>Zeinada R. Delantar</p> 
                    <p>School Principal</p>
                </div>
            </body>
        </html>
    `;

    // Write the content to the new window
    printWindow.document.write(content);
    printWindow.document.close();
    printWindow.print();
}


function calculateWrittenScores() {
    let totalMaxWritten = 0;
    let weightWritten = parseFloat(document.getElementById('ws_written_input').value) || 0;

    // Calculate total max written scores
    const maxWrittenInputs = document.querySelectorAll('.max-written');
    maxWrittenInputs.forEach(input => {
        totalMaxWritten += parseFloat(input.value) || 0;
    });

    document.getElementById('total_max_written').value = totalMaxWritten.toFixed(2);

    // Recalculate each student's score
    const students = document.querySelectorAll('#written-table tbody tr[data-student-id]');
    students.forEach(studentRow => {
        const writtenScores = studentRow.querySelectorAll('.written-score');
        let totalScore = 0;

        writtenScores.forEach(scoreInput => {
            totalScore += parseFloat(scoreInput.value) || 0;
        });

        // Update the total, percentage, and weighted scores
        const percentage = totalMaxWritten ? (totalScore / totalMaxWritten) * 100 : 0;
        const weightedScore = (percentage * weightWritten / 100) || 0;

        studentRow.querySelector('.total_written').value = totalScore.toFixed(2);
        studentRow.querySelector('.percentage_written').value = percentage.toFixed(2);
        studentRow.querySelector('.weighted_written').value = weightedScore.toFixed(2);
    });

    // Calculate final grades after updating written scores
    calculateFinalGrades();
}

function calculatePerformanceScores() {
    let totalMaxPerformance = 0;
    let weightPerformance = parseFloat(document.getElementById('ws_performance_input').value) || 0;

    // Calculate total max performance scores
    const maxPerformanceInputs = document.querySelectorAll('.max-performance');
    maxPerformanceInputs.forEach(input => {
        totalMaxPerformance += parseFloat(input.value) || 0;
    });

    document.getElementById('total_max_performance').value = totalMaxPerformance.toFixed(2);

    // Recalculate each student's score
    const students = document.querySelectorAll('#performance-table tbody tr[data-student-id]');
    students.forEach(studentRow => {
        const performanceScores = studentRow.querySelectorAll('.performance-score');
        let totalScore = 0;

        performanceScores.forEach(scoreInput => {
            totalScore += parseFloat(scoreInput.value) || 0;
        });

        // Update the total, percentage, and weighted scores
        const percentage = totalMaxPerformance ? (totalScore / totalMaxPerformance) * 100 : 0;
        const weightedScore = (percentage * weightPerformance / 100) || 0;

        studentRow.querySelector('.total_performance').value = totalScore.toFixed(2);
        studentRow.querySelector('.percentage_performance').value = percentage.toFixed(2);
        studentRow.querySelector('.weighted_performance').value = weightedScore.toFixed(2);
    });

    // Calculate final grades after updating performance scores
    calculateFinalGrades();
}

function calculateAssessmentScores() {
    const maxAssessmentInput = document.querySelector('.max-assessment');
    const totalMaxAssessment = parseFloat(maxAssessmentInput.value) || 0;
    document.getElementById('total_max_assessment').value = totalMaxAssessment.toFixed(2);

    // Retrieve the weight for Assessment
    const weightAssessment = parseFloat(document.getElementById('ws_assessment_input').value) || 0;

    // Recalculate each student's score
    const students = document.querySelectorAll('#assessment-table tbody tr[data-student-id]');
    students.forEach(studentRow => {
        const assessmentScoreInput = studentRow.querySelector('.assessment-score');
        const assessmentScore = parseFloat(assessmentScoreInput.value) || 0;

        // Update the total, percentage, and weighted scores
        const percentage = totalMaxAssessment ? (assessmentScore / totalMaxAssessment) * 100 : 0;
        const weightedScore = (percentage * weightAssessment / 100) || 0;

        studentRow.querySelector('.total_assessment').value = assessmentScore.toFixed(2);
        studentRow.querySelector('.percentage_assessment').value = percentage.toFixed(2);
        studentRow.querySelector('.weighted_assessment').value = weightedScore.toFixed(2);
    });

    // Calculate final grades after updating assessment scores
    calculateFinalGrades();
}

// Transmutation Table function
function transmuteGrade(finalGrade) {
    if (finalGrade=== 100) return 100;
    else if (finalGrade>= 98.4) return 99;
    else if (finalGrade  >= 96.8) return 98;
    else if (finalGrade  >= 95.2) return 97;
    else if (finalGrade  >= 93.6) return 96;
    else if (finalGrade  >= 92) return 95;
    else if (finalGrade  >= 90.4) return 94;
    else if (finalGrade  >= 88.8) return 93;
    else if (finalGrade  >= 87.2) return 92;
    else if (finalGrade  >= 85.6) return 91;
    else if (finalGrade  >= 84) return 90;
    else if (finalGrade  >= 82.4) return 89;
    else if (finalGrade  >= 80.8) return 88;
    else if (finalGrade  >= 79.2) return 87;
    else if (finalGrade  >= 77.6) return 86;
    else if (finalGrade  >= 76) return 85;
    else if (finalGrade  >= 74.4) return 84;
    else if (finalGrade  >= 72.8) return 83;
    else if (finalGrade  >= 71.2) return 82;
    else if (finalGrade  >= 69.6) return 81;
    else if (finalGrade  >= 68) return 80;
    else if (finalGrade  >= 66.4) return 79;
    else if (finalGrade  >= 64.8) return 78;
    else if (finalGrade  >= 63.2) return 77;
    else if (finalGrade  >= 61.6) return 76;
    else if (finalGrade  >= 0) return 75;
    else return 'Invalid Grade';
}

function calculateFinalGrades() {
    // Retrieve weights
    const weightWritten = parseFloat(document.getElementById('ws_written_input').value) || 0;
    const weightPerformance = parseFloat(document.getElementById('ws_performance_input').value) || 0;
    const weightAssessment = parseFloat(document.getElementById('ws_assessment_input').value) || 0;

    // Validate that the total weight equals 100
    const totalWeight = weightWritten + weightPerformance + weightAssessment;
    if (totalWeight !== 100) {
        console.warn('The total weight of Written, Performance, and Assessment tasks must equal 100%. Currently, it is ' + totalWeight + '%. Please adjust the weights accordingly.');
    }

    // Iterate through each student row in the Final Grades table
    const finalGradesRows = document.querySelectorAll('#final-grades-table tbody tr[data-student-id]');
    finalGradesRows.forEach((row) => {
        const studentId = row.getAttribute('data-student-id');

        // Retrieve weighted scores from Written Tasks
        const writtenRow = document.querySelector(`#written-table tbody tr[data-student-id="${studentId}"]`);
        const weightedWritten = parseFloat(writtenRow.querySelector('.weighted_written').value) || 0;

        // Retrieve weighted scores from Performance Tasks
        const performanceRow = document.querySelector(`#performance-table tbody tr[data-student-id="${studentId}"]`);
        const weightedPerformance = parseFloat(performanceRow.querySelector('.weighted_performance').value) || 0;

        // Retrieve weighted scores from Assessment Tasks
        const assessmentRow = document.querySelector(`#assessment-table tbody tr[data-student-id="${studentId}"]`);
        const weightedAssessment = parseFloat(assessmentRow.querySelector('.weighted_assessment').value) || 0;

        // Calculate the final grade
        const finalGrade = weightedWritten + weightedPerformance + weightedAssessment;

        // Transmute the final grade
        const transmutedGrade = transmuteGrade(finalGrade);

        // Update the Final Grade input field
        row.querySelector('.final-grade').value = transmutedGrade.toFixed(2);
    });
}


// Attach event listeners to recalculate scores when inputs change
document.querySelectorAll('.max-written').forEach(input => {
    input.addEventListener('input', calculateWrittenScores);
});

document.querySelectorAll('.max-performance').forEach(input => {
    input.addEventListener('input', calculatePerformanceScores);
});

document.querySelectorAll('.max-assessment').forEach(input =>{
    input.addEventListener('input', calculateAssessmentScores);
});

// Add event listeners to weight inputs to trigger final grade recalculation
document.getElementById('ws_written_input').addEventListener('input', calculateFinalGrades);
document.getElementById('ws_performance_input').addEventListener('input', calculateFinalGrades);
document.getElementById('ws_assessment_input').addEventListener('input', calculateFinalGrades);

// Initialize final grades on page load
$(document).ready(function() {
    calculateWrittenScores();
    calculatePerformanceScores();
    calculateAssessmentScores();
});
</script>

<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
