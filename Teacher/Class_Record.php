<?php  
include 'config.php'; // Include your database connection script

session_start();
if (!isset($_SESSION['teacher_id'])) {
    die("No teacher ID found.");
}

$teacher_id = $_SESSION['teacher_id'];

// Fetch school years
function getSchoolYears() {
    global $conn;
    $sql = "SELECT * FROM school_years";
    $result = $conn->query($sql);
    $options = '';
    while ($row = $result->fetch_assoc()) {
        $formatted = $row['school_year'] . ' - ' . $row['quarter']; // Combine school year and quarter
        $options .= '<option value="' . $row['school_year_id'] . '">' . $formatted . '</option>';
    }
    return $options;
}

// Fetch classes along with subjects based on school year and teacher
function getClassesWithSubjects($school_year_id) {
    global $conn, $teacher_id;
    $sql = "SELECT c.class_id, c.class_name, s.subject_id, s.subject_name 
            FROM class_per_subject cps
            JOIN classes c ON cps.class_id = c.class_id
            JOIN subjects s ON cps.subject_id = s.subject_id
            WHERE cps.school_year_id = ? AND cps.teacher_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $school_year_id, $teacher_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $classes = [];
    while ($row = $result->fetch_assoc()) {
        $class_id = $row['class_id'];
        if (!isset($classes[$class_id])) {
            $classes[$class_id] = [
                'class_id' => $class_id,
                'class_name' => $row['class_name'],
                'subjects' => []
            ];
        }
        $classes[$class_id]['subjects'][] = [
            'subject_id' => $row['subject_id'],
            'subject_name' => $row['subject_name']
        ];
    }
    return array_values($classes);
}

// Handle AJAX requests
if (isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'fetchSchoolYears':
            echo getSchoolYears();
            break;
        case 'fetchClasses':
            $classes = getClassesWithSubjects($_POST['school_year_id']);
            echo json_encode($classes);
            break;
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Class Record</title>
    <link rel="icon" type="image/x-icon" href="image/favicon.ico">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.5.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        /* Custom styles for class boxes */
.card {
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    overflow: hidden;
}


.card-body {
    padding: 20px;
    text-align: center;
}

.card-title {
    font-size: 1.25rem;
    font-weight: bold;
    color: #00ab41;
}

.list-group-item {
    border: none;
    padding: 10px;
    background-color: #f8f9fa;
    transition: background-color 0.3s ease;
}

.list-group-item:hover {
    background-color: #e2e6ea;
}

.list-group-item a {
    text-decoration: none;
    color: #333;
}

.list-group-item a:hover {
    color: #007bff;
}

#class-container {
    display: flex;
    flex-wrap: wrap;

}


.form-control {
    background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 8 8"><path d="M2 3l3 3 3-3H2z"/></svg>');
    background-repeat: no-repeat;
    background-position: right 10px center;
    background-size: 12px; /* Adjust the size of the icon */
    padding-right: 30px; /* Ensure space for the icon */
}

    </style>

    <script>
    $(document).ready(function() {
        $('#school-year-quarter-select').change(function() {
            const schoolYearId = $(this).val();
            if (schoolYearId) {
                $.ajax({
                    type: 'POST',
                    url: 'Class_Record.php', // Adjust the URL accordingly
                    data: { action: 'fetchClasses', school_year_id: schoolYearId },
                    success: function(response) {
                        const classes = JSON.parse(response);
                        $('#class-container').empty();
                        if (classes.length) {
                            classes.forEach(classObj => {
                                let subjectList = '<ul class="list-group mt-2">';
                                classObj.subjects.forEach(subject => {
                                    subjectList += 
                                        `<li class="list-group-item">
                                            <a href="ClassRecord.php?class_id=${classObj.class_id}&subject_id=${subject.subject_id}&school_year_id=${schoolYearId}">
                                                ${subject.subject_name}
                                            </a>
                                        </li>`;
                                });
                                subjectList += '</ul>';

                                $('#class-container').append(
                                    `<div class="col-md-3 mb-3">
                                        <div class="card">
                                            <div class="card-body">
                                                <h5 class="card-title">${classObj.class_name}</h5>
                                                ${subjectList}
                                            </div>
                                        </div>
                                    </div>`
                                );
                            });
                        } else {
                            $('#class-container').append('<p>No classes found for this school year.</p>');
                        }
                    }
                });
            }
        });
    });
</script>
</head>
<body style="padding-top: 80px;">
<header class="fixed-top bg-light shadow-sm">
    <?php include "inc/Navbar.php"; ?>
</header>
<main style="margin-top: 50px;">
    <div class="container mt-4 ml-auto">
    <h2>Class Record</h2>
        <!-- School Year/Quarter Dropdown -->
        <div class="form-row mb-4">
            <div class="form-group col-md-12">
            <div class="p-4 border rounded bg-light shadow-sm">
                <label for="school-year-quarter-select">School Year/Quarter:</label>
                <select id="school-year-quarter-select" class="form-control">
                    <option value="">Select School Year</option>
                    <?php echo getSchoolYears(); ?>
                </select>
            </div>
        </div>
</div>

        <!-- Class Boxes for Grades 7-10 -->
        <div class="p-4 border rounded bg-light shadow-sm">
        <div class="row" id="class-container">
            <!-- Class boxes will be populated here -->
        </div>
</div>
    </div>
</main>
</body>
</html>
