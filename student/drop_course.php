<?php
session_start();
require_once '../DB/db_connect.php'; // Ensures access to your aast_web database

// SECURITY LOCK: Student Only
if (!isset($_SESSION['Role']) || $_SESSION['Role'] !== 'student') {
    header("Location: ../login.html");
    exit();
}

// Ensure the course_id was sent via POST
if (!isset($_POST['course_id'])) {
    header("Location: my_courses.php");
    exit();
}

$user_id = $_SESSION['UserID'];
$course_id = intval($_POST['course_id']);

// 1. Verify the enrollment exists for this specific user
$check_stmt = $conn->prepare("SELECT * FROM enrollments WHERE UserID = ? AND CourseID = ?");
$check_stmt->bind_param("ii", $user_id, $course_id);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: my_courses.php?error=not_found");
    exit();
}

// 2. Delete the specific enrollment record
$delete_stmt = $conn->prepare("DELETE FROM enrollments WHERE UserID = ? AND CourseID = ?");
$delete_stmt->bind_param("ii", $user_id, $course_id);

if ($delete_stmt->execute()) {
    // Successfully dropped the course
    header("Location: my_courses.php?success=dropped");
    exit();
} else {
    // Database error during deletion
    header("Location: my_courses.php?error=drop_failed");
    exit();
}
?>