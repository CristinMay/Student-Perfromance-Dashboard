<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Dashboard</title>
    <link rel="icon" type="image/x-icon" href="image/favicon.ico">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.5.0/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
      <style>
        body {
            background-color: #f4f7fa;
            font-family: Arial, sans-serif;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .card .icon-circle {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            color: #fff;
        }
        .bg-primary { background-color: #007bff !important; }
        .bg-success { background-color: #28a745 !important; }
        .bg-warning { background-color: #ffc107 !important; }
        .bg-info { background-color: #17a2b8 !important; }
        .bg-dark { background-color: #343a40 !important; }
        .bg-secondary { background-color: #6c757d !important; }
        h6 {
            color: #6c757d;
            font-weight: bold;
            margin-top: 10px;
        }
        .display-6 {
            font-size: 1.8rem;
            font-weight: bold;
        }
    </style>
</head>

<body style="padding-top: 80px;">
    <header>
        <?php include "inc/Navbar.php"; ?>
    </header>

    <?php
   include 'config.php';

    // Fetch total counts
    $totalClasses = $conn->query("SELECT COUNT(*) AS total FROM classes")->fetch_assoc()['total'];
    $totalSubjects = $conn->query("SELECT COUNT(*) AS total FROM subjects")->fetch_assoc()['total'];
    $totalSchoolYears = $conn->query("SELECT COUNT(*) AS total FROM school_years")->fetch_assoc()['total'];
    $totalClassPerSubject = $conn->query("SELECT COUNT(*) AS total FROM class_per_subject")->fetch_assoc()['total'];
    $totalStudents = $conn->query("SELECT COUNT(*) AS total FROM students")->fetch_assoc()['total'];
    $totalTeachers = $conn->query("SELECT COUNT(*) AS total FROM teachers")->fetch_assoc()['total'];

    $conn->close();
    ?>

    <main class="pt-5" style="margin-top: -90px;">
        <div class="container mt-5">
            <div class="row row-cols-1 row-cols-md-2 g-4">
                <!-- Total Classes -->
                <div class="col">
                 <a href="class.php" class="card h-100 border text-decoration-none">
                    <div class="card h-100 border">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-4 d-flex align-items-center justify-content-center">
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                                        <i class="fa fa-graduation-cap fa-2x"></i>
                                    </div>
                                </div>
                                <div class="col-8">
                                    <div>
                                        <p class="display-6" id="totalClasses"><?php echo $totalClasses; ?></p>
                                    </div>
                                    <div>
                                        <h6>Total Classes</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    </a>
                </div>

                <!-- Total Subjects -->
                <div class="col">
                 <a href="Subject.php" class="card h-100 border text-decoration-none">
                    <div class="card h-100 border">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-4 d-flex align-items-center justify-content-center">
                                    <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                                        <i class="fa fa-book fa-2x"></i>
                                    </div>
                                </div>
                                <div class="col-8">
                                    <div>
                                        <p class="display-6" id="totalSubjects"><?php echo $totalSubjects; ?></p>
                                    </div>
                                    <div>
                                        <h6>Total Subjects</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    </a>
                </div>

                <!-- Total School Years -->
                <div class="col">
                 <a href="School_year.php" class="card h-100 border text-decoration-none">
                    <div class="card h-100 border">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-4 d-flex align-items-center justify-content-center">
                                    <div class="rounded-circle bg-warning text-white d-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                                        <i class="fa fa-calendar fa-2x"></i>
                                    </div>
                                </div>
                                <div class="col-8">
                                    <div>
                                        <p class="display-6" id="totalSchoolYears"><?php echo $totalSchoolYears; ?></p>
                                    </div>
                                    <div>
                                        <h6>School Year/Quarter</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    </a>
                </div>

                <!-- Class per Subject -->
                <div class="col">
                 <a href="class_per_subject.php" class="card h-100 border text-decoration-none">
                    <div class="card h-100 border">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-4 d-flex align-items-center justify-content-center">
                                    <div class="rounded-circle bg-info text-white d-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                                        <i class="fa fa-list fa-2x"></i>
                                    </div>
                                </div>
                                <div class="col-8">
                                    <div>
                                        <p class="display-6" id="totalClassPerSubject"><?php echo $totalClassPerSubject; ?></p>
                                    </div>
                                    <div>
                                        <h6>Class per Subject</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    </a>
                </div>

                <!-- Total Students -->
                <div class="col">
                 <a href="Student.php" class="card h-100 border text-decoration-none">
                    <div class="card h-100 border">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-4 d-flex align-items-center justify-content-center">
                                    <div class="rounded-circle bg-dark text-white d-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                                        <i class="fa fa-users fa-2x"></i>
                                    </div>
                                </div>
                                <div class="col-8">
                                    <div>
                                        <p class="display-6" id="totalStudents"><?php echo $totalStudents; ?></p>
                                    </div>
                                    <div>
                                        <h6>Total Students</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    </a>
                </div>

                <!-- Total Teachers -->
                <div class="col">
                 <a href="Teacher.php" class="card h-100 border text-decoration-none">
                    <div class="card h-100 border">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-4 d-flex align-items-center justify-content-center">
                                    <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                                        <i class="fa fa-users fa-2x"></i>
                                    </div>
                                </div>
                                <div class="col-8">
                                    <div>
                                        <p class="display-6" id="totalTeachers"><?php echo $totalTeachers; ?></p>
                                    </div>
                                    <div>
                                        <h6>Total Teachers</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    </a>
                </div>

            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function updateCounts() {
            $.ajax({
                url: '', // We are using the same page, no need to specify URL
                method: 'GET',
                success: function(data) {
                    // Assuming the PHP script returns the data as HTML
                    const totalClasses = '<?php echo $totalClasses; ?>';
                    const totalSubjects = '<?php echo $totalSubjects; ?>';
                    const totalSchoolYears = '<?php echo $totalSchoolYears; ?>';
                    const totalClassPerSubject = '<?php echo $totalClassPerSubject; ?>';
                    const totalStudents = '<?php echo $totalStudents; ?>';
                    const totalTeachers = '<?php echo $totalTeachers; ?>';
                    
                    $('#totalClasses').text(totalClasses);
                    $('#totalSubjects').text(totalSubjects);
                    $('#totalSchoolYears').text(totalSchoolYears);
                    $('#totalClassPerSubject').text(totalClassPerSubject);
                    $('#totalStudents').text(totalStudents);
                    $('#totalTeachers').text(totalTeachers);
                }
            });
        }

        // Call updateCounts periodically
        setInterval(updateCounts, 60000); // Update every 60 seconds
    </script>
</body>
</html>
