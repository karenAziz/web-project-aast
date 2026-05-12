<?php
session_start();
require_once '../DB/db_connect.php';

// Security check: Only teachers allowed
if (!isset($_SESSION['Role']) || $_SESSION['Role'] !== 'teacher') {
    header("Location: ../login.html"); exit();
}

$quiz_id = intval($_GET['quiz_id'] ?? 0);
if ($quiz_id === 0) die("Invalid Quiz ID.");

// Fetch the Quiz Title for the header
$quiz_stmt = $conn->query("SELECT Title FROM quizzes WHERE QuizID = $quiz_id");
if ($quiz_stmt->num_rows === 0) die("Quiz not found.");
$quiz_title = $quiz_stmt->fetch_assoc()['Title'];

// Fetch student grades for this specific quiz
// We use a JOIN to get the student's name from the users table based on their UserID
$grades_query = "
    SELECT u.Name, g.Score, g.Total, g.DateTaken 
    FROM quiz_grades g 
    JOIN users u ON g.UserID = u.UserID 
    WHERE g.QuizID = $quiz_id 
    ORDER BY g.Score DESC, g.DateTaken DESC
";
$grades = $conn->query($grades_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Grades | Success Hub</title>
    <link rel="stylesheet" href="tDashboard.css">
    <style>
        .card { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 30px; }
        .table-header th { padding: 15px; border-bottom: 2px solid #f1f5f9; text-align: left; color: #64748b; font-size: 13px; text-transform: uppercase; }
        .table-row td { padding: 15px; border-bottom: 1px solid #f1f5f9; color: #1e293b; }
        .score-badge { padding: 5px 12px; border-radius: 20px; font-weight: 700; font-size: 14px; }
        .score-high { background: #dcfce7; color: #166534; } /* Green for passing */
        .score-low { background: #fee2e2; color: #991b1b; }  /* Red for failing */
    </style>
</head>
<body style="display: flex; background: #f1f4f9;">

    <aside class="sidebar">
        <h2 style="padding: 20px; color: #4a90e2; font-size: 1.4rem;">Teacher Portal</h2>
        <a href="tdashboard.php">Dashboard</a>
        <a href="createCourse.php">Create Course</a>
        <a href="courses.php">My Courses</a>
        <a href="uploadLessons.php">Upload Lessons</a>
        <a href="manage_quizzes.php" class="active" style="border-left: 3px solid #f59e0b;">📝 Manage Quizzes</a>
        <a href="../logout.php" class="logout" style="margin-top: auto; color: #ff6b6b; padding: 20px;">Logout</a>
    </aside>

    <main class="content" style="margin-left: 260px; padding: 40px; width: 100%;">
        <div class="page-header" style="margin-bottom: 30px;">
            <h1>Grades: <?php echo htmlspecialchars($quiz_title); ?></h1>
            <a href="manage_quizzes.php" style="color: #4a90e2; text-decoration: none; font-weight: 600;">← Back to Quizzes</a>
        </div>

        <div class="card">
            <h3 style="margin-bottom: 20px;">Student Performance</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <tr class="table-header">
                    <th>Student Name</th>
                    <th>Score</th>
                    <th>Percentage</th>
                    <th style="text-align: right;">Date Taken</th>
                </tr>
                
                <?php if($grades && $grades->num_rows > 0): ?>
                    <?php while($g = $grades->fetch_assoc()): 
                        // Calculate percentage to color-code the badge
                        $percentage = ($g['Total'] > 0) ? round(($g['Score'] / $g['Total']) * 100) : 0;
                        $badge_class = ($percentage >= 50) ? 'score-high' : 'score-low';
                    ?>
                        <tr class="table-row">
                            <td style="font-weight: 600;"><?php echo htmlspecialchars($g['Name']); ?></td>
                            <td><?php echo $g['Score'] . " / " . $g['Total']; ?></td>
                            <td>
                                <span class="score-badge <?php echo $badge_class; ?>">
                                    <?php echo $percentage; ?>%
                                </span>
                            </td>
                            <td style="text-align: right; color: #64748b; font-size: 14px;">
                                <?php echo date('M d, Y - h:i A', strtotime($g['DateTaken'])); ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="padding: 40px 20px; text-align: center; color: #94a3b8;">
                            No students have taken this quiz yet.
                        </td>
                    </tr>
                <?php endif; ?>
            </table>
        </div>
    </main>
</body>
</html>