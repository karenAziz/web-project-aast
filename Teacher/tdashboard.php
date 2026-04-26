<?php
session_start();
include '../DB/db_connect.php'; 

// SECURITY LOCK: Teacher Only
if (!isset($_SESSION['Role']) || $_SESSION['Role'] !== 'teacher') {
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard - Helpy</title>
    <link rel="stylesheet" href="../style.css" />
</head>
<body>

<div class="admin-container">
    <div class="sidebar">
        <h2>Teacher Portal</h2>
        <a href="tdashboard.php" style="background-color: #1a2a35; border-left: 4px solid #4a90e2;">Dashboard</a>
        <a href="my_courses.php">My Courses</a>
        <a href="create_course.php">Create / Edit Course</a>
        <a href="upload_lessons.php">Upload Lessons</a>
        <a href="manage_quizzes.php">Manage Quizzes</a>
        <a href="student_progress.php">Student Progress</a>
        <a href="profile.php">Profile / Settings</a>
        <a href="../logout.php" style="color: #ff6b6b; margin-top: 50px;">Logout</a>
    </div>

    <div class="main-content">
        <div class="welcome-card">
            <h1>Welcome back, Professor <?php echo htmlspecialchars($_SESSION['Name']); ?>!</h1>
            <p>You can manage your courses and check student progress from the menu on the left.</p>
        </div>
    </div>
</div>

</body>
</html>