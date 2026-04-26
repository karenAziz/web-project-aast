<?php
session_start();
include '../DB/db_connect.php'; 

// SECURITY LOCK: Student Only
if (!isset($_SESSION['Role']) || $_SESSION['Role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Helpy</title>
    <link rel="stylesheet" href="../style.css" />
</head>
<body>

<div class="admin-container">
    <div class="sidebar">
        <h2>Helpy Student</h2>
        <a href="sdashboard.php" style="background-color: #1a2a35; border-left: 4px solid #4a90e2;">Dashboard</a>
        <a href="../Courses.php">Browse Courses</a>
        <a href="my_progress.php">My Progress</a>
        <a href="subscriptions.php">My Subscriptions</a>
        <a href="certificates.php">Certificates</a>
        <a href="profile.php">Profile / Settings</a>
        <a href="../logout.php" style="color: #ff6b6b; margin-top: 50px;">Logout</a>
    </div>

    <div class="main-content">
        <div class="welcome-card">
            <h1>Welcome back, <?php echo htmlspecialchars($_SESSION['Name']); ?>!</h1>
            <p>Ready to learn? Browse new courses or pick up where you left off!</p>
        </div>
    </div>
</div>

</body>
</html>