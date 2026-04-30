<?php
session_start();
include '../DB/db_connect.php'; 

if (!isset($_SESSION['Role']) || $_SESSION['Role'] !== 'admin') {
    header("Location: ../login.html");
    exit();
}

if (isset($_GET['id'])) {
    $course_id = intval($_GET['id']);
    $sql = "DELETE FROM courses WHERE CourseID = $course_id";
    
    if ($conn->query($sql) === TRUE) {
        header("Location: ManageCourses.php?status=deleted");
        exit();
    }
}
header("Location: ManageCourses.php");
exit();