<?php
session_start();
require_once '../DB/db_connect.php'; // Database connection

// SECURITY LOCK: Student Only
if (!isset($_SESSION['Role']) || $_SESSION['Role'] !== 'student') {
    header("Location: ../login.html");
    exit();
}

$user_id = $_SESSION['UserID'];

// Fetch all courses this student is currently enrolled in
// Joining the 'enrollments' table with the 'courses' table
$sql = "SELECT c.CourseID, c.Title, c.Instructor, c.Level, c.Price 
        FROM enrollments e 
        JOIN courses c ON e.CourseID = c.CourseID 
        WHERE e.UserID = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses | Success Hub</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="../Teacher/tDashboard.css"> <style>
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 600; font-size: 14px; }
        .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .alert-error { background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }
        .btn-view { background: #4a90e2; color: white; padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 12px; }
        .btn-drop { background: none; border: none; color: #ff6b6b; cursor: pointer; font-size: 12px; font-weight: 700; text-decoration: underline; }
    </style>
</head>
<body>

    <aside class="sidebar">
        <h2 style="color: white; padding: 20px;">Student Portal</h2>
        <a href="sdashboard.php">Dashboard</a>
        <a href="../Courses.php">Browse Courses</a>
        <a href="my_courses.php" class="active">My Courses</a>
        <a href="../logout.php" style="margin-top: auto; color: #ff6b6b; padding: 20px;">Logout</a>
    </aside>

    <main class="content">
        <div class="page-header">
            <h1>My Enrolled Courses</h1>
            <p>Access your learning materials and track your progress.</p>
        </div>

        <?php if(isset($_GET['success']) && $_GET['success'] == 'dropped'): ?>
            <div class="alert alert-success">Successfully dropped from the course.</div>
        <?php endif; ?>

        <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-error">Operation failed. Please try again later.</div>
        <?php endif; ?>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th style="text-align: left;">COURSE NAME</th>
                        <th>INSTRUCTOR</th>
                        <th>LEVEL</th>
                        <th style="text-align: right;">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <div style="font-weight: 700; color: #1a233a;"><?php echo htmlspecialchars($row['Title']); ?></div>
                                <div style="font-size: 11px; color: #64748b;"><?php echo number_format($row['Price'], 2); ?> L.E.</div>
                            </td>
                            <td><?php echo htmlspecialchars($row['Instructor']); ?></td>
                            <td><span class="status-badge live" style="background: #e0f2fe; color: #0369a1;"><?php echo htmlspecialchars($row['Level']); ?></span></td>
                            <td style="text-align: right;">
                                <div style="display: flex; gap: 15px; justify-content: flex-end; align-items: center;">
                                    <a href="course_detail.php?course_id=<?php echo $row['CourseID']; ?>" class="btn-view">View Lessons</a>
                                    
                                    <form action="drop_course.php" method="POST" onsubmit="return confirm('Are you sure you want to drop this course? No refund will be issued.');">
                                        <input type="hidden" name="course_id" value="<?php echo $row['CourseID']; ?>">
                                        <button type="submit" class="btn-drop">Drop</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 50px; color: #94a3b8;">
                                You are not enrolled in any courses yet.<br>
                                <a href="../Courses.php" style="color: #4a90e2; text-decoration: none; font-weight: 600;">Browse courses here →</a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

</body>
</html>