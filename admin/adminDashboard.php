<?php
session_start();
include '../DB/db_connect.php'; 

// SECURITY LOCK: Admin Only
if (!isset($_SESSION['Role']) || $_SESSION['Role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Helpy</title>
    <link rel="stylesheet" href="../style.css" />
</head>
<body>

<div class="admin-container">
    <div class="sidebar">
        <h2>Helpy Admin</h2>
        <a href="adminDashboard.php" style="background-color: #1a2a35; border-left: 4px solid #4a90e2;">Dashboard</a>
        <a href="manageUser.php">Manage Users</a>
        <a href="ManageCourses.php">Manage Courses</a>
        <a href="Reports.php">Reports</a>
        <a href="Settings.php">Settings</a>
        <a href="../logout.php" style="color: #ff6b6b; margin-top: 50px;">Logout</a>
    </div>

    <div class="main-content">
        <div class="welcome-card">
            <h1>Welcome back, <?php echo htmlspecialchars($_SESSION['Name']); ?>!</h1>
            <p>Select an option from the sidebar to manage the Helpy platform.</p>
        </div>
    </div>
</div>

</body>
</html>