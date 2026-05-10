<?php
session_start();
require_once '../DB/db_connect.php';

if (isset($_SESSION['Role']) && $_SESSION['Role'] === 'teacher' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $teacher = $_SESSION['Name'];

    // Verify ownership before deleting
    $stmt = $conn->prepare("DELETE FROM courses WHERE CourseID = ? AND Instructor = ?");
    $stmt->bind_param("is", $id, $teacher);
    
    if ($stmt->execute()) {
        header("Location: courses.php?success=deleted");
    } else {
        header("Location: courses.php?error=failed");
    }
    exit();
}
header("Location: courses.php");
?>