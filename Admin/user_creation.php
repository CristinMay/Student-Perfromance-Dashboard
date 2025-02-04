<?php 
session_start();
include 'config.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// PHPMailer setup
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// Function to send email
function sendEmail($recipientEmail, $username, $password) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';  // Specify your SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = '';  // Your Gmail address
        $mail->Password = '';  // Your Gmail password or app-specific password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('', 'EduPerformance Tracker');
        $mail->addAddress($recipientEmail);

        $mail->isHTML(true);
        $mail->Subject = 'Your Login Credentials';
        $mail->Body = "Hello,<br>Your account has been created.<br><strong>Username:</strong> $username<br><strong>Password:</strong> $password<br>Please log in.";

        $mail->send();
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}

// Handle form submission for creating a new user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['role'], $_POST['user_id'])) {
    $role = $_POST['role'];
    $user_id = $_POST['user_id'];
    
    // Fetch user details
    if ($role == 'teacher') {
        $query = "SELECT email, first_name, last_name FROM teachers WHERE teacher_id = ?";
    } else if ($role == 'student') {
        $query = "SELECT email, first_name, last_name FROM students WHERE student_id = ?";
    }
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if ($user) {
        // Generate random password
        $password = bin2hex(random_bytes(8));  // 16 characters
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Insert into users table
        $insertQuery = "INSERT INTO users (username, password, role, email, teacher_id, student_id, first_name, last_name) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insertQuery);
        $teacher_id = ($role == 'teacher') ? $user_id : NULL;
        $student_id = ($role == 'student') ? $user_id : NULL;
        $first_name = $user['first_name'];
        $last_name = $user['last_name'];
        $stmt->bind_param('ssssiiss', $user_id, $hashedPassword, $role, $user['email'], $teacher_id, $student_id, $first_name, $last_name);
        if ($stmt->execute()) {
            // Send email
            sendEmail($user['email'], $user_id, $password);
            $_SESSION['message'] = 'User created successfully and email sent.';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = 'Failed to create user.';
            $_SESSION['message_type'] = 'danger';
        }
    } else {
        $_SESSION['message'] = 'User not found.';
        $_SESSION['message_type'] = 'warning';
    }
    header("Location: user_creation.php");
    exit();
}

// Handle the AJAX request for user data
if (isset($_POST['role'])) {
    $role = $_POST['role'];
    $query = '';

    if ($role == 'teacher') {
        $query = "SELECT teacher_id AS user_id, first_name, last_name, email FROM teachers";
    } else if ($role == 'student') {
        $query = "SELECT student_id AS user_id, first_name, last_name, email FROM students";
    }

    $result = $conn->query($query);
    $users = [];

    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }

    echo json_encode($users);
    exit();
}

// Handle user deletion
if (isset($_POST['delete_user_id'])) {
    $deleteUserId = $_POST['delete_user_id'];
    
    $deleteQuery = "DELETE FROM users WHERE username = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param('s', $deleteUserId);
    if ($stmt->execute()) {
        $_SESSION['message'] = 'User deleted successfully.';
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = 'Failed to delete user.';
        $_SESSION['message_type'] = 'danger';
    }
    header("Location: user_creation.php");
    exit();
}

// Fetch all users for display in the table
$usersResult = $conn->query("SELECT * FROM users");
$usersList = $usersResult->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - User Creation</title>
    <link rel="icon" type="image/x-icon" href="image/favicon.ico">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.5.0/font/bootstrap-icons.css" rel="stylesheet">    <style>/* Container Modernization */
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
.table-bordered {
    border-radius: 8px;
    overflow: hidden; /* For rounded borders */
    background-color: #ffffff; /* Soft white for table background */
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05); /* Subtle shadow */
}

.table-bordered thead {
    background-color: #81c784; /* Soft green header */
    color: #ffffff; /* White text */
}

.table-bordered tbody tr:hover {
    background-color: #edf5ee; /* Very light green for hover effect */
}

.table-bordered th,
.table-bordered td {
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
    <main class="pt-2" style="margin-top: -40px;">
        <div class="container mt-5">
            <!-- User Creation Form -->
            <div class="row mb-4">
                <div class="col">
                    <div class="card h-100 border">
                        <div class="card-header text-dark">Create User</div>
                        <div class="card-body">
                            <form method="POST" action="" class="mt-4">
                                <div class="row mb-3">
                                    <div class="col-md-4 d-flex align-items-center">
                                        <label for="role" class="form-label mb-0">Role</label>
                                    </div>
                                    <div class="col-md-8">
                                        <select class="form-select" name="role" id="role" required>
                                            <option value="" disabled selected>Select Role</option>
                                            <option value="student">Student</option>
                                            <option value="teacher">Teacher</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-4 d-flex align-items-center">
                                        <label for="user_id" class="form-label mb-0">User</label>
                                    </div>
                                    <div class="col-md-8">
                                        <input type="text" id="user_search" class="form-control" placeholder="Search User" onkeyup="filterUsers()">
                                        <select class="form-select" name="user_id" id="user_id" required>
                                            <option value="" disabled selected>Select User</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-4 d-flex align-items-center">
                                        <label for="email" class="form-label mb-0">Email</label>
                                    </div>
                                    <div class="col-md-8">
                                        <input type="email" class="form-control" name="email" id="email" placeholder="Email" required readonly>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-4 d-flex align-items-center">
                                        <label for="first_name" class="form-label mb-0">First Name</label>
                                    </div>
                                    <div class="col-md-8">
                                        <input type="text" class="form-control" name="first_name" id="first_name" placeholder="First Name" required>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-4 d-flex align-items-center">
                                        <label for="last_name" class="form-label mb-0">Last Name</label>
                                    </div>
                                    <div class="col-md-8">
                                        <input type="text" class="form-control" name="last_name" id="last_name" placeholder="Last Name" required>
                                    </div>
                                </div>
                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary">Create User</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
<?php if (isset($_SESSION['message'])): ?>
    <div class="alert alert-<?= $_SESSION['message_type'] ?> alert-dismissible fade show" role="alert">
        <?= $_SESSION['message'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
<?php endif; ?>

            <!-- Table to Display Created Accounts with Delete Option -->
            <div class="row mb-4">
                <div class="col">
                    <div class="card h-100 border">
                        <div class="card-header text-dark">Created Accounts</div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>Username</th>
                                            <th>Email</th>
                                            <th>Role</th>
                                            <th>First Name</th>
                                            <th>Last Name</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($usersList)) : ?>
                                            <?php foreach ($usersList as $user) : ?>
                                                <tr>
                                                    <td><?= $user['username']; ?></td>
                                                    <td><?= $user['email']; ?></td>
                                                    <td><?= ucfirst($user['role']); ?></td>
                                                    <td><?= $user['first_name']; ?></td>
                                                    <td><?= $user['last_name']; ?></td>
                                                    <td>
                                                             <button 
                                                                class="btn btn-danger btn-sm delete-user" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#deleteUserModal" 
                                                                data-username="<?= $user['username']; ?>">
                                                                Delete
                                                            </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else : ?>
                                            <tr>
                                                <td colspan="6" class="text-center">No accounts created yet.</td>
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

    <!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteUserModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this user?
                <p class="text-danger fw-bold" id="deleteUsername"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteButton">Delete</button>
            </div>
        </div>
    </div>
</div>


    <script>
    // Populate user dropdown based on role
    $('#role').on('change', function() {
        var role = $(this).val();
        if (role) {
            $.post('user_creation.php', {role: role}, function(data) {
                var users = JSON.parse(data);
                var options = '<option value="" disabled selected>Select User</option>';
                users.forEach(function(user) {
                    options += '<option value="' + user.user_id + '" data-email="' + user.email + '" data-first_name="' + user.first_name + '" data-last_name="' + user.last_name + '">' + user.first_name + ' ' + user.last_name + '</option>';
                });
                $('#user_id').html(options);
            });
        }
    });

    // Filter users based on input
    function filterUsers() {
        var input = $('#user_search').val().toLowerCase();
        $('#user_id option').each(function() {
            var text = $(this).text().toLowerCase();
            $(this).toggle(text.indexOf(input) > -1);
        });
    }

    // Populate email, first name, and last name when user is selected
    $('#user_id').on('change', function() {
        var selectedOption = $(this).find('option:selected');
        var email = selectedOption.data('email');
        var firstName = selectedOption.data('first_name');
        var lastName = selectedOption.data('last_name');
        
        $('#email').val(email);
        $('#first_name').val(firstName);
        $('#last_name').val(lastName);
    });

    let userToDelete = null;

$('#deleteUserModal').on('show.bs.modal', function(event) {
    const button = $(event.relatedTarget); // Button that triggered the modal
    userToDelete = button.data('username'); // Extract username from data attribute
    $('#deleteUsername').text(userToDelete); // Update modal with the username
});

// Handle the confirm delete button click
$('#confirmDeleteButton').on('click', function() {
    if (userToDelete) {
        $.post('user_creation.php', { delete_user_id: userToDelete }, function(response) {
            const res = JSON.parse(response);
            if (res.status === 'success') {
                location.reload(); // Refresh the page on successful deletion
            } else {
                alert('Error deleting user.');
            }
        });
        $('#deleteUserModal').modal('hide'); // Close the modal after action
    }
});
</script>

</body>
</html>
