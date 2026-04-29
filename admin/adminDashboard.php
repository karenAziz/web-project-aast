<?php
session_start();
include '../DB/db_connect.php'; 

// SECURITY LOCK: Admin Only
if (!isset($_SESSION['Role']) || $_SESSION['Role'] !== 'admin') {
    // Redirecting to login.html based on our previous setup
    header("Location: ../login.html");
    exit();
}

// Initialize variables for our dashboard statistics
$total_students = 0;
$total_courses = 0;
$total_enrollments = 0;

// Fetch Total Students
$student_query = "SELECT COUNT(*) as count FROM users WHERE Role = 'student'";
if ($result = $conn->query($student_query)) {
    $total_students = $result->fetch_assoc()['count'];
}

// Fetch Total Courses
$course_query = "SELECT COUNT(*) as count FROM courses";
if ($result = $conn->query($course_query)) {
    $total_courses = $result->fetch_assoc()['count'];
}

// Fetch Total Active Enrollments
$enroll_query = "SELECT COUNT(*) as count FROM enrollments WHERE Status = 'Active'";
if ($result = $conn->query($enroll_query)) {
    $total_enrollments = $result->fetch_assoc()['count'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | AASTMT Success Hub</title>
    <link rel="stylesheet" href="../style.css" />
    <link rel="stylesheet" href="admin.css" />
</head>
<body>

<div class="admin-container">
    <nav class="sidebar">
        <h2>AASTMT Admin</h2>
        <a href="adminDashboard.php" class="active">Dashboard</a>
        <a href="manageUser.php">Manage Users</a>
        <a href="ManageCourses.php">Manage Courses</a>
        <a href="Reports.php">Reports</a>
        <a href="Settings.php">Settings</a>
        <a href="../logout.php" class="logout-link">Logout</a>
    </nav>

    <main class="main-content">
        <div class="welcome-card">
            <h1>Welcome back, <?php echo isset($_SESSION['Name']) ? htmlspecialchars($_SESSION['Name']) : 'Admin'; ?>!</h1>
            <p>Here is an overview of the AASTMT Success Hub platform today.</p>
        </div>

        <div class="dashboard-grid">
            <div class="stat-card">
                <h3>Total Students</h3>
                <p class="number"><?php echo $total_students; ?></p>
            </div>
            
            <div class="stat-card">
                <h3>Active Courses</h3>
                <p class="number"><?php echo $total_courses; ?></p>
            </div>
            
            <div class="stat-card">
                <h3>Total Enrollments</h3>
                <p class="number"><?php echo $total_enrollments; ?></p>
            </div>
        </div>
    </main>
</div>

</body>
</html>