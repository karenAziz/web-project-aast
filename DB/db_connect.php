<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "aast_web"; // This MUST match the DB containing your users table

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>