<?php
include 'config.php';
session_start();

// Fetch Archived Classes
$sql = "SELECT * FROM classes WHERE is_archived = 1";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archived Classes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <div class="container mt-4">
        <h1>Archived Classes</h1>
        <?php if (isset($_SESSION['alert'])): ?>
            <div class="alert alert-<?php echo $_SESSION['alert']['type']; ?> alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['alert']['message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['alert']); // Clear the alert after displaying it ?>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th scope="col">Class</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0) : ?>
                        <?php while ($row = $result->fetch_assoc()) : ?>
                            <tr>
                                <td><?php echo $row['class_name']; ?></td>
                                <td>
                                    <a href="?unarchive_class=<?php echo $row['class_id']; ?>" class="btn btn-success btn-sm">Unarchive</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="2">No archived classes found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>

</html>

<?php
// Handle unarchive
if (isset($_GET['unarchive_class'])) {
    $class_id = $_GET['unarchive_class'];
    $sql = "UPDATE classes SET is_archived = 0 WHERE class_id = $class_id";
    if ($conn->query($sql) === TRUE) {
        $_SESSION['alert'] = ['type' => 'success', 'message' => 'Class unarchived successfully.'];
    } else {
        $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Error unarchiving class: ' . $conn->error];
    }
    header("Location: archive_classes.php");
    exit;
}
?>

