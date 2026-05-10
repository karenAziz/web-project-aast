<?php
session_start();
require_once '../DB/db_connect.php';

if (!isset($_SESSION['Role']) || $_SESSION['Role'] !== 'teacher') {
    header("Location: ../login.html"); exit();
}

$teacher_name = $_SESSION['Name'];

// Fetch courses owned by this teacher
$courses = $conn->query("SELECT CourseID, Title FROM courses WHERE Instructor = '$teacher_name'");

// Fetch existing quizzes
$quizzes = $conn->query("
    SELECT q.QuizID, q.Title as QuizTitle, c.Title as CourseTitle 
    FROM quizzes q 
    JOIN courses c ON q.CourseID = c.CourseID 
    WHERE c.Instructor = '$teacher_name'
    ORDER BY q.QuizID DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Quizzes | Success Hub</title>
    <link rel="stylesheet" href="tDashboard.css">
    <style>
        .card { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 30px; }
        .btn-create { background: #4a90e2; color: white; padding: 12px 20px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; width: 100%; margin-top: 10px; }
        input, select { padding: 12px; width: 100%; border: 1px solid #ddd; border-radius: 6px; margin: 8px 0 20px 0; }
        .table-header th { padding: 15px; border-bottom: 2px solid #f1f5f9; text-align: left; color: #64748b; }
        .table-row td { padding: 15px; border-bottom: 1px solid #f1f5f9; color: #1e293b; }
    </style>
</head>
<body>
    <aside class="sidebar">
        <h2 style="padding: 20px; color: #4a90e2; font-size: 1.4rem;">Teacher Portal</h2>
        <a href="tdashboard.php">Dashboard</a>
        <a href="createCourse.php">Create Course</a>
        <a href="/web-project-aast/Courses.php">Browse Courses</a>
        <a href="/web-project-aast/Teacher/upload_lessons.php">Upload Lessons</a>
        <a href="manage_quizzes.php" class="active" style="border-left: 3px solid #f59e0b;">📝 Manage Quizzes</a>
        <a href="../logout.php" class="logout" style="margin-top: auto; color: #ff6b6b; padding: 20px;">Logout</a>
    </aside>

    <main class="content">
        <div class="page-header">
            <h1>Manage Quizzes</h1>
            <p>Create auto-graded quizzes for your active courses.</p>
        </div>

        <div class="card" style="max-width: 600px;">
            <h3 style="margin-bottom: 15px;">Create a New Quiz</h3>
            <form action="create_quiz_action.php" method="POST">
                <label style="font-weight: 600; color: #475569;">Select Course</label>
                <select name="course_id" required>
                    <option value="">-- Choose a Course --</option>
                    <?php while($c = $courses->fetch_assoc()): ?>
                        <option value="<?php echo $c['CourseID']; ?>"><?php echo htmlspecialchars($c['Title']); ?></option>
                    <?php endwhile; ?>
                </select>

                <label style="font-weight: 600; color: #475569;">Quiz Title</label>
                <input type="text" name="quiz_title" required placeholder="e.g. Midterm Exam, Week 1 Quiz">

                <button type="submit" class="btn-create">+ Start Adding Questions</button>
            </form>
        </div>

        <div class="card">
            <h3 style="margin-bottom: 15px;">Your Active Quizzes</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <tr class="table-header">
                    <th>QUIZ TITLE</th>
                    <th>COURSE</th>
                    <th style="text-align: right;">ACTIONS</th>
                </tr>
                <?php if($quizzes->num_rows > 0): ?>
                    <?php while($q = $quizzes->fetch_assoc()): ?>
                        <tr class="table-row">
                            <td style="font-weight: 600;"><?php echo htmlspecialchars($q['QuizTitle']); ?></td>
                            <td><?php echo htmlspecialchars($q['CourseTitle']); ?></td>
                            <td style="text-align: right;">
                                <a href="add_questions.php?quiz_id=<?php echo $q['QuizID']; ?>" style="color: #4a90e2; font-weight: 600; text-decoration: none; margin-right: 15px;">Add/Edit Questions</a>
                                <a href="view_grades.php?quiz_id=<?php echo $q['QuizID']; ?>" style="color: #10b981; font-weight: 600; text-decoration: none;">View Grades</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="3" style="padding: 30px; text-align: center; color: #94a3b8;">No quizzes created yet.</td></tr>
                <?php endif; ?>
            </table>
        </div>
    </main>
</body>
</html>