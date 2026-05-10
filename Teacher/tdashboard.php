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
    <nav class="sidebar">
        <h2>Teacher Portal</h2>
        <a href="tdashboard.php" class="active">Dashboard</a>
        <a href="create_course.php">Create Course</a>
        <a href="upload_lessons.php">Upload Lessons</a>
        <a href="../logout.php" style="color: #ff7675; margin-top: auto;">Logout</a>
    </nav>
    <main class="content">
        <header class="page-header">
            <h1>Welcome back, Professor <?php echo htmlspecialchars($teacher_name); ?>!</h1>
        </header>
        <section class="table-container">
            <table>
                <thead><tr><th>COURSE NAME</th><th>STATUS</th></tr></thead>
                <tbody>
                    <?php while($row = $courses_res->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['Title']); ?></td>
                        <td><span class="status-badge live">Live</span></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>
    </main>
</body>
</html>