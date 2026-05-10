<?php
session_start();
include '../DB/db_connect.php'; 

if (!isset($_SESSION['Role']) || $_SESSION['Role'] !== 'admin') {
    header("Location: ../login.html");
    exit();
}

// 1. Top 5 Most Popular Courses
// Fixed: Counting UserID instead of the non-existent EnrollmentID
$popular_courses_query = "
    SELECT c.Title, COUNT(e.UserID) as student_count 
    FROM courses c
    LEFT JOIN enrollments e ON c.CourseID = e.CourseID
    GROUP BY c.CourseID
    ORDER BY student_count DESC
    LIMIT 5";
$popular_result = $conn->query($popular_courses_query);

// 2. User Distribution
$role_dist_query = "SELECT Role, COUNT(*) as count FROM users GROUP BY Role";
$role_result = $conn->query($role_dist_query);

// 3. Recent Enrollments
// Fixed: Removed e.EnrollmentID from the SELECT clause
$recent_enroll_query = "
    SELECT u.Name as student_name, c.Title as course_name
    FROM enrollments e
    JOIN users u ON e.UserID = u.UserID
    JOIN courses c ON e.CourseID = c.CourseID
    LIMIT 10";
$recent_result = $conn->query($recent_enroll_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Platform Reports | AASTMT Admin</title>
    <link rel="stylesheet" href="../style.css" />
    <link rel="stylesheet" href="admin.css" />
</head>
<body>
<div class="admin-container">
 <nav class="sidebar">
        <h2>AASTMT Admin</h2>
        <a href="adminDashboard.php">Dashboard</a>
        <a href="manageUser.php">Manage Users</a>
        <a href="ManageCourses.php">Manage Courses</a>
        <a href="Reports.php" class="active">Reports</a>
        <a href="Settings.php">Settings</a>
        <a href="../logout.php" class="logout-link">Logout</a>
    </nav>

    <main class="main-content">
        <div class="page-header">
            <h1>Platform Reports</h1>
            <p>Real-time analytics for the Helpy platform.</p>
        </div>

        <div class="dashboard-grid" style="margin-top: 20px;">
            <div class="report-card" style="background:white; padding:20px; border-radius:10px;">
                <h3>Popular Courses</h3>
                <table class="admin-table">
                    <?php while($row = $popular_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['Title']); ?></td>
                            <td><strong><?php echo $row['student_count']; ?> Enrolled</strong></td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            </div>
        </div>

        <div class="table-container" style="margin-top: 30px;">
            <h3 style="padding: 15px;">Recent Activity</h3>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Course</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $recent_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['student_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['course_name']); ?></td>
                            <td><strong>Enrolled</strong></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
</body>
</html>