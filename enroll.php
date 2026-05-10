<?php
session_start();
require_once 'DB/db_connect.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['UserID'])) {
    header("Location: login.html?error=not_logged_in");
    exit();
}

if ($_SESSION['Role'] !== 'student') {
    header("Location: index.html?error=unauthorized");
    exit();
}

// Check if course_id is provided
if (!isset($_POST['course_id'])) {
    header("Location: Courses.php?error=no_course");
    exit();
}

$user_id = $_SESSION['UserID'];
$course_id = intval($_POST['course_id']);

// Verify course exists
$course_check = "SELECT CourseID FROM courses WHERE CourseID = $course_id";
$course_result = $conn->query($course_check);

if ($course_result->num_rows === 0) {
    header("Location: Courses.php?error=course_not_found");
    exit();
}

// FIXED: Removed the missing 'EnrollmentID' column
$check_enrollment = "SELECT * FROM enrollments WHERE UserID = $user_id AND CourseID = $course_id";
$enrollment_result = $conn->query($check_enrollment);

if ($enrollment_result->num_rows > 0) {
    header("Location: Courses.php?error=already_enrolled");
    exit();
}

// FIXED: Removed the missing 'Progress' column from the insert statement
$insert_enrollment = "INSERT INTO enrollments (UserID, CourseID) VALUES ($user_id, $course_id)";

if ($conn->query($insert_enrollment) === TRUE) {
    header("Location: Courses.php?success=enrolled&course_id=$course_id");
    exit();
} else {
    header("Location: Courses.php?error=enrollment_failed");
    exit();
}
?>