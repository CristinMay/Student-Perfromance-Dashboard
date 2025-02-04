<?php 
session_start();

if (isset($_SESSION['student_id'])) {
    $student_id = $_SESSION['student_id'];
} else {
    echo "You must be logged in to view this page.";
    exit;
}

include 'config.php'; // Ensure this file contains your database connection logic

// Fetch student's name, class, and school year
$student_query = "SELECT first_name, last_name, c.class_id 
                  FROM students s 
                  INNER JOIN student_class_years sc ON s.student_id = sc.student_id 
                  INNER JOIN classes c ON sc.class_id = c.class_id 
                  WHERE s.student_id = ?";
$stmt = $conn->prepare($student_query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student_result = $stmt->get_result();
$student = $student_result->fetch_assoc();

if (!$student) {
    echo "Student not found.";
    exit;
}

// Get the student's class name
$class_name = 'N/A';

// Fetch classes associated with the student's current class
$classes_query = "SELECT DISTINCT c.class_id, c.class_name 
                  FROM student_class_years sc 
                  INNER JOIN classes c ON sc.class_id = c.class_id 
                  WHERE sc.student_id = ?";
$class_stmt = $conn->prepare($classes_query);
$class_stmt->bind_param("i", $student_id);
$class_stmt->execute();
$classes_result = $class_stmt->get_result();

// Prepare to fetch grades and teachers
$final_grades = [];
$selected_class_id = $student['class_id']; // Default to the student's current class

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_class_id'])) {
    $selected_class_id = intval($_POST['selected_class_id']);
}

// Fetch the class name for the selected class ID
$class_name_query = "SELECT class_name FROM classes WHERE class_id = ?";
$class_stmt = $conn->prepare($class_name_query);
$class_stmt->bind_param("i", $selected_class_id);
$class_stmt->execute();
$class_name_result = $class_stmt->get_result();
$class_name_data = $class_name_result->fetch_assoc();
$class_name = $class_name_data['class_name'] ?? 'N/A'; // Default to 'N/A' if not found

// Fetch school year associated with the selected class
$school_year_query = "SELECT sy.school_year, sy.quarter 
                      FROM student_class_years sc 
                      INNER JOIN school_years sy ON sc.school_year_id = sy.school_year_id 
                      WHERE sc.student_id = ? AND sc.class_id = ?";
$school_year_stmt = $conn->prepare($school_year_query);
$school_year_stmt->bind_param("ii", $student_id, $selected_class_id);
$school_year_stmt->execute();
$school_year_result = $school_year_stmt->get_result();
$school_year_data = $school_year_result->fetch_assoc();
$school_year = $school_year_data['school_year'] ?? 'N/A'; // Default to 'N/A' if not found
$quarter = $school_year_data['quarter'] ?? 'N/A'; // Default to 'N/A' if not found

// Fetch grades based on the selected class
$grades_query = "SELECT s.subject_name, fg.final_grade, sy.quarter, t.first_name AS teacher_first, t.last_name AS teacher_last
                 FROM final_grades fg
                 INNER JOIN subjects s ON fg.subject_id = s.subject_id
                 INNER JOIN school_years sy ON fg.school_year_id = sy.school_year_id
                 INNER JOIN class_per_subject cps ON cps.subject_id = s.subject_id
                 INNER JOIN teachers t ON cps.teacher_id = t.teacher_id
                 WHERE fg.student_id = ? AND fg.class_id = ?
                 ORDER BY sy.quarter";

$stmt = $conn->prepare($grades_query);
$stmt->bind_param("ii", $student_id, $selected_class_id);
$stmt->execute();
$grades_result = $stmt->get_result();

// Process fetched grades and calculate averages
while ($grade = $grades_result->fetch_assoc()) {
    $final_grades[$grade['subject_name']]['grades'][$grade['quarter']] = $grade['final_grade'];
    $final_grades[$grade['subject_name']]['teacher'] = $grade['teacher_first'] . ' ' . $grade['teacher_last'];
}

// Calculate final grades
foreach ($final_grades as $subject => $data) {
    $grades = array_filter($data['grades']);
    if (!empty($grades)) {
        $final_grades[$subject]['final_grade'] = number_format(array_sum($grades) / count($grades), 2);
    } else {
        $final_grades[$subject]['final_grade'] = '';
    }
}

// Calculate average grade
$total_grade = 0;
$grade_count = 0;

foreach ($final_grades as $subject => $data) {
    if ($data['final_grade'] !== '') {
        $total_grade += $data['final_grade'];
        $grade_count++;
    }
}

$average_grade = $grade_count > 0 ? number_format($total_grade / $grade_count, 2) : '';

$stmt->close();
$class_stmt->close();
$school_year_stmt->close();
$conn->close();

// Function to get remark based on final grade
function getRemark($final_grade) {
    if ($final_grade === '') {
        return '';
    }
    $final_grade = floatval($final_grade); // Convert to float for comparison
    if ($final_grade >= 90) {
        return 'Passed';
    } elseif ($final_grade >= 85) {
        return 'Passed';
    } elseif ($final_grade >= 80) {
        return 'Passed';
    } elseif ($final_grade >= 75) {
        return 'Passed';
    } else {
        return 'Did not meet expectations';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Grades</title>
    <link rel="icon" type="image/x-icon" href="image/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.5.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css"> 
    <link rel="stylesheet" href="css/student.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .grade-card {
            margin-top: 20px;
        }
        .average-badge {
            font-size: 15px;
            padding: 10px 20px;
        }
        .table-responsive {
            margin-top: 20px;
        }
        header.fixed-top {
            z-index: 1030;
        }

            @media print {
        body {
            margin: 0;
            padding: 0;
        }
        header {
            display: none; /* Hide header during print */
        }
        .container {
            width: 100%;
            padding: 0;
        }
        .grade-card {
            margin: 0;
            padding: 0;
        }
        .table {
            font-size: 10px; /* Adjust table font size for print */
        }
        .table th, .table td {
            padding: 4px; /* Reduce padding for print */
        }
        .print-layout {
            display: flex; /* Use flex for two columns */
            flex-direction: row;
        }
        .print-column {
            width: 50%; /* Each column takes half the width */
            padding: 10px;
        }
        /* Hide elements that should not appear in print */
        #classSelect {
            display: none; /* Hide the class selection dropdown */
        }
    }
    </style>
</head>
<body>
<header class="fixed-top bg-light shadow-sm">
    <?php include "inc/Navbar.php"; ?>
</header>
<main class="container mt-5 pt-5">
    <div class="row justify-content-center">
        <div class="col-md-10 offset-md-2">
        <button onclick="printReport()" class="btn btn-primary">Print Report Card</button>
        <div class="text-center">
                <img src="image/logo.png" alt="School Logo" class="img-fluid" style="max-height: 100px;"> <!-- Update the path to your school logo -->
                <h3>Caniogan High School</h3> <!-- Replace with actual school name -->
                <h5>Report Card</h5>
            </div>
            <!-- Student Information Card -->
            <div class="card grade-card">
                <div class="card-body">
                    <h3 class="card-title text-center mb-4">Grade Card</h3>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Name:</strong> <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></p>
                            <p><strong>Class:</strong> <?php echo htmlspecialchars($class_name); ?></p>
                            <p><strong>School Year:</strong> <?php echo htmlspecialchars($school_year); ?></p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label for="classSelect" class="form-label">Select Class:</label>
                                    <select name="selected_class_id" id="classSelect" class="form-select" onchange="this.form.submit()">
                                        <?php while ($class_option = $classes_result->fetch_assoc()): ?>
                                            <option value="<?php echo $class_option['class_id']; ?>" <?php if ($class_option['class_id'] == $selected_class_id) echo 'selected'; ?>>
                                                <?php echo htmlspecialchars($class_option['class_name']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
                                            
            <!-- Grades Table Card -->
            <div class="card grade-card">
                <div class="card-body">
                    <?php if (!empty($final_grades)): ?>
                        <h5 class="text-center">Class: <?php echo htmlspecialchars($class_name); ?></h5> 
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Subject</th>
                                        <th>Q1</th>
                                        <th>Q2</th>
                                        <th>Q3</th>
                                        <th>Q4</th>
                                        <th>Final Grade</th>
                                        <th>Remarks</th> <!-- New column for remarks -->
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($final_grades as $subject => $data): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($subject); ?></td>
                                            <td><?php echo htmlspecialchars($data['grades']['Q1'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($data['grades']['Q2'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($data['grades']['Q3'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($data['grades']['Q4'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($data['final_grade']); ?></td>
                                            <td><?php echo htmlspecialchars(getRemark($data['final_grade'])); ?></td> <!-- Add remark -->
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-success average-badge">Average Grade: <?php echo htmlspecialchars($average_grade); ?></span>
                        </div>
                    <?php else: ?>
                        <p class="text-center">No grades available.</p>
                    <?php endif; ?>
                </div>
                <!-- Grading Scale Remarks -->
             </div>
                        <div class="mt-4 mb-3">
                        <h5 class="text-center">Grading Scale Remarks</h5>
                        <ul class="list-group">           
                        <li class="list-group-item"><strong>Outstanding:</strong> 90-100 (Passed)</li>
                        <li class="list-group-item"><strong>Very Satisfactory:</strong> 85-89 (Passed)</li>
                        <li class="list-group-item"><strong>Satisfactory:</strong> 80-84 (Passed)</li>
                        <li class="list-group-item"><strong>Fairly Satisfactory:</strong> 75-79 (Passed)</li>
                        <li class="list-group-item"><strong>Did not meet expectations:</strong> Below 75 (Failed)</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
            
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function printReport() {
        // Hide the class selection dropdown
        const classSelect = document.getElementById('classSelect');
        classSelect.style.display = 'none';

        // Create a print layout
        const printContent = `
            <div class="print-layout">
                <div class="print-column">
                    <img src="image/logo.png" alt="School Logo" style="max-height: 100px;">
                    <h3>Caniogan High School</h3>
                    <h5>Report Card</h5>
                    <div class="card">
                        <div class="card-body">
                            <h3 class="card-title text-center mb-4">Grade Card</h3>
                            <p><strong>Name:</strong> ${"<?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>"}</p>
                            <p><strong>Class:</strong> ${"<?php echo htmlspecialchars($class_name); ?>"}</p>
                            <p><strong>School Year:</strong> ${"<?php echo htmlspecialchars($school_year); ?>"}</p>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Subject</th>
                                            <th>Q1</th>
                                            <th>Q2</th>
                                            <th>Q3</th>
                                            <th>Q4</th>
                                            <th>Final Grade</th>
                                            <th>Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${"<?php foreach ($final_grades as $subject => $data): ?>"}
                                        <tr>
                                            <td>${"<?php echo htmlspecialchars($subject); ?>"}</td>
                                            <td>${"<?php echo htmlspecialchars($data['grades']['Q1'] ?? ''); ?>"}</td>
                                            <td>${"<?php echo htmlspecialchars($data['grades']['Q2'] ?? ''); ?>"}</td>
                                            <td>${"<?php echo htmlspecialchars($data['grades']['Q3'] ?? ''); ?>"}</td>
                                            <td>${"<?php echo htmlspecialchars($data['grades']['Q4'] ?? ''); ?>"}</td>
                                            <td>${"<?php echo htmlspecialchars($data['final_grade']); ?>"}</td>
                                            <td>${"<?php echo htmlspecialchars(getRemark($data['final_grade'])); ?>"}</td>
                                        </tr>
                                        ${"<?php endforeach; ?>"}
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-success average-badge">Average Grade: ${"<?php echo htmlspecialchars($average_grade); ?>"}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="print-column">
                    <h5 class="text-center">Grading Scale Remarks</h5>
                    <ul class="list-group">
                        <li class="list-group-item"><strong>Outstanding:</strong> 90-100 (Passed)</li>
                        <li class="list-group-item"><strong>Very Satisfactory:</strong> 85-89 (Passed)</li>
                        <li class="list-group-item"><strong>Satisfactory:</strong> 80-84 (Passed)</li>
                        <li class="list-group-item"><strong>Fairly Satisfactory:</strong> 75-79 (Passed)</li>
                        <li class="list-group-item"><strong>Did not meet expectations:</strong> Below 75 (Failed)</li>
                    </ul>
                </div>
            </div>
        `;

        // Open a new window to print
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <html>
                <head>
                    <title>Print Report Card</title>
                    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css">
                    <style>
                        body { font-family: Arial, sans-serif; }
                        ${document.querySelector('style').innerHTML} /* Include existing styles */
                    </style>
                </head>
                <body>
                    ${printContent}
                </body>
            </html>
        `);
        printWindow.document.close();
        printWindow.print();

        // Restore the dropdown visibility
        classSelect.style.display = '';
    }
</script>

</body>
</html>
