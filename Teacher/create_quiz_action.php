<?php
session_start();
require_once '../DB/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['Role']) && $_SESSION['Role'] === 'teacher') {
    $course_id = intval($_POST['course_id']);
    $title = $conn->real_escape_string($_POST['quiz_title']);

    $stmt = $conn->prepare("INSERT INTO quizzes (CourseID, Title) VALUES (?, ?)");
    $stmt->bind_param("is", $course_id, $title);
    
    if ($stmt->execute()) {
        $new_quiz_id = $conn->insert_id;
        // Redirect directly to the question builder
        header("Location: add_questions.php?quiz_id=" . $new_quiz_id);
        exit();
    } else {
        die("Error creating quiz: " . $conn->error);
    }
}
header("Location: manage_quizzes.php");
?>