<?php
session_start();
require_once '../DB/db_connect.php'; // Adjust path if necessary

// Ensure the user is an admin
if (!isset($_SESSION['Role']) || $_SESSION['Role'] !== 'admin') {
    header("Location: ../login.html");
    exit();
}

if (isset($_GET['id'])) {
    $course_id = intval($_GET['id']);

    // 1. Delete associated payments first to prevent foreign key errors
    $stmt_payments = $conn->prepare("DELETE FROM payments WHERE CourseID = ?");
    $stmt_payments->bind_param("i", $course_id);
    $stmt_payments->execute();

    // 2. Delete associated enrollments
    $stmt_enrollments = $conn->prepare("DELETE FROM enrollments WHERE CourseID = ?");
    $stmt_enrollments->bind_param("i", $course_id);
    $stmt_enrollments->execute();

    // 3. Delete associated lessons
    $stmt_lessons = $conn->prepare("DELETE FROM lessons WHERE CourseID = ?");
    $stmt_lessons->bind_param("i", $course_id);
    $stmt_lessons->execute();

    // 4. Delete associated quizzes
    $stmt_quizzes = $conn->prepare("DELETE FROM quizzes WHERE CourseID = ?");
    $stmt_quizzes->bind_param("i", $course_id);
    $stmt_quizzes->execute();

    // 5. FINALLY, delete the parent Course
    $stmt_course = $conn->prepare("DELETE FROM courses WHERE CourseID = ?");
    $stmt_course->bind_param("i", $course_id);
    
    if ($stmt_course->execute()) {
        // Success! Redirect back to the admin dashboard using the exact case-sensitive path
        header("Location: adminDashboard.php?status=deleted");
        exit();
    } else {
        echo "Error deleting course: " . $conn->error;
    }
} else {
    // Exact case-sensitive path
    header("Location: adminDashboard.php");
    exit();
}
?>