<?php
session_start();
require_once '../DB/db_connect.php';

// Check if user is logged in as a teacher and request is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['Role']) && $_SESSION['Role'] === 'teacher') {
    
    $course_id = intval($_POST['course_id']);
    $teacher_name = $_SESSION['Name'];

    // Security: Ensure the teacher deleting the course is the one who created it
    $stmt = $conn->prepare("DELETE FROM courses WHERE CourseID = ? AND Instructor = ?");
    $stmt->bind_param("is", $course_id, $teacher_name);
    
    if ($stmt->execute()) {
        header("Location: courses.php?status=deleted");
    } else {
        header("Location: courses.php?error=delete_failed");
    }
    exit();
}

// If accessed directly without POST, send them back
header("Location: courses.php");
exit();
?>