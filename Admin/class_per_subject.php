<?php
// Database connection
include 'config.php';
session_start();

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch data for dropdowns and store in arrays
$classes = [];
$classes_result = $conn->query("SELECT class_id, class_name FROM classes WHERE is_archived = 0");
if ($classes_result) {
    while ($row = $classes_result->fetch_assoc()) {
        $classes[] = $row;
    }
} else {
    die("Error fetching classes: " . $conn->error);
}

$school_years = [];
$school_years_result = $conn->query("SELECT school_year_id, CONCAT(school_year, ' - ', quarter) AS school_year_quarter FROM school_years  WHERE is_archived = 0");
if ($school_years_result) {
    while ($row = $school_years_result->fetch_assoc()) {
        $school_years[] = $row;
    }
} else {
    die("Error fetching school years: " . $conn->error);
}

$subjects = [];
$subjects_result = $conn->query("SELECT subject_id, subject_name FROM subjects  WHERE is_archived = 0");
if ($subjects_result) {
    while ($row = $subjects_result->fetch_assoc()) {
        $subjects[] = $row;
    }
} else {
    die("Error fetching subjects: " . $conn->error);
}

$teachers = [];
$teachers_result = $conn->query("SELECT teacher_id, CONCAT(last_name, ', ', first_name) AS teacher_name FROM teachers  WHERE is_archived = 0");
if ($teachers_result) {
    while ($row = $teachers_result->fetch_assoc()) {
        $teachers[] = $row;
    }
} else {
    die("Error fetching teachers: " . $conn->error);
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['create'])) {
        // Insert new record
        $class_id = $_POST['class'];
        $school_year_id = $_POST['school_year'];
        $subject_id = $_POST['subject'];
        $teacher_id = $_POST['teacher'];
        $sql = "INSERT INTO class_per_subject (teacher_id, class_id, school_year_id, subject_id) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('iiii', $teacher_id, $class_id, $school_year_id, $subject_id);
        $stmt->execute();
        if ($stmt->error) {
            $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Error inserting record: ' . $stmt->error];
        } else {
            $_SESSION['alert'] = ['type' => 'success', 'message' => 'Record created successfully.'];
        }
        $stmt->close();
    } elseif (isset($_POST['update'])) {
        // Update existing record
        $id = $_POST['id'];
        $class_id = $_POST['class'];
        $school_year_id = $_POST['school_year'];
        $subject_id = $_POST['subject'];
        $teacher_id = $_POST['teacher'];
        $sql = "UPDATE class_per_subject SET teacher_id=?, class_id=?, school_year_id=?, subject_id=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('iiiii', $teacher_id, $class_id, $school_year_id, $subject_id, $id);
        $stmt->execute();
        if ($stmt->error) {
            $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Error updating record: ' . $stmt->error];
        } else {
            $_SESSION['alert'] = ['type' => 'success', 'message' => 'Record updated successfully.'];
        }
        $stmt->close();
    }
    elseif (isset($_POST['archive'])) {
        // Archive record
        $id = $_POST['id'];
        $sql = "UPDATE class_per_subject SET is_archived = 1 WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        if ($stmt->error) {
            $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Error archiving record: ' . $stmt->error];
        } else {
            $_SESSION['alert'] = ['type' => 'success', 'message' => 'Record archived successfully.'];
        }
        $stmt->close();
    } elseif (isset($_POST['unarchive'])) {
        // Unarchive record
        $id = $_POST['id'];
        $sql = "UPDATE class_per_subject SET is_archived = 0 WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        if ($stmt->error) {
            $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Error unarchiving record: ' . $stmt->error];
        } else {
            $_SESSION['alert'] = ['type' => 'success', 'message' => 'Record unarchived successfully.'];
        }
        $stmt->close();
    }
    // Redirect to the same page to display the alert
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Fetch data for table with error checking
$results = $conn->query("SELECT cps.id, c.class_id, c.class_name, sy.school_year_id, CONCAT(sy.school_year, ' - ', sy.quarter) AS school_year_quarter, s.subject_id, s.subject_name, t.teacher_id, CONCAT(t.last_name, ', ', t.first_name) AS teacher_name
                        FROM class_per_subject cps
                        JOIN classes c ON cps.class_id = c.class_id
                        JOIN school_years sy ON cps.school_year_id = sy.school_year_id
                        JOIN subjects s ON cps.subject_id = s.subject_id
                        JOIN teachers t ON cps.teacher_id = t.teacher_id");
if (!$results) {
    die("Error fetching records: " . $conn->error);
}

// Initialize filter variables
$filter_class = isset($_GET['filter_class']) ? intval($_GET['filter_class']) : null;
$filter_school_year = isset($_GET['filter_school_year']) ? intval($_GET['filter_school_year']) : null;
$filter_subject = isset($_GET['filter_subject']) ? intval($_GET['filter_subject']) : null;
$filter_teacher = isset($_GET['filter_teacher']) ? intval($_GET['filter_teacher']) : null;

$filter_status = isset($_GET['filter_status']) ? intval($_GET['filter_status']) : null;

$query = "SELECT cps.id, c.class_id, c.class_name, sy.school_year_id, CONCAT(sy.school_year, ' - ', sy.quarter) AS school_year_quarter,
                s.subject_id, s.subject_name, t.teacher_id, CONCAT(t.last_name, ', ', t.first_name) AS teacher_name, cps.is_archived
          FROM class_per_subject cps
          JOIN classes c ON cps.class_id = c.class_id
          JOIN school_years sy ON cps.school_year_id = sy.school_year_id
          JOIN subjects s ON cps.subject_id = s.subject_id
          JOIN teachers t ON cps.teacher_id = t.teacher_id
          WHERE 1=1";


// Apply existing filters
if ($filter_class) {
    $query .= " AND cps.class_id = $filter_class";
}
if ($filter_school_year) {
    $query .= " AND cps.school_year_id = $filter_school_year";
}
if ($filter_subject) {
    $query .= " AND cps.subject_id = $filter_subject";
}
if ($filter_teacher) {
    $query .= " AND cps.teacher_id = $filter_teacher";
}

// Apply the archive filter
if ($filter_status !== null) {
    $query .= " AND cps.is_archived = $filter_status";
}

$results = $conn->query($query);
if (!$results) {
    die("Error fetching records: " . $conn->error);
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Create Class Per Subject</title>
    <link rel="icon" type="image/x-icon" href="image/favicon.ico">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.5.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
        <style>/* Container Modernization */
.container {
    background-color: #f5f7fa; /* Light, cool gray for a neutral background */
    padding: 2rem; /* Add more padding */
    border-radius: 12px; /* Rounded corners */
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05); /* Soft shadow for subtle depth */
}

/* Card Modernization */
.card {
    border: none; /* Remove card border */
    border-radius: 12px; /* Rounded corners */
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05); /* Very subtle shadow */
}

.card-header {
    background-color: #81c784; /* Soft green for headers */
    color: #ffffff; /* White text for contrast */
    font-weight: bold;
    border-top-left-radius: 12px;
    border-top-right-radius: 12px;
}

/* Table Styling */
.table-striped {
    border-radius: 8px;
    overflow: hidden; /* For rounded borders */
    background-color: #ffffff; /* Soft white for table background */
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05); /* Subtle shadow */
}

.table-striped thead {
    background-color: #81c784; /* Soft green header */
    color: #ffffff; /* White text */
}

.table-striped tbody tr:hover {
    background-color: #edf5ee; /* Very light green for hover effect */
}

.table-striped th,
.table-striped td {
    padding: 1rem;
    vertical-align: middle;
    border: none; /* Remove table borders */
}

/* Button Styling */
.btn-info, .btn-danger, .btn-primary {
    border-radius: 6px; /* Rounded buttons */
    font-weight: 600;
    padding: 0.5rem 1rem;
}

.btn-primary {
    background-color: #81c784; /* Consistent soft green color */
    border: none;
}

.btn-info {
    background-color: #4fc3f7; /* Soft blue for info buttons */
}

.btn-danger {
    background-color: #e57373; /* Soft red for danger buttons */
}

/* Modal Styling */
.modal-content {
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); /* Subtle shadow for focus */
}

.modal-header {
    background-color: #81c784; /* Soft green header */
    color: #ffffff;
    border-top-left-radius: 12px;
    border-top-right-radius: 12px;
}

.btn-close {
    filter: invert(1); /* Make close button white */
}
</style>
</head>

<body  style="padding-top: 80px;">

<header>
    <?php include "inc/Navbar.php"; ?>
</header>

<main class="pt-2">
    <div class="container pt-4">
        <!-- Form Card -->
        <div class="card mb-4">
            <div class="card-header">
                Create New Class Per Subject
            </div>
            <div class="card-body">
                <form action="class_per_subject.php" method="POST">
                    <!-- Class Dropdown -->
                    <div class="row mb-3">
                        <div class="col-md-3 d-flex align-items-center">
                            <label for="class" class="form-label">Class</label>
                        </div>
                        <div class="col-md-9">
                            <select class="form-select" id="class" name="class" required>
                                <option value="" disabled selected>Select Class</option>
                                <?php foreach ($classes as $row) { ?>
                                    <option value="<?php echo $row['class_id']; ?>"><?php echo $row['class_name']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <!-- School Year/Quarter Dropdown -->
                    <div class="row mb-3">
                        <div class="col-md-3 d-flex align-items-center">
                            <label for="school_year" class="form-label">School Year/Quarter</label>
                        </div>
                        <div class="col-md-9">
                            <select class="form-select" id="school_year" name="school_year" required>
                                <option value="" disabled selected>Select School Year/Quarter</option>
                                <?php foreach ($school_years as $row) { ?>
                                    <option value="<?php echo $row['school_year_id']; ?>"><?php echo $row['school_year_quarter']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <!-- Subject Dropdown -->
                    <div class="row mb-3">
                        <div class="col-md-3 d-flex align-items-center">
                            <label for="subject" class="form-label">Subject</label>
                        </div>
                        <div class="col-md-9">
                            <select class="form-select" id="subject" name="subject" required>
                                <option value="" disabled selected>Select Subject</option>
                                <?php foreach ($subjects as $row) { ?>
                                    <option value="<?php echo $row['subject_id']; ?>"><?php echo $row['subject_name']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <!-- Teacher Dropdown -->
                    <div class="row mb-3">
                        <div class="col-md-3 d-flex align-items-center">
                            <label for="teacher" class="form-label">Teacher</label>
                        </div>
                        <div class="col-md-9">
                            <select class="form-select" id="teacher" name="teacher" required>
                                <option value="" disabled selected>Select Teacher</option>
                                <?php foreach ($teachers as $row) { ?>
                                    <option value="<?php echo $row['teacher_id']; ?>"><?php echo $row['teacher_name']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <button type="submit" name="create" class="btn btn-primary">Add</button>
                    <button type="reset" class="btn btn-secondary">Clear</button>
                </form>
            </div>
        </div>

<!-- Filter Section -->
<div class="card mb-4">
    <div class="card-header">
        Filter Records
    </div>
    <div class="card-body">
        <form action="class_per_subject.php" method="GET" class="row g-2">
            <div class="col-md-3">
                <select class="form-select" name="filter_class">
                    <option value="" disabled selected>Select Class</option>
                    <?php foreach ($classes as $row) { ?>
                        <option value="<?php echo $row['class_id']; ?>"><?php echo $row['class_name']; ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" name="filter_school_year">
                    <option value="" disabled selected>Select School Year/Quarter</option>
                    <?php foreach ($school_years as $row) { ?>
                        <option value="<?php echo $row['school_year_id']; ?>"><?php echo $row['school_year_quarter']; ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" name="filter_subject">
                    <option value="" disabled selected>Select Subject</option>
                    <?php foreach ($subjects as $row) { ?>
                        <option value="<?php echo $row['subject_id']; ?>"><?php echo $row['subject_name']; ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" name="filter_teacher">
                    <option value="" disabled selected>Select Teacher</option>
                    <?php foreach ($teachers as $row) { ?>
                        <option value="<?php echo $row['teacher_id']; ?>"><?php echo $row['teacher_name']; ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" name="filter_status">
                    <option value="" disabled selected>Select Status</option>
                    <option value="0">Active</option>
                    <option value="1">Archived</option>
                    <option value="">Both</option>
                </select>
            </div>
            <div class="col-md-12 text-end mt-2">
                <button type="submit" class="btn btn-primary">Apply Filter</button>
            </div>
        </form>
    </div>
</div>


<?php
// Display alert messages
if (isset($_SESSION['alert'])):
    $alert = $_SESSION['alert'];
    $alert_type = $alert['type'];
    $alert_message = $alert['message'];
    unset($_SESSION['alert']); // Clear the alert after displaying it
?>
    <div class="alert alert-<?php echo $alert_type; ?> alert-dismissible fade show" role="alert">
        <?php echo $alert_message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

        <!-- Table Card -->
        <div class="card">
            <div class="card-header">
                Class Per Subject List
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
    
                                <th scope="col">Class</th>
                                <th scope="col">School Year/Quarter</th>
                                <th scope="col">Subject</th>
                                <th scope="col">Teacher</th>
                                <th scope="col">Status</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="classTable">
                            <?php while ($row = $results->fetch_assoc()) { ?>
                                <tr>
                                    <td><?php echo $row['class_name']; ?></td>
                                    <td><?php echo $row['school_year_quarter']; ?></td>
                                    <td><?php echo $row['subject_name']; ?></td>
                                    <td><?php echo $row['teacher_name']; ?></td>
                                    <td><?php echo ($row['is_archived'] == 0) ? 'Active' : 'Archived'; ?></td>
                                   <!-- Actions -->
<td>
    <button class="btn btn-info btn-sm edit-btn" data-bs-toggle="modal" data-bs-target="#editModal"
        data-class="<?php echo $row['class_id']; ?>"
        data-school-year="<?php echo $row['school_year_id']; ?>"
        data-subject="<?php echo $row['subject_id']; ?>"
        data-teacher="<?php echo $row['teacher_id']; ?>">
        Edit
    </button>

<?php if ($row['is_archived'] == 0) { ?>
    <button class="btn btn-warning btn-sm archive-btn" data-bs-toggle="modal" data-bs-target="#archiveModal" data-id="<?php echo $row['id']; ?>">Archive</button>
<?php } else { ?>
    <button class="btn btn-success btn-sm unarchive-btn" data-bs-toggle="modal" data-bs-target="#unarchiveModal" data-id="<?php echo $row['id']; ?>">Unarchive</button>
<?php } ?>

</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Archive Modal -->
<div class="modal fade" id="archiveModal" tabindex="-1" aria-labelledby="archiveModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title" id="archiveModalLabel">Archive Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="class_per_subject.php" method="POST">
                <div class="modal-body">
                    <p>Are you sure you want to archive this record?</p>
                    <input type="hidden" id="archive-id" name="id">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="archive" class="btn btn-warning">Archive</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Unarchive Modal -->
<div class="modal fade" id="unarchiveModal" tabindex="-1" aria-labelledby="unarchiveModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="unarchiveModalLabel">Unarchive Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="class_per_subject.php" method="POST">
                <div class="modal-body">
                    <p>Are you sure you want to unarchive this record?</p>
                    <input type="hidden" id="unarchive-id" name="id">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="unarchive" class="btn btn-success">Unarchive</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit Class Per Subject</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="class_per_subject.php" method="POST">
                    <input type="hidden" id="edit_id" name="id">
                    
                    <div class="mb-3">
                        <label for="edit_class" class="form-label">Class</label>
                        <select class="form-select" id="edit_class" name="class" required>
                            <?php foreach ($classes as $row) { ?>
                                <option value="<?php echo $row['class_id']; ?>"><?php echo $row['class_name']; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_school_year" class="form-label">School Year/Quarter</label>
                        <select class="form-select" id="edit_school_year" name="school_year" required>
                            <?php foreach ($school_years as $row) { ?>
                                <option value="<?php echo $row['school_year_id']; ?>"><?php echo $row['school_year_quarter']; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_subject" class="form-label">Subject</label>
                        <select class="form-select" id="edit_subject" name="subject" required>
                            <?php foreach ($subjects as $row) { ?>
                                <option value="<?php echo $row['subject_id']; ?>"><?php echo $row['subject_name']; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_teacher" class="form-label">Teacher</label>
                        <select class="form-select" id="edit_teacher" name="teacher" required>
                            <?php foreach ($teachers as $row) { ?>
                                <option value="<?php echo $row['teacher_id']; ?>"><?php echo $row['teacher_name']; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    
                    <button type="submit" name="update" class="btn btn-primary">Save Changes</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.min.js"></script>
<script>
   // Edit Modal
$('#editModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget); // Button that triggered the modal
    var class_id = button.data('class');
    var school_year_id = button.data('school-year');
    var subject_id = button.data('subject');
    var teacher_id = button.data('teacher');
    var id = button.closest('tr').data('id');

    var modal = $(this);
    modal.find('#edit_class').val(class_id);
    modal.find('#edit_school_year').val(school_year_id);
    modal.find('#edit_subject').val(subject_id);
    modal.find('#edit_teacher').val(teacher_id);
    modal.find('#edit_id').val(id);
});

document.querySelectorAll('.archive-btn').forEach(button => {
    button.addEventListener('click', function () {
        document.getElementById('archive-id').value = this.dataset.id;
    });
});

document.querySelectorAll('.unarchive-btn').forEach(button => {
    button.addEventListener('click', function () {
        document.getElementById('unarchive-id').value = this.dataset.id;
    });
});




</script>

</body>
</html>
