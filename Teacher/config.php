<?php

$servername = "sql203.infinityfree.com";
$username = "if0_37662060";
$password = "ETXPiYBxjcw";
$dbname = "if0_37662060_final_final";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>
