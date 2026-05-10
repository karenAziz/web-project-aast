<?php
session_start();
require_once '../DB/db_connect.php';

// Security Lock
if (!isset($_SESSION['Role']) || $_SESSION['Role'] !== 'student') {
    header("Location: ../login.html");
    exit();
}

$user_id = $_SESSION['UserID'];
$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;
$lesson_id = isset($_GET['lesson_id']) ? intval($_GET['lesson_id']) : 0;

// Verify Enrollment
$enroll_check = "SELECT c.* FROM enrollments e 
                JOIN courses c ON e.CourseID = c.CourseID 
                WHERE e.UserID = $user_id AND c.CourseID = $course_id";
$enroll_res = $conn->query($enroll_check);

if ($enroll_res->num_rows === 0) {
    header("Location: my_courses.php");
    exit();
}
$course = $enroll_res->fetch_assoc();

// Fetch Lessons for Sidebar
$lessons_query = "SELECT * FROM lessons WHERE CourseID = $course_id ORDER BY LessonOrder ASC";
$lessons_res = $conn->query($lessons_query);
$lessons = [];
while ($row = $lessons_res->fetch_assoc()) {
    $lessons[] = $row;
}

// Select Current Lesson
$current_lesson = null;
if ($lesson_id > 0) {
    foreach ($lessons as $l) { if ($l['LessonID'] == $lesson_id) $current_lesson = $l; }
} elseif (!empty($lessons)) {
    $current_lesson = $lessons[0];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($course['Title']); ?> | Success Hub</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        /* Navbar Button Fixes */
        .nav-links { display: flex; align-items: center; gap: 20px; list-style: none; }
        .nav-link { text-decoration: none; color: #4a90e2; font-weight: 500; font-size: 14px; transition: 0.3s; }
        .nav-link:hover { color: #357abd; }
        .btn-logout { color: #ef4444 !important; border: 1px solid #ef4444; padding: 6px 15px; border-radius: 6px; }
        .btn-logout:hover { background: #fef2f2; }

        /* Layout */
        .view-container { display: grid; grid-template-columns: 1fr 320px; gap: 30px; max-width: 1200px; margin: 40px auto; padding: 0 20px; }
        .video-box { background: #000; aspect-ratio: 16/9; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.15); }
        
        /* Sidebar Styling Fixes */
        .lesson-sidebar { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); }
        .sidebar-title { font-size: 18px; font-weight: 700; color: #1f2937; margin-bottom: 20px; }
        .lesson-btn { 
            display: block; padding: 12px 15px; text-decoration: none; color: #4b5563; 
            border-radius: 8px; margin-bottom: 8px; transition: 0.3s; font-size: 14px;
            background: #f9fafb; border: 1px solid #f3f4f6;
        }
        .lesson-btn:hover { background: #f3f4f6; border-color: #e5e7eb; }
        .lesson-btn.active { 
            background: #4a90e2; color: white; font-weight: 600; 
            border-color: #4a90e2; box-shadow: 0 4px 12px rgba(74, 144, 226, 0.2); 
        }

        .info-card { background: white; padding: 25px; border-radius: 12px; margin-top: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        @media (max-width: 900px) { .view-container { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">
                <h1 class="logo-text">AASTMT <span style="font-size: 14px; font-weight: 400;">Success Hub</span></h1>
            </div>
            <ul class="nav-links">
                <li><a href="sdashboard.php" class="nav-link">Dashboard</a></li>
                <li><a href="my_courses.php" class="nav-link">My Courses</a></li>
                <li><a href="../logout.php" class="nav-link btn-logout">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="view-container">
        <main>
            <div class="video-box">
                <?php if ($current_lesson && !empty($current_lesson['VideoURL'])): ?>
                    <iframe width="100%" height="100%" src="<?php echo htmlspecialchars($current_lesson['VideoURL']); ?>" frameborder="0" allowfullscreen></iframe>
                <?php else: ?>
                    <div style="height:100%; display:flex; align-items:center; justify-content:center; color:white; font-size:14px;">
                        No video found. Please select a lesson from the sidebar.
                    </div>
                <?php endif; ?>
            </div>

            <div class="info-card">
                <h2 style="color: #111827;"><?php echo htmlspecialchars($current_lesson['Title'] ?? 'Welcome to ' . $course['Title']); ?></h2>
                <p style="margin-top: 15px; color: #6b7280; line-height: 1.6;">
                    <?php echo htmlspecialchars($current_lesson['Description'] ?? 'Select a lesson to see content and details.'); ?>
                </p>
            </div>
        </main>

        <aside class="lesson-sidebar">
            <div class="sidebar-title">Course Content</div>
            <?php if (empty($lessons)): ?>
                <p style="color: #9ca3af; font-size: 13px;">No lessons added yet.</p>
            <?php else: ?>
                <?php foreach ($lessons as $idx => $lesson): ?>
                    <a href="?course_id=<?php echo $course_id; ?>&lesson_id=<?php echo $lesson['LessonID']; ?>" 
                       class="lesson-btn <?php echo ($current_lesson && $current_lesson['LessonID'] == $lesson['LessonID']) ? 'active' : ''; ?>">
                        <?php echo ($idx + 1) . ". " . htmlspecialchars($lesson['Title']); ?>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </aside>
    </div>
</body>
</html>