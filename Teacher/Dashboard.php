<?php
session_start();
$teacher_id = $_SESSION['teacher_id'];

// Include database connection
include 'config.php';

// Fetch necessary data for dropdowns
function getSchoolYears() {
    global $conn;
    $sql = "SELECT * FROM school_years";
    return $conn->query($sql);
}

function getClassesByTeacher($teacher_id) {
    global $conn;
    $sql = "SELECT DISTINCT c.class_id, c.class_name 
            FROM classes c 
            JOIN class_per_subject cps ON c.class_id = cps.class_id 
            WHERE cps.teacher_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $teacher_id);
    $stmt->execute();
    return $stmt->get_result();
}

function getSubjectsByTeacher($teacher_id) {
    global $conn;
    $sql = "SELECT DISTINCT s.subject_id, s.subject_name 
            FROM subjects s 
            JOIN class_per_subject cps ON s.subject_id = cps.subject_id 
            WHERE cps.teacher_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $teacher_id);
    $stmt->execute();
    return $stmt->get_result();
}

function getStudentsInClass($class_id, $school_year_id) {
    global $conn;
    $sql = "SELECT s.student_id, CONCAT(s.first_name, ' ', s.last_name) AS name 
            FROM students s 
            JOIN student_class_years sc ON s.student_id = sc.student_id 
            WHERE sc.class_id = ? AND sc.school_year_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $class_id, $school_year_id);
    $stmt->execute();
    return $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Ranking Dashboard</title>
    <link rel="icon" type="image/x-icon" href="image/favicon.ico">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.5.0/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://code.highcharts.com/modules/drilldown.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <style>
        .form-control {
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 8 8"><path d="M2 3l3 3 3-3H2z"/></svg>');
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 12px;
            padding-right: 30px;
        }

        .ranking-table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }

        .ranking-table th, .ranking-table td {
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
        }

        .ranking-table th {
            background-color: #f8f9fa;
        }

        .ranking-table td {
            background-color: #ffffff;
        }

        .ranking-table tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .ranking-table tr:hover {
            background-color: #ddd;
        }

        .ranking-table .ranking {
            font-weight: bold;
            color: #007bff;
        }

        .ranking-table .name {
            font-size: 1.1em;
        }
    </style>
</head>
<body>

<header class="fixed-top bg-light shadow-sm">
    <?php include "inc/Navbar.php"; ?>
</header>   

<main class="col-md-9 col-lg-10 ml-sm-auto px-md-4 py-4" style="margin-top: 85px;">
    <h1>Student Ranking Dashboard</h1>

    <!-- Box Container for Filters -->
    <div class="p-4 border rounded bg-light shadow-sm">
        <div class="form-row">
            <div class="form-group col-md-4">
                <label for="schoolYear">School Year</label>
                <select id="schoolYear" class="form-control">
                    <option value="">Select School Year</option>
                    <?php $years = getSchoolYears(); while($year = $years->fetch_assoc()): ?>
                        <option value="<?php echo $year['school_year_id']; ?>"><?php echo $year['school_year']; ?> - <?php echo $year['quarter']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group col-md-4">
                <label for="classSelect">Class</label>
                <select id="classSelect" class="form-control">
                    <option value="">Select Class</option>
                    <?php $classes = getClassesByTeacher($teacher_id); while($class = $classes->fetch_assoc()): ?>
                        <option value="<?php echo $class['class_id']; ?>"><?php echo $class['class_name']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group col-md-4">
                <label for="subjectSelect">Subject</label>
                <select id="subjectSelect" class="form-control">
                    <option value="">Select Subject</option>
                    <?php $subjects = getSubjectsByTeacher($teacher_id); while($subject = $subjects->fetch_assoc()): ?>
                        <option value="<?php echo $subject['subject_id']; ?>"><?php echo $subject['subject_name']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>
        <button id="loadData" class="btn btn-primary">Load Data</button>
    </div>

    <!-- Ranking Table Container -->
    <div id="rankingContainer" class="mt-4" style="display: none;">
        <table class="ranking-table">
            <thead>
                <tr>
                    <th>Ranking</th>
                    <th>Student Name</th>
                    <th>Final Grade</th>
                </tr>
            </thead>
            <tbody id="rankingTableBody"></tbody>
        </table>
    </div>

</main>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script>
$(document).ready(function() {
    $('#loadData').click(function() {
        const schoolYearId = $('#schoolYear').val();
        const classId = $('#classSelect').val();
        const subjectId = $('#subjectSelect').val();

        if (schoolYearId && classId && subjectId) {
            $.ajax({
                url: 'fetch_student_ranking.php',
                method: 'POST',
                data: {
                    school_year_id: schoolYearId,
                    class_id: classId,
                    subject_id: subjectId
                },
                success: function(data) {
                    const studentData = JSON.parse(data);
                    if (studentData.length > 0) {
                        renderRankingTable(studentData);
                        $('#rankingContainer').show(); // Show ranking table
                    } else {
                        alert('No data found.');
                        $('#rankingContainer').hide(); // Hide ranking table if no data
                    }
                },
                error: function(err) {
                    console.error(err);
                }
            });
        } else {
            alert('Please select all fields.');
        }
    });

    function renderRankingTable(students) {
        const tableBody = $('#rankingTableBody');
        tableBody.empty(); // Clear previous data

        students.forEach((student, index) => {
            const row = `<tr>
                <td class="ranking">${index + 1}</td>
                <td class="name">${student.name}</td>
                <td>${student.final_grade}</td>
            </tr>`;
            tableBody.append(row);
        });
    }
});
</script>

</body>
</html>
