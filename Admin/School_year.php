<?php
include 'config.php';

session_start();

// Handle Add Request
// Handle Add Request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_school_year'])) {
    $start_date = $_POST['start_date']; // Admin input for start date
    $end_date = $_POST['end_date']; // Admin input for end date

    // Generate school year (e.g., 2024-2025)
    $start_year = date("Y", strtotime($start_date));
    $end_year = date("Y", strtotime($end_date));
    $school_year = $start_year . '-' . $end_year;

    // Assign quarters automatically (Q1, Q2, Q3, Q4)
    $quarters = ['Q1', 'Q2', 'Q3', 'Q4'];
    foreach ($quarters as $quarter) {
        $sql = "INSERT INTO school_years (school_year, quarter, start_date, end_date) 
                VALUES ('$school_year', '$quarter', '$start_date', '$end_date')";
        if ($conn->query($sql) === TRUE) {
            $_SESSION['alert'] = ['type' => 'success', 'message' => 'School year and quarters added successfully.'];
        } else {
            $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Error adding school year and quarters: ' . $conn->error];
        }
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Handle Edit Request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_school_year'])) {
    $school_year_id = $_POST['school_year_id'];
    $school_year = $_POST['school_year'];
    $quarter = $_POST['quarter'];

    $sql = "UPDATE school_years SET school_year = '$school_year', quarter = '$quarter' WHERE school_year_id = $school_year_id";
    if ($conn->query($sql) === TRUE) {
        $_SESSION['alert'] = ['type' => 'success', 'message' => 'Class updated successfully.'];
    } else {
        $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Error updating class: ' . $conn->error];
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Handle Delete Request
if (isset($_GET['delete_school_year'])) {
    $school_year_id = $_GET['delete_school_year'];
    $sql = "DELETE FROM school_years WHERE school_year_id = $school_year_id";
    if ($conn->query($sql) === TRUE) {
        $_SESSION['alert'] = ['type' => 'success', 'message' => 'Class deleted successfully.'];
    } else {
        $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Error deleting class: ' . $conn->error];
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

if (isset($_GET['archive_school_year'])) {
    $school_year_id = $_GET['archive_school_year'];
    $sql = "UPDATE school_years SET is_archived = 1 WHERE school_year_id = $school_year_id";
    if ($conn->query($sql) === TRUE) {
        $_SESSION['alert'] = ['type' => 'success', 'message' => 'School year archived successfully.'];
    } else {
        $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Error archiving school year: ' . $conn->error];
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
if (isset($_GET['unarchive_school_year'])) {
    $school_year_id = $_GET['unarchive_school_year'];
    $sql = "UPDATE school_years SET is_archived = 0 WHERE school_year_id = $school_year_id";
    if ($conn->query($sql) === TRUE) {
        $_SESSION['alert'] = ['type' => 'success', 'message' => 'School year unarchived successfully.'];
    } else {
        $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Error unarchiving school year: ' . $conn->error];
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}


$filter_school_year = '';
$filter_quarter = '';
$status_filter = '';  // Filter for active/inactive school years

if (isset($_POST['school_year_filter'])) {
    $filter_school_year = $_POST['school_year_filter'];
}
if (isset($_POST['quarter_filter'])) {
    $filter_quarter = $_POST['quarter_filter'];
}
if (isset($_POST['status_filter'])) {
    $status_filter = $_POST['status_filter'];  // Active or Inactive
}

// Fetch School Years with optional filtering
$sql = "SELECT * FROM school_years WHERE 1=1";

if (!empty($filter_school_year)) {
    $sql .= " AND school_year LIKE '%$filter_school_year%'";
}
if (!empty($filter_quarter)) {
    $sql .= " AND quarter LIKE '%$filter_quarter%'";
}
if ($status_filter !== '') {
    $sql .= " AND is_archived = " . (int)$status_filter;  // 0 for active, 1 for inactive
}

$result = $conn->query($sql);



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage School Year & Quarter</title>
    <link rel="icon" type="image/x-icon" href="image/favicon.ico">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.5.0/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

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
<body style="padding-top: 80px;">
    <header>
        <?php include "inc/Navbar.php"; ?>
    </header>

    <main class="pt-2" style="margin-top: -90px;">
        <div class="container mt-5">
            <div class="row">
                <div class="col">
                    <!-- Card for School Year & Quarter Management -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>Manage School Year & Quarter</h5>
                        </div>
                        <div class="card-body">
                            <p class="card-text">You can add, edit, or delete the school year and quarter from the database.</p>

                         <!-- Add New School Year & Quarter Form -->
                        <form method="POST" action="" class="mt-4">
                            <input type="hidden" name="add_school_year" value="1">
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="startDate" class="form-label">Start Date</label>
                                </div>
                                <div class="col-md-8">
                                    <input type="date" class="form-control" name="start_date" id="startDate" required>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="endDate" class="form-label">End Date</label>
                                </div>
                                <div class="col-md-8">
                                    <input type="date" class="form-control" name="end_date" id="endDate" required>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button type="reset" class="btn btn-secondary me-2">Clear</button>
                                <button type="submit" class="btn btn-primary">Add</button>
                            </div>
                        </form>


                    <!-- Card for Filter Form -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>Filter School Year & Quarter</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="" class="mt-4">
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label for="school_year_filter" class="form-label">School Year</label>
                                    </div>
                                    <div class="col-md-8">
                                        <select name="school_year_filter" id="school_year_filter" class="form-select">
                                            <option value="">Select School Year</option>
                                            <?php
                                            // Fetch school years from the database to populate the dropdown
                                            $year_sql = "SELECT DISTINCT school_year FROM school_years ORDER BY school_year";
                                            $year_result = $conn->query($year_sql);
                                            while ($row = $year_result->fetch_assoc()) {
                                                echo "<option value='" . $row['school_year'] . "'>" . $row['school_year'] . "</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label for="quarter_filter" class="form-label">Quarter</label>
                                    </div>
                                    <div class="col-md-8">
                                        <select name="quarter_filter" id="quarter_filter" class="form-select">
                                            <option value="">Select Quarter</option>
                                            <?php
                                            // Fetch distinct quarters from the database to populate the dropdown
                                            $quarter_sql = "SELECT DISTINCT quarter FROM school_years ORDER BY quarter";
                                            $quarter_result = $conn->query($quarter_sql);
                                            while ($row = $quarter_result->fetch_assoc()) {
                                                echo "<option value='" . $row['quarter'] . "'>" . $row['quarter'] . "</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label for="status_filter" class="form-label">Status</label>
                                    </div>
                                    <div class="col-md-8">
                                        <select name="status_filter" id="status_filter" class="form-select">
                                            <option value="">Select Status</option>
                                            <option value="0" <?php echo ($status_filter === '0') ? 'selected' : ''; ?>>Active</option>
                                            <option value="1" <?php echo ($status_filter === '1') ? 'selected' : ''; ?>>Inactive</option>
                                        </select>
                                    </div>
                                </div>


                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary">Apply Filter</button>
                                </div>
                            </form>
                        </div>
                    </div>
                     <?php if (isset($_SESSION['alert'])): ?>
                        <div class="alert alert-<?php echo $_SESSION['alert']['type']; ?> alert-dismissible fade show" role="alert">
                            <?php echo $_SESSION['alert']['message']; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['alert']); // Remove alert after displaying ?>
                    <?php endif; ?>

                    <!-- Card for Displaying Existing Records -->
                    <div class="card">
                        <div class="card-header">
                            <h5>Existing Records</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive mt-4">
                                <table class="table table-striped">
                                    <thead>
    <tr>
        <th scope="col">Academic Year / Quarter</th>
        <th scope="col">Start Date</th>
        <th scope="col">End Date</th>
        <th scope="col">Status</th>
        <th scope="col">Action</th>
    </tr>
</thead>
<tbody>
<?php if ($result->num_rows > 0) : ?>
    <?php while ($row = $result->fetch_assoc()) : ?>
        <tr>
            <td><?php echo $row['school_year'] . ' - ' . $row['quarter']; ?></td>
            <td><?php echo date("F j, Y", strtotime($row['start_date'])); ?></td> <!-- Format start date -->
            <td><?php echo date("F j, Y", strtotime($row['end_date'])); ?></td> <!-- Format end date -->
            <td><?php echo ($row['is_archived'] == 0) ? 'Active' : 'Archived'; ?></td>
            <td>
                <button class="btn btn-info btn-sm editBtn" 
                        data-id="<?php echo $row['school_year_id']; ?>" 
                        data-schoolyear="<?php echo $row['school_year']; ?>"
                        data-quarter="<?php echo $row['quarter']; ?>">Edit</button>
                
                <?php if ($row['is_archived'] == 0) : ?>
                    <a href="#" class="btn btn-warning btn-sm archiveBtn" 
                    data-id="<?php echo $row['school_year_id']; ?>">Archive</a>
                <?php else : ?>
                    <a href="#" class="btn btn-success btn-sm unarchiveBtn" 
                    data-id="<?php echo $row['school_year_id']; ?>">Unarchive</a>
                <?php endif; ?>
            </td>
        </tr>
    <?php endwhile; ?>
<?php else : ?>
    <tr>
        <td colspan="5">No school years found.</td>
    </tr>
<?php endif; ?>
</tbody>

                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit School Year & Quarter</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="" id="editForm">
                        <input type="hidden" name="edit_school_year" value="1">
                        <input type="hidden" id="editId" name="school_year_id">
                        <div class="mb-3">
                            <label for="editSchoolYear" class="form-label">School Year</label>
                            <input type="text" class="form-control" id="editSchoolYear" name="school_year" required>
                        </div>
                        <div class="mb-3">
                            <label for="editQuarter" class="form-label">Quarter</label>
                            <input type="text" class="form-control" id="editQuarter" name="quarter" required>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary ms-2">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
             <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this school year and quarter? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a id="deleteLink" href="" class="btn btn-danger">Delete</a>
            </div>
        </div>
    </div>
</div>

<!-- Archive Confirmation Modal -->
<div class="modal fade" id="archiveSchoolYearModal" tabindex="-1" aria-labelledby="archiveSchoolYearModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="archiveSchoolYearModalLabel">Confirm Archive</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to archive this school year?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a id="confirmArchiveButton" href="" class="btn btn-warning">Archive</a>
            </div>
        </div>
    </div>
</div>

<!-- Unarchive Confirmation Modal -->
<div class="modal fade" id="unarchiveSchoolYearModal" tabindex="-1" aria-labelledby="unarchiveSchoolYearModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="unarchiveSchoolYearModalLabel">Confirm Unarchive</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to unarchive this school year?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a id="confirmUnarchiveButton" href="" class="btn btn-success">Unarchive</a>
            </div>
        </div>
    </div>
</div>



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.editBtn').click(function() {
                var schoolYearId = $(this).data('id');
                var schoolYear = $(this).data('schoolyear');
                var quarter = $(this).data('quarter');

                $('#editId').val(schoolYearId);
                $('#editSchoolYear').val(schoolYear);
                $('#editQuarter').val(quarter);

                $('#editModal').modal('show');
            });
        });
           $(document).ready(function() {
        // Edit Button click handler
        $('.editBtn').click(function() {
            var schoolYearId = $(this).data('id');
            var schoolYear = $(this).data('schoolyear');
            var quarter = $(this).data('quarter');

            $('#editId').val(schoolYearId);
            $('#editSchoolYear').val(schoolYear);
            $('#editQuarter').val(quarter);

            $('#editModal').modal('show');
        });

   $('.archiveBtn').click(function(e) {
        e.preventDefault();
        var schoolYearId = $(this).data('id');
        $('#confirmArchiveButton').attr('href', '?archive_school_year=' + schoolYearId);
        $('#archiveSchoolYearModal').modal('show');
    });

    // Unarchive Button click handler
    $('.unarchiveBtn').click(function(e) {
        e.preventDefault();
        var schoolYearId = $(this).data('id');
        $('#confirmUnarchiveButton').attr('href', '?unarchive_school_year=' + schoolYearId);
        $('#unarchiveSchoolYearModal').modal('show');
    });
});


    </script>
</body>
</html>
