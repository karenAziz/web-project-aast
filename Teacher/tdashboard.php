<?php
session_start();
require_once '../DB/db_connect.php'; 

if (!isset($_SESSION['Role']) || $_SESSION['Role'] !== 'teacher') {
    header("Location: ../login.html");
    exit();
}

$teacher_name = $_SESSION['Name'];
$courses_res = $conn->query("SELECT * FROM courses WHERE Instructor = '$teacher_name'");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Teacher Dashboard</title>
    <link rel="stylesheet" href="tDashboard.css">
</head>
<body>
    
    <aside class="sidebar">
        <h2 style="padding: 20px; color: #4a90e2; font-size: 1.4rem;">Teacher Portal</h2>
        <a href="tdashboard.php" class="active">Dashboard</a>
        <a href="createCourse.php">Create Course</a>
        <a href="/web-project-aast/Courses.php">Browse Courses</a>
        <a href="/web-project-aast/Teacher/upload_lessons.php">Upload Lessons</a>
        <a href="manage_quizzes.php" style="border-left: 3px solid #f59e0b; color: white;">📝 Manage Quizzes</a>
        <a href="../logout.php" class="logout" style="margin-top: auto; color: #ff6b6b; padding: 20px;">Logout</a>
    </aside>

    <main class="content">
        <header class="page-header">
            <h1>Welcome back, Professor <?php echo htmlspecialchars($teacher_name); ?>!</h1>
        </header>
        <section class="table-container">
            <table>
                <thead>
                    <tr>
                        <th style="text-align: left;">COURSE NAME  </th>
                        <th style="text-align: right;">  STATUS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $courses_res->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['Title']); ?></td>
                        <td style="text-align: right;"><span class="status-badge live" style="background: #dcfce7; color: #166534; padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 600;">Live</span></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>
    </main>
</body>
</html>