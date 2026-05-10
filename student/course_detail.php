<?php
session_start();
require_once '../DB/db_connect.php';

// Security: Ensure only logged-in students can access this page
if (!isset($_SESSION['Role']) || $_SESSION['Role'] !== 'student') {
    header("Location: ../login.html");
    exit();
}

$user_id = $_SESSION['UserID'];
$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;
$lesson_id = isset($_GET['lesson_id']) ? intval($_GET['lesson_id']) : 0;

// 1. Verify Enrollment: Checks if this student is actually signed up for this course
$enroll_check = "SELECT c.* FROM enrollments e 
                JOIN courses c ON e.CourseID = c.CourseID 
                WHERE e.UserID = $user_id AND c.CourseID = $course_id";
$enroll_res = $conn->query($enroll_check);

if ($enroll_res->num_rows === 0) {
    header("Location: my_courses.php?error=not_enrolled");
    exit();
}
$course = $enroll_res->fetch_assoc();

// 2. Fetch All Lessons: Pulls the sidebar list based on your 'lessons' table
$lessons_query = "SELECT * FROM lessons WHERE CourseID = $course_id ORDER BY LessonOrder ASC";
$lessons_res = $conn->query($lessons_query);
$lessons = [];
while ($row = $lessons_res->fetch_assoc()) {
    $lessons[] = $row;
}

// 3. Select Current Lesson: Defaults to the first lesson if the user just arrived
$current_lesson = null;
if ($lesson_id > 0) {
    foreach ($lessons as $l) { 
        if ($l['LessonID'] == $lesson_id) $current_lesson = $l; 
    }
} elseif (!empty($lessons)) {
    $current_lesson = $lessons[0];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($course['Title']); ?> | AASTMT Success Hub</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .view-container { 
            display: grid; 
            grid-template-columns: 1fr 320px; 
            gap: 30px; 
            max-width: 1200px; 
            margin: 40px auto; 
            padding: 0 20px; 
        }
        .video-box { 
            background: #000; 
            aspect-ratio: 16/9; 
            border-radius: 12px; 
            overflow: hidden; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.2); 
        }
        .lesson-sidebar { 
            background: white; 
            padding: 25px; 
            border-radius: 12px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.08); 
            height: fit-content; 
        }
        .lesson-btn { 
            display: block; 
            padding: 12px; 
            text-decoration: none; 
            color: #4b5563; 
            border-radius: 8px; 
            margin-bottom: 8px; 
            transition: 0.3s; 
            font-size: 14px; 
            border-left: 4px solid transparent; 
        }
        .lesson-btn:hover { background: #f3f4f6; }
        .lesson-btn.active { 
            background: #eff6ff; 
            color: #4a90e2; 
            border-left-color: #4a90e2; 
            font-weight: 600; 
        }
        .lesson-info { 
            margin-top: 25px; 
            padding: 25px; 
            background: white; 
            border-radius: 12px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        @media (max-width: 900px) { 
            .view-container { grid-template-columns: 1fr; } 
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">
                <h1 class="logo-text">AASTMT</h1>
                <span class="logo-subtitle">Success Hub</span>
            </div>
            <ul class="nav-links">
                <li><a href="sdashboard.php">Dashboard</a></li>
                <li><a href="my_courses.php" class="nav-link">My Courses</a></li>
                <li><a href="../logout.php" class="btn-logout">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="view-container">
        <main>
            <div class="video-box">
                <?php if ($current_lesson && !empty($current_lesson['VideoURL'])): ?>
                    <iframe width="100%" height="100%" 
                            src="<?php echo htmlspecialchars($current_lesson['VideoURL']); ?>" 
                            frameborder="0" allowfullscreen></iframe>
                <?php else: ?>
                    <div style="height:100%; display:flex; align-items:center; justify-content:center; color:white; text-align:center; padding:20px;">
                        No video content found. Please select a lesson from the sidebar.
                    </div>
                <?php endif; ?>
            </div>

            <div class="lesson-info">
                <h2><?php echo htmlspecialchars($current_lesson['Title'] ?? 'Welcome to ' . $course['Title']); ?></h2>
                <p style="margin-top: 15px; color: #6b7280; line-height: 1.6;">
                    <?php echo htmlspecialchars($current_lesson['Description'] ?? 'Select a lesson to see the details and resources.'); ?>
                </p>
            </div>
        </main>

        <aside class="lesson-sidebar">
            <h3 style="margin-bottom: 20px; color: #1f2937;">Course Content</h3>
            <div class="lesson-list">
                <?php if (empty($lessons)): ?>
                    <p style="color: #9ca3af; font-size: 14px;">No lessons have been added yet.</p>
                <?php else: ?>
                    <?php foreach ($lessons as $idx => $lesson): ?>
                        <a href="?course_id=<?php echo $course_id; ?>&lesson_id=<?php echo $lesson['LessonID']; ?>" 
                           class="lesson-btn <?php echo ($current_lesson && $current_lesson['LessonID'] == $lesson['LessonID']) ? 'active' : ''; ?>">
                            <?php echo ($idx + 1) . ". " . htmlspecialchars($lesson['Title']); ?>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </aside>
    </div>

    <footer style="background: white; border-top: 1px solid #e5e7eb; padding: 24px 20px; margin-top: 60px;">
        <div style="max-width: 1200px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; color: #6b7280; font-size: 14px;">
            <p>&copy; 2026 AASTMT Success Hub. All rights reserved.</p>
            <div style="display: flex; gap: 20px;">
                <a href="#" style="color: inherit; text-decoration: none;">Privacy Policy</a>
                <a href="#" style="color: inherit; text-decoration: none;">Contact Us</a>
            </div>
        </div>
    </footer>
</body>
</html>