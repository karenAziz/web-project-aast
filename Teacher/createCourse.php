<?php
session_start();
require_once '../DB/db_connect.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $conn->real_escape_string($_POST['title']);
    $instructor = $_SESSION['Name'];
    $conn->query("INSERT INTO courses (Title, Instructor) VALUES ('$title', '$instructor')");
    echo "Course Added!";
}
?>
<form method="POST">
    <input type="text" name="title" placeholder="Course Title" required>
    <button type="submit">Create</button>
</form>