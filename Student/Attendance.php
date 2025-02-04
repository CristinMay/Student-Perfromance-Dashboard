<?php 
session_start();

// Check if the student is logged in
if (isset($_SESSION['student_id'])) {
    $student_id = $_SESSION['student_id'];
} else {
    // Handle the case where the student is not logged in
    echo "You must be logged in to view this page.";
    exit;
}

// Include database connection
include 'config.php'; // Ensure this file contains the necessary database connection code

// Fetch student's name and class
$student_query = "SELECT first_name, last_name FROM students WHERE student_id = ?";
$stmt = $conn->prepare($student_query);
$stmt->bind_param("i", $student_id); 
$stmt->execute();
$student_result = $stmt->get_result();
$student = $student_result->fetch_assoc();

if (!$student) {
    echo "Student not found.";
    exit;
}

// Fetch classes assigned to the student
$class_query = "SELECT c.class_id, c.class_name FROM classes c 
                INNER JOIN student_class_years scy ON c.class_id = scy.class_id 
                WHERE scy.student_id = ?";
$stmt = $conn->prepare($class_query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$class_result = $stmt->get_result();

// Fetch subjects assigned to the student
$subject_query = "SELECT DISTINCT sub.subject_id, sub.subject_name 
                  FROM subjects sub
                  INNER JOIN class_per_subject cps ON sub.subject_id = cps.subject_id
                  INNER JOIN student_class_years scy ON cps.class_id = scy.class_id
                  WHERE scy.student_id = ?";
$stmt = $conn->prepare($subject_query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$subject_result = $stmt->get_result();

// Fetch school years and quarters
$year_query = "SELECT * FROM school_years";
$year_result = $conn->query($year_query);

// Initialize selected_year_id and selected_subject_id
$selected_year_id = null;
$selected_subject_id = null;

// Initialize attendance summary
$attendance_summary = [
    'present' => 0,
    'absent' => 0,
    'late' => 0,
];

// Ensure the student is assigned to a class before querying subjects
if ($class = $class_result->fetch_assoc()) {
    // Check for selected year and subject from the dropdown
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $selected_year_id = isset($_POST['selected_year_id']) ? intval($_POST['selected_year_id']) : null; // Sanitize input
        $selected_subject_id = isset($_POST['selected_subject_id']) ? intval($_POST['selected_subject_id']) : null;
    } else {
        // Set a default selected_year_id (e.g., the first year available)
        if ($year = $year_result->fetch_assoc()) {
            $selected_year_id = $year['school_year_id'];
            // Reset the year_result pointer if needed
            $year_result->data_seek(0);
        }
    }

    // Fetch attendance data for the student, selected school year, and selected subject
    $attendance_query = "SELECT attendance_date, status FROM attendance 
                         WHERE student_id = ? AND school_year_id = ? AND subject_id = ?";
    $stmt = $conn->prepare($attendance_query);
    $stmt->bind_param("iii", $student_id, $selected_year_id, $selected_subject_id);
    $stmt->execute();
    $attendance_result = $stmt->get_result();

    $attendance_events = [];
    // Set a threshold for too many absences
    $absence_threshold = 3;

    // After fetching attendance data
    while ($attendance = $attendance_result->fetch_assoc()) {
        // Increment attendance summary counts
        $attendance_summary[$attendance['status']]++;

        // Determine the color based on status
        $color = '';
        switch ($attendance['status']) {
            case 'present':
                $color = 'green';
                break;
            case 'absent':
                $color = 'red';
                break;
            case 'late':
                $color = 'orange';
                break;
            default:
                $color = 'blue';
        }

        $attendance_events[] = [
            'title' => ucfirst($attendance['status']),
            'start' => $attendance['attendance_date'],
            'color' => $color
        ];
    }

    // Warning message for too many absences
    $warning_message = '';
    if ($attendance_summary['absent'] > $absence_threshold) {
        $warning_message = "Warning: You have too many absences ({$attendance_summary['absent']} total). Please be aware of your attendance.";
    }
    
} else {
    echo "No class assigned to this student.";
    exit;
}

// Close database connection
$stmt->close();
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Attendance Calendar</title>
    <link rel="icon" type="image/x-icon" href="image/favicon.ico">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- FullCalendar CSS -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet" />

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/style.css"> 
    <link rel="stylesheet" href="css/student.css">

    <!-- Bootstrap Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.5.0/font/bootstrap-icons.css" rel="stylesheet">

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- FullCalendar JS -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>

    <!-- Tippy.js -->
    <!-- Tippy.js (CDN link updated to version 6.3.1) -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tippy.js/6.3.1/tippy.css" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.11.6/umd/popper.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/tippy.js/6.3.1/tippy-bundle.umd.min.js"></script>
        <link rel="stylesheet" href="https://unpkg.com/tippy.js@6/dist/tippy.css" />
        <script src="https://unpkg.com/tippy.js@6"></script>

    <style>
        /* Custom Calendar Styles */
        #calendar {
            background-color: #f8f9fa; /* Light background for the calendar */
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Subtle shadow */
            padding: 20px;
        }

        .fc-daygrid-day-number {
            color: black !important; /* Set the color of the numbers to black */
            border: none; /* Remove any border */
            text-decoration: none; /* Remove underline */
        }

        .fc-daygrid-event {
            border-radius: 5px; /* Rounded corners for events */
            padding: 5px 10px; /* Padding for event text */
            transition: transform 0.2s; /* Animation on hover */
        }

        .fc-daygrid-event:hover {
            transform: scale(1.05); /* Slightly enlarge event on hover */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2); /* Add shadow on hover */
        }

        /* Custom colors for attendance statuses */
        .fc-event-green {
            background-color: #28a745; /* Green for present */
            color: white;
        }

        .fc-event-red {
            background-color: #dc3545; /* Red for absent */
            color: white;
        }

        .fc-event-orange {
            background-color: #fd7e14; /* Orange for late */
            color: white;
        }

        .fc-event-blue {
            background-color: #007bff; /* Blue for others */
            color: white;
        }

        /* Alert styles */
        .alert {
            margin-top: 20px;
        }

        /* Center button and improve appearance */
        .form-select{
            width: 100%; /* Full width */
        }

        /* Responsive center alignment for dropdowns */
        .dropdown-container {
            text-align: center; /* Center text */
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
        <form method="POST" class="mb-4">
        <div class="row g-4 align-items-center">
            <div class="col-md-12">
                <!-- Box Container for Dropdowns and Button -->
                <div class="p-4 border rounded bg-light shadow-sm"> <!-- Box styling -->
                    <div class="row">
                        <div class="col-md-6 dropdown-container">
                            <label for="selected_year_id" class="col-form-label">Select School Year:</label>
                            <select name="selected_year_id" id="selected_year_id" class="form-select">
                                <?php
                                $year_result->data_seek(0);
                                while ($year = $year_result->fetch_assoc()) {
                                    $selected = ($year['school_year_id'] == $selected_year_id) ? 'selected' : '';
                                    echo "<option value=\"{$year['school_year_id']}\" $selected>{$year['school_year']}-{$year['quarter']}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="col-md-6 dropdown-container">
                            <label for="selected_subject_id" class="col-form-label">Select Subject:</label>
                            <select name="selected_subject_id" id="selected_subject_id" class="form-select">
                                <?php
                                while ($subject = $subject_result->fetch_assoc()) {
                                    $selected = ($subject['subject_id'] == $selected_subject_id) ? 'selected' : '';
                                    echo "<option value=\"{$subject['subject_id']}\" $selected>{$subject['subject_name']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-12 dropdown-container">
                            <button type="submit" class="btn btn-primary w-100">View Attendance</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
                <!-- Display warning message if exists -->
                <?php if ($warning_message): ?>
                    <div class="alert alert-warning" role="alert">
                        <?php echo $warning_message; ?>
                    </div>
                <?php endif; ?>

        <div class="container mt-8 pt-6">
            <div class="row justify-content-start">

                <div class="col-md-8"> <!-- Calendar Column -->
                    <div id='calendar'></div>
                </div>

                <div class="col-md-4"> <!-- Attendance Summary Column -->
                    <div class="card shadow-sm">
                        <div class="card-header bg-info text-white">
                            <strong>Attendance Summary</strong>
                        </div>
                        <div class="card-body">
                            <p class="card-text">
                                <i class="bi bi-check-circle-fill text-success"></i> Present: 
                                <span class="text-success"><?php echo $attendance_summary['present']; ?></span>
                            </p>
                            <div class="progress mb-2">
                                <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo ($attendance_summary['present'] / ($attendance_summary['present'] + $attendance_summary['absent'] + $attendance_summary['late'])) * 100; ?>%;">
                                    <?php  ($attendance_summary['present'] / ($attendance_summary['present'] + $attendance_summary['absent'] + $attendance_summary['late'])) * 100; ?>
                                </div>
                            </div>
                            
                            <p class="card-text">
                                <i class="bi bi-x-circle-fill text-danger"></i> Absent: 
                                <span class="text-danger"><?php echo $attendance_summary['absent']; ?></span>
                            </p>
                            <div class="progress mb-2">
                                <div class="progress-bar bg-danger" role="progressbar" style="width: <?php echo ($attendance_summary['absent'] / ($attendance_summary['present'] + $attendance_summary['absent'] + $attendance_summary['late'])) * 100; ?>%;">
                                    <?php  ($attendance_summary['absent'] / ($attendance_summary['present'] + $attendance_summary['absent'] + $attendance_summary['late'])) * 100; ?>
                                </div>
                            </div>
                            
                            <p class="card-text">
                                <i class="bi bi-clock-fill text-warning"></i> Late: 
                                <span class="text-warning"><?php echo $attendance_summary['late']; ?></span>
                            </p>
                            <div class="progress mb-2">
                                <div class="progress-bar bg-warning" role="progressbar" style="width: <?php echo ($attendance_summary['late'] / ($attendance_summary['present'] + $attendance_summary['absent'] + $attendance_summary['late'])) * 100; ?>%;">
                                    <?php ($attendance_summary['late'] / ($attendance_summary['present'] + $attendance_summary['absent'] + $attendance_summary['late'])) * 100; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    </main>

    <!-- Initialize FullCalendar -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var attendanceEvents = <?php echo json_encode($attendance_events); ?>;

            var calendarEl = document.getElementById('calendar');

            // Modify event rendering to include custom classes
            attendanceEvents.forEach(function(event) {
                if (event.color) {
                    event.classNames = ['fc-event-' + event.color];
                }
            });

            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                events: attendanceEvents,
                eventDidMount: function(info) {
                    // Add tooltip with status using Tippy.js
                    tippy(info.el, {
                        content: info.event.title,
                        placement: 'top',
                        animation: 'scale',
                    });
                },
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: ''
                },
                height: 'auto'
            });

            calendar.render();
        });
    </script>

</body>
</html>

