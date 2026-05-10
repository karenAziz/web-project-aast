<?php
session_start();
require_once '../DB/db_connect.php';

// SECURITY LOCK: Student Only
if (!isset($_SESSION['Role']) || $_SESSION['Role'] !== 'student') {
    header("Location: ../login.html");
    exit();
}

if (!isset($_POST['course_id'])) {
    header("Location: my_courses.php");
    exit();
}

$user_id = $_SESSION['UserID'];
$course_id = intval($_POST['course_id']);

// Check enrollment exists
$check = "SELECT EnrollmentID FROM enrollments WHERE UserID = $user_id AND CourseID = $course_id";
$result = $conn->query($check);

if ($result->num_rows === 0) {
    header("Location: my_courses.php?error=not_found");
    exit();
}

$row = $result->fetch_assoc();
$enrollment_id = $row['EnrollmentID'];

// Delete enrollment (drop course)
$delete = "DELETE FROM enrollments WHERE EnrollmentID = $enrollment_id";

if ($conn->query($delete) === TRUE) {
    header("Location: my_courses.php?success=dropped");
    exit();
} else {
    header("Location: my_courses.php?error=drop_failed");
    exit();
}
?>
