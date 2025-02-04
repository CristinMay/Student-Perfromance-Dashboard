<?php
session_start(); // Start session to use session variables

include 'config.php'; // Database connection

// Handle the creation of a new teacher
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['teacher_id'])) {
    $teacher_id = $_POST['teacher_id'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $middle_name = $_POST['middle_name'];
    $email = $_POST['email'];

    if (isset($_POST['create_teacher'])) {
        $sql = "INSERT INTO teachers (teacher_id, first_name, middle_name, last_name, email) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssss", $teacher_id, $first_name, $middle_name, $last_name, $email);

        if ($stmt->execute()) {
            $_SESSION['message'] = "Teacher added successfully.";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error adding teacher: " . $stmt->error;
            $_SESSION['message_type'] = "danger";
        }
        header("Location: Teacher.php");
        exit();
    }

    // Handle the update of an existing teacher
    if (isset($_POST['update_teacher'])) {
       $sql = "UPDATE teachers SET first_name=?, middle_name=?, last_name=?, email=? WHERE teacher_id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssss", $first_name, $middle_name, $last_name, $email, $teacher_id);

        if ($stmt->execute()) {
            $_SESSION['message'] = "Teacher updated successfully.";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error updating teacher: " . $stmt->error;
            $_SESSION['message_type'] = "danger";
        }
        header("Location: Teacher.php");
        exit();
    }
}

// Handle deletion of a teacher
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['teacher_id'])) {
    $teacher_id = $_GET['teacher_id'];
    $sql = "DELETE FROM teachers WHERE teacher_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $teacher_id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Teacher deleted successfully.";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error deleting teacher: " . $stmt->error;
        $_SESSION['message_type'] = "danger";
    }
    header("Location: Teacher.php");
    exit();
}

// Archive Class
if (isset($_GET['archive_teacher'])) {
    $teacher_id = $_GET['archive_teacher'];
    $sql = "UPDATE teachers SET is_archived = 1 WHERE teacher_id = $teacher_id";
    if ($conn->query($sql) === TRUE) {
        $_SESSION['alert'] = ['type' => 'success', 'message' => 'Teacher archived successfully.'];
    } else {
        $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Error archiving teacher: ' . $conn->error];
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Unarchive Class
if (isset($_GET['unarchive_teacher'])) {
    $teacher_id = $_GET['unarchive_teacher'];
    $sql = "UPDATE teachers SET is_archived = 0 WHERE teacher_id = $teacher_id";
    if ($conn->query($sql) === TRUE) {
        $_SESSION['alert'] = ['type' => 'success', 'message' => 'Teacher unarchived successfully.'];
    } else {
        $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Error unarchiving teacher: ' . $conn->error];
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}


// Fetch teachers
$search = $_GET['search'] ?? '';
$archiveFilter = $_GET['archiveFilter'] ?? '';

$sql = "SELECT * FROM teachers WHERE (last_name LIKE ? OR first_name LIKE ?)";
if ($archiveFilter !== '') {
    $sql .= " AND is_archived = ?";
}

$stmt = $conn->prepare($sql);
$searchParam = "%$search%";
if ($archiveFilter !== '') {
    $stmt->bind_param("sss", $searchParam, $searchParam, $archiveFilter);
} else {
    $stmt->bind_param("ss", $searchParam, $searchParam);
}

$stmt->execute();
$teachers = $stmt->get_result();

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Teacher Dashboard</title>
    <link rel="icon" type="image/x-icon" href="image/favicon.ico">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.5.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .search-bar {
            margin-bottom: 20px;
        }
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
        <div class="container pt-5" style="margin-top: 60px">
            <!-- Modal for Creating Teacher -->
            <div class="modal fade" id="createTeacherModal" tabindex="-1" aria-labelledby="createTeacherModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="createTeacherModalLabel">Create New Teacher</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="createTeacherForm" action="" method="POST">
                                <div class="mb-3">
                                    <label for="new_teacher_id" class="form-label">Teacher ID</label>
                                    <input type="text" class="form-control" id="new_teacher_id" name="teacher_id" required>
                                </div>
                                <div class="mb-3">
                                    <label for="last_name" class="form-label">Last Name</label>
                                    <input type="text" class="form-control" id="last_name" name="last_name" required>
                                </div>
                                                            <div class="mb-3">
                                <label for="middle_name" class="form-label">Middle Name</label>
                                <input type="text" class="form-control" id="middle_name" name="middle_name">
                            </div>

                                <div class="mb-3">
                                    <label for="first_name" class="form-label">First Name</label>
                                    <input type="text" class="form-control" id="first_name" name="first_name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                <button type="submit" class="btn btn-primary" name="create_teacher">ADD</button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">BACK</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal for Editing Teacher -->
            <div class="modal fade" id="editTeacherModal" tabindex="-1" aria-labelledby="editTeacherModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editTeacherModalLabel">Edit Teacher</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="editTeacherForm" action="" method="POST">
                                <input type="hidden" id="edit_teacher_id" name="teacher_id">
                                <div class="mb-3">
                                    <label for="edit_last_name" class="form-label">Last Name</label>
                                    <input type="text" class="form-control" id="edit_last_name" name="last_name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_middle_name" class="form-label">Middle Name</label>
                                    <input type="text" class="form-control" id="edit_middle_name" name="middle_name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_first_name" class="form-label">First Name</label>
                                    <input type="text" class="form-control" id="edit_first_name" name="first_name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="edit_email" name="email" required>
                                </div>
                                <button type="submit" class="btn btn-primary" name="update_teacher">UPDATE</button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">BACK</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                   Teacher List
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createTeacherModal">
                                Create Teacher
                            </button>
                        </div>
<div class="col-md-6">
    <div class="input-group">
        <input type="text" id="searchInput" class="form-control" placeholder="Search by name">
        <select id="archiveFilter" class="form-select">
            <option value="">All</option>
            <option value="0">Active</option>
            <option value="1">Archived</option>
        </select>
        <button class="btn btn-primary" id="searchButton">Search</button>
    </div>
</div>

                        
                    </div>

                    <?php if (isset($_SESSION['message'])): ?>
                        <div class="alert alert-<?= $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
                            <?= $_SESSION['message']; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
                    <?php endif; ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
<thead>
    <tr>
        <th scope="col">Teacher ID</th>
        <th scope="col">Last Name</th>
        <th scope="col">First Name</th>
        <th scope="col">Middle Name</th>
        <th scope="col">Email</th>
        <th scope="col">Status</th>
        <th scope="col">Actions</th>
    </tr>
</thead>
<tbody id="teacherTable">
    <?php while ($teacher = $teachers->fetch_assoc()): ?>
    <tr>
        <td><?= $teacher['teacher_id'] ?></td>
        <td><?= $teacher['last_name'] ?></td>
        <td><?= $teacher['first_name'] ?></td>
        <td><?= $teacher['middle_name'] ?></td>
        <td><?= $teacher['email'] ?></td>
        <td><?= $teacher['is_archived'] == 0 ? 'Active' : 'Archived' ?></td>
        <td>
            <button class="btn btn-info btn-sm btn-edit editBtn"
                    data-id="<?= $teacher['teacher_id'] ?>"
                    data-lastname="<?= $teacher['last_name'] ?>"
                    data-firstname="<?= $teacher['first_name'] ?>"
                    data-middlename="<?= $teacher['middle_name'] ?>"
                    data-email="<?= $teacher['email'] ?>"
                    data-bs-toggle="modal"
                    data-bs-target="#editTeacherModal">
                Edit
            </button>

            <?php if ($teacher['is_archived'] == 0) : ?>
            <a href="#" class="btn btn-warning btn-sm archiveBtn" data-id="<?= $teacher['teacher_id']; ?>">Archive</a>
            <?php else : ?>
            <a href="#" class="btn btn-success btn-sm unarchiveBtn" data-id="<?= $teacher['teacher_id']; ?>">Unarchive</a>
            <?php endif; ?>
        </td>
    </tr>
    <?php endwhile; ?>
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
  

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.editBtn').forEach(button => {
                button.addEventListener('click', () => {
                    const id = button.getAttribute('data-id');
                    const lastName = button.getAttribute('data-lastname');
                    const firstName = button.getAttribute('data-firstname');
                    const email = button.getAttribute('data-email');
                    const middleName = button.getAttribute('data-middlename');

                    document.getElementById('edit_teacher_id').value = id;
                    document.getElementById('edit_last_name').value = lastName;
                    document.getElementById('edit_first_name').value = firstName;
                    document.getElementById('edit_email').value = email;
                    document.getElementById('edit_middle_name').value = middleName;
                });
            });

            document.getElementById('searchButton').addEventListener('click', () => {
                const searchQuery = document.getElementById('searchInput').value;
                window.location.href = `?search=${searchQuery}`;
            });
        });
          document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.deleteBtn').forEach(button => {
            button.addEventListener('click', () => {
                const teacherId = button.getAttribute('data-id');
                document.getElementById('delete_teacher_id').value = teacherId;
            });
        });
    });

    $(document).ready(function() {
    // Archive Button
    $('.archiveBtn').click(function(e) {
        e.preventDefault(); // Prevent default link behavior
        var classId = $(this).data('id'); // Get class ID from the button
        var archiveUrl = "?archive_teacher=" + classId; // Generate the archive URL

        // Set the archive URL in the confirmation button
        $('#confirmArchiveButton').attr('href', archiveUrl);

        // Show the modal
        $('#archiveClassModal').modal('show');
    });

    // Unarchive Button
    $('.unarchiveBtn').click(function(e) {
        e.preventDefault(); // Prevent default link behavior
        var classId = $(this).data('id'); // Get class ID from the button
        var unarchiveUrl = "?unarchive_teacher=" + classId; // Generate the unarchive URL

        // Set the unarchive URL in the confirmation button
        $('#confirmUnarchiveButton').attr('href', unarchiveUrl);

        // Show the modal
        $('#unarchiveClassModal').modal('show');
    });
});

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('searchButton').addEventListener('click', () => {
        const searchQuery = document.getElementById('searchInput').value;
        const archiveFilter = document.getElementById('archiveFilter').value;
        window.location.href = `?search=${searchQuery}&archiveFilter=${archiveFilter}`;
    });
});

    </script>
</body>
</html>
