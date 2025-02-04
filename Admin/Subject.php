<?php
// Database connection
include 'config.php';
session_start();

// Add Subject
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_subject'])) {
    $subject = $_POST['subject'];

    $sql = "INSERT INTO subjects (subject_name) VALUES ('$subject')";
    if ($conn->query($sql) === TRUE) {
        $_SESSION['alert'] = ['type' => 'success', 'message' => 'Subject created successfully.'];
    } else {
        $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Error creating subject: ' . $conn->error];
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
// Edit Subject
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_subject'])) {
    $subject_id = $_POST['subject_id'];
    $subject_name = $_POST['subject_name'];

    $sql = "UPDATE subjects SET subject_name = '$subject_name' WHERE subject_id = $subject_id";
    if ($conn->query($sql) === TRUE) {
        $_SESSION['alert'] = ['type' => 'success', 'message' => 'Class updated successfully.'];
    } else {
        $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Error updating subject: ' . $conn->error];
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Delete Subject
if (isset($_GET['delete_subject'])) {
    $subject_id = $_GET['delete_subject'];
    $sql = "DELETE FROM subjects WHERE subject_id = $subject_id";
    if ($conn->query($sql) === TRUE) {
        $_SESSION['alert'] = ['type' => 'success', 'message' => 'Subject deleted successfully.'];
    } else {
        $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Error deleting subject: ' . $conn->error];
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
if (isset($_GET['archive_subject'])) {
    $subject_id = $_GET['archive_subject'];
    $sql = "UPDATE subjects SET is_archived = 1 WHERE subject_id = $subject_id";
    if ($conn->query($sql) === TRUE) {
        $_SESSION['alert'] = ['type' => 'success', 'message' => 'Subject archived successfully.'];
    } else {
        $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Error archiving subject: ' . $conn->error];
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Unarchive Class
if (isset($_GET['unarchive_subject'])) {
    $subject_id = $_GET['unarchive_subject'];
    $sql = "UPDATE subjects SET is_archived = 0 WHERE subject_id = $subject_id";
    if ($conn->query($sql) === TRUE) {
        $_SESSION['alert'] = ['type' => 'success', 'message' => 'Class unarchived successfully.'];
    } else {
        $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Error unarchiving subject: ' . $conn->error];
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}



$filter = isset($_POST['filter']) ? $_POST['filter'] : '';
$status_filter = isset($_POST['status_filter']) ? $_POST['status_filter'] : '';

$sql = "SELECT * FROM subjects";
$conditions = [];

// Build conditions only when filters are provided
if ($status_filter !== '') { // Make sure to check exact comparison with ''
    $conditions[] = "is_archived = " . (int)$status_filter; // Casting to integer for safety
}

// Add conditions to SQL query
if (!empty($conditions)) {
    $sql .= " WHERE " . implode(' AND ', $conditions);
}

$result = $conn->query($sql);

// Debugging query for troubleshooting
if (!$result) {
    echo "SQL Error: " . $conn->error;
}
?>

<!-- HTML/PHP below -->
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

    <main class="pt-2">
        <div class="container pt-5">
            <!-- Card for form input -->
            <div class="card mb-4">
                <div class="card-header">
                    Manage Subjects
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <input type="hidden" name="add_subject" value="1">
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="subject" class="form-label">Subject</label>
                            </div>
                            <div class="col-md-9">
                                <input type="text" class="form-control" id="subject" name="subject" required>
                            </div>
                        </div>

                        <button type="reset" class="btn btn-secondary">Clear</button>
                        <button type="submit" class="btn btn-primary">Add</button>
                    </form>
                </div>
            </div>
    <!-- Filter Card -->
    <div class="card mb-4">
        <div class="card-header">
            Filter Classes
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <div class="row mb-3">
                    <label for="status_filter" class="col-md-4 col-form-label">Status</label>
                    <div class="col-md-8">
                        <select class="form-control" name="status_filter" id="status_filter">
                            <option value="">All</option>
                            <option value="0" <?php echo ($status_filter === '0') ? 'selected' : ''; ?>>Active</option>
                            <option value="1" <?php echo ($status_filter === '1') ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">Filter</button>
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

            <!-- Card for table content -->
            <div class="card">
                <div class="card-header">
                    Subject List
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                           </thead>
                           <th scope="col"> Subject </th>
                           <th scope="col"> Status </th>
                           <th scope="col"> Action </th>
                                <tbody>
                                    <?php if ($result->num_rows > 0) : ?>
                                        <?php while ($row = $result->fetch_assoc()) : ?>
                                            <tr>
                                                <td><?php echo $row['subject_name']; ?></td>
                                                <td><?php echo ($row['is_archived'] == 0) ? 'Active' : 'Archived'; ?></td>
                                                <td>
                                                <button class="btn btn-info btn-sm editBtn" data-id="<?php echo $row['subject_id']; ?>" data-name="<?php echo $row['subject_name']; ?>">Edit</button>
                                                    
                                                    <?php if ($row['is_archived'] == 0) : ?>
                                                        <a href="#" class="btn btn-warning btn-sm archiveBtn" 
                                                        data-id="<?php echo $row['subject_id']; ?>">Archive</a>
                                                    <?php else : ?>
                                                        <a href="#" class="btn btn-success btn-sm unarchiveBtn" 
                                                        data-id="<?php echo $row['subject_id']; ?>">Unarchive</a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else : ?>
                                        <tr>
                                            <td colspan="3">No classes found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

     <!-- Archive Confirmation Modal -->
<div class="modal fade" id="archiveClassModal" tabindex="-1" aria-labelledby="archiveClassModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title" id="archiveClassModalLabel">Confirm Archive</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to archive this class? This action can be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="#" id="confirmArchiveButton" class="btn btn-warning">Archive</a>
            </div>
        </div>
    </div>
</div>

<!-- Unarchive Confirmation Modal -->
<div class="modal fade" id="unarchiveClassModal" tabindex="-1" aria-labelledby="unarchiveClassModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="unarchiveClassModalLabel">Confirm Unarchive</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to unarchive this class? It will be marked as active again.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="#" id="confirmUnarchiveButton" class="btn btn-success">Unarchive</a>
            </div>
        </div>
    </div>
</div>

   
    <!-- Edit Subject Modal -->
    <div class="modal fade" id="editSubjectModal" tabindex="-1" aria-labelledby="editSubjectModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editSubjectModalLabel">Edit Subject</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="">
                        <input type="hidden" name="edit_subject" value="1">
                        <input type="hidden" id="editSubjectId" name="subject_id">
                        <div class="mb-3">
                            <label for="editSubjectName" class="form-label">Subject</label>
                            <input type="text" class="form-control" id="editSubjectName" name="subject_name" required>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">Update Subject</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Delete Subject Modal -->
<!-- Delete Subject Modal -->
<div class="modal fade" id="deleteSubjectModal" tabindex="-1" aria-labelledby="deleteSubjectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteSubjectModalLabel">Delete Subject</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this subject?
            </div>
            <div class="modal-footer">
                <form method="GET" action="">
                    <input type="hidden" id="deleteSubjectId" name="delete_subject">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.editBtn').click(function() {
                var subjectId = $(this).data('id');
                var subjectName = $(this).data('name');

                $('#editSubjectId').val(subjectId);
                $('#editSubjectName').val(subjectName);

                $('#editSubjectModal').modal('show');
            });
        });
        $(document).ready(function() {
    $('.deleteBtn').click(function() {
        var subjectId = $(this).data('id');
        $('#deleteSubjectId').val(subjectId); // Pass subject ID to hidden input
        $('#deleteSubjectModal').modal('show'); // Show delete modal
    });
});


$(document).ready(function() {
    // Archive Button
    $('.archiveBtn').click(function(e) {
        e.preventDefault(); // Prevent default link behavior
        var subjectId = $(this).data('id'); // Get class ID from the button
        var archiveUrl = "?archive_subject=" + subjectId; // Generate the archive URL

        // Set the archive URL in the confirmation button
        $('#confirmArchiveButton').attr('href', archiveUrl);

        // Show the modal
        $('#archiveClassModal').modal('show');
    });

    // Unarchive Button
    $('.unarchiveBtn').click(function(e) {
        e.preventDefault(); // Prevent default link behavior
        var subjectId = $(this).data('id'); // Get class ID from the button
        var unarchiveUrl = "?unarchive_subject=" + subjectId; // Generate the unarchive URL

        // Set the unarchive URL in the confirmation button
        $('#confirmUnarchiveButton').attr('href', unarchiveUrl);

        // Show the modal
        $('#unarchiveClassModal').modal('show');
    });
});


    </script>
</body>

</html>
