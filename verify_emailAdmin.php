<?php
require 'config.php';  // Include your database connection

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Verify the token
    $stmt = $conn->prepare("SELECT email FROM email_verifications WHERE token = ?");
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $stmt->bind_result($email);
    $stmt->fetch();
    $stmt->close();

    if ($email) {
        // Token is valid, proceed to verify the email
        $stmt = $conn->prepare("UPDATE admins SET is_verified = 1 WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->close();

        // Optionally delete the token from the email_verifications table
        $stmt = $conn->prepare("DELETE FROM email_verifications WHERE token = ?");
        $stmt->bind_param('s', $token);
        $stmt->execute();
        $stmt->close();

        // Redirect to login page
        header('Location: loginAdmin.php');
        exit();
    } else {
        echo 'Invalid verification token.';
    }
} else {
    echo 'No token provided.';
}
?>
