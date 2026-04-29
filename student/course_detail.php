<?php
session_start();
require_once '../DB/db_connect.php';

// SECURITY LOCK: Student Only
if (!isset($_SESSION['Role']) || $_SESSION['Role'] !== 'student') {
    header("Location: ../login.html");
    exit();
}

if (!isset($_GET['course_id'])) {
    header("Location: my_courses.php");
    exit();
}

$user_id = $_SESSION['UserID'];
$course_id = intval($_GET['course_id']);

// Verify enrollment
$enrollment_check = "SELECT e.EnrollmentID, e.Progress, e.Status, c.* FROM enrollments e
                    JOIN courses c ON e.CourseID = c.CourseID
                    WHERE e.UserID = $user_id AND c.CourseID = $course_id";
$enrollment_result = $conn->query($enrollment_check);

if ($enrollment_result->num_rows === 0) {
    header("Location: my_courses.php?error=not_enrolled");
    exit();
}

$course = $enrollment_result->fetch_assoc();

// Get lessons
$lessons_query = "SELECT * FROM lessons WHERE CourseID = $course_id ORDER BY LessonOrder ASC";
$lessons_result = $conn->query($lessons_query);
$lessons = [];

if ($lessons_result && $lessons_result->num_rows > 0) {
    while ($row = $lessons_result->fetch_assoc()) {
        $lessons[] = $row;
    }
}

// Get lesson progress
$progress_query = "SELECT * FROM lesson_progress WHERE EnrollmentID = {$course['EnrollmentID']}";
$progress_result = $conn->query($progress_query);
$lesson_progress = [];

if ($progress_result && $progress_result->num_rows > 0) {
    while ($row = $progress_result->fetch_assoc()) {
        $lesson_progress[$row['LessonID']] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($course['CourseName']); ?> - AAST Extra Learning</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .course-header-detail {
            background: linear-gradient(135deg, #010d1c 0%, #1a3a52 100%);
            padding: 40px 20px;
            color: white;
        }

        .course-detail-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 40px;
        }

        .lesson-player {
            background-color: #000;
            aspect-ratio: 16/9;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
            margin-bottom: 30px;
        }

        .lesson-info {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        .lesson-title {
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 15px;
            color: #010d1c;
        }

        .lesson-description {
            color: #6b7280;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .lesson-actions {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
        }

        .btn {
            padding: 12px 24px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background-color: #4a90e2;
            color: white;
        }

        .btn-primary:hover {
            background-color: #357abd;
        }

        .btn-secondary {
            background-color: #f3f4f6;
            color: #010d1c;
            border: 1px solid #d1d5db;
        }

        .btn-secondary:hover {
            background-color: #e5e7eb;
        }

        .sidebar {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            padding: 20px;
            height: fit-content;
            position: sticky;
            top: 100px;
        }

        .sidebar-title {
            font-size: 16px;
            font-weight: 700;
            color: #010d1c;
            margin-bottom: 15px;
        }

        .lessons-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .lesson-item {
            padding: 12px;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            background-color: #f9fafb;
            border-left: 3px solid transparent;
        }

        .lesson-item:hover {
            background-color: #f3f4f6;
        }

        .lesson-item.active {
            background-color: #eff6ff;
            border-left-color: #4a90e2;
        }

        .lesson-item.completed {
            opacity: 0.7;
        }

        .lesson-item-title {
            font-size: 13px;
            font-weight: 600;
            color: #010d1c;
            margin-bottom: 4px;
        }

        .lesson-item-duration {
            font-size: 12px;
            color: #6b7280;
        }

        .lesson-checkbox {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 8px;
        }

        .progress-section {
            margin-top: 30px;
            padding-top: 30px;
            border-top: 1px solid #e5e7eb;
        }

        .progress-bar {
            width: 100%;
            height: 10px;
            background-color: #e5e7eb;
            border-radius: 5px;
            overflow: hidden;
            margin: 10px 0;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #4a90e2 0%, #357abd 100%);
            transition: width 0.3s ease;
        }

        .progress-text {
            font-size: 13px;
            color: #6b7280;
            text-align: center;
        }

        @media (max-width: 1024px) {
            .course-detail-container {
                grid-template-columns: 1fr;
            }

            .sidebar {
                position: static;
            }
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
                <li><a href="../index.html" class="nav-link">Home</a></li>
                <li><a href="../Courses.php" class="nav-link">Browse Courses</a></li>
                <li><a href="my_courses.php" class="nav-link">My Courses</a></li>
                <li><a href="sdashboard.php" class="nav-link">Dashboard</a></li>
                <li><a href="../logout.php" class="nav-link btn-login">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="course-header-detail">
        <div style="max-width: 1200px; margin: 0 auto;">
            <a href="my_courses.php" style="color: #4a90e2; text-decoration: none; font-size: 14px;">← Back to My Courses</a>
            <h1 style="margin-top: 10px;"><?php echo htmlspecialchars($course['CourseName']); ?></h1>
            <p style="color: #d1d5db; margin-top: 5px;">by <?php echo htmlspecialchars($course['Instructor']); ?></p>
        </div>
    </div>

    <main class="course-detail-container">
        <div>
            <div class="lesson-player">
                🎥 Video Player - Video would display here
            </div>

            <div class="lesson-info">
                <h2 class="lesson-title">Introduction to <?php echo htmlspecialchars($course['CourseName']); ?></h2>
                <p class="lesson-description">
                    Start your learning journey with this comprehensive introduction to the course. Learn the fundamental concepts and objectives.
                </p>

                <div class="lesson-actions">
                    <button class="btn btn-primary">Mark as Complete</button>
                    <button class="btn btn-secondary">Download Resources</button>
                </div>

                <div class="lesson-checkbox">
                    <input type="checkbox" id="completedCheck">
                    <label for="completedCheck">Mark this lesson as completed</label>
                </div>
            </div>
        </div>

        <div class="sidebar">
            <div class="sidebar-title">📚 Course Content</div>
            <div class="lessons-list">
                <?php if (!empty($lessons)): ?>
                    <?php foreach ($lessons as $lesson): 
                        $is_completed = isset($lesson_progress[$lesson['LessonID']]) && $lesson_progress[$lesson['LessonID']]['Completed'];
                        $completed_class = $is_completed ? 'completed' : '';
                    ?>
                        <div class="lesson-item <?php echo $completed_class; ?>">
                            <div class="lesson-item-title">
                                <?php if ($is_completed): ?>
                                    ✅
                                <?php else: ?>
                                    ▶
                                <?php endif; ?>
                                <?php echo htmlspecialchars($lesson['LessonTitle']); ?>
                            </div>
                            <div class="lesson-item-duration">
                                <?php echo $lesson['Duration']; ?> min
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="font-size: 13px; color: #6b7280;">No lessons yet</p>
                <?php endif; ?>
            </div>

            <div class="progress-section">
                <div class="sidebar-title">Course Progress</div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?php echo $course['Progress']; ?>%;"></div>
                </div>
                <div class="progress-text"><?php echo $course['Progress']; ?>% Complete</div>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="footer-content">
            <p>&copy; 2026 AASTMT Success Hub. All rights reserved.</p>
            <div class="footer-links">
                <a href="#">Privacy Policy</a>
                <a href="#">Terms of Service</a>
                <a href="#">Contact Us</a>
            </div>
        </div>
    </footer>
</body>
</html>
