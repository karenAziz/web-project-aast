<?php
session_start();
require_once '../DB/db_connect.php';

// SECURITY LOCK: Student Only
if (!isset($_SESSION['Role']) || $_SESSION['Role'] !== 'student') {
    header("Location: ../login.html");
    exit();
}

$user_id = $_SESSION['UserID'];

// Get enrolled courses
$query = "SELECT 
    c.CourseID,
    c.CourseName,
    c.Instructor,
    c.Price,
    c.Rating,
    c.Level,
    c.Category,
    c.ImageURL,
    e.Progress,
    e.Status,
    e.EnrollmentDate
FROM enrollments e
JOIN courses c ON e.CourseID = c.CourseID
WHERE e.UserID = $user_id
ORDER BY e.EnrollmentDate DESC";

$result = $conn->query($query);
$courses = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $courses[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses - AAST Extra Learning</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="../Teacher/courses-page.css">
    <style>
        .my-courses-header {
            background: linear-gradient(135deg, #010d1c 0%, #1a3a52 100%);
            padding: 40px 20px;
            color: white;
        }

        .my-courses-header h1 {
            font-size: 36px;
            font-weight: 800;
            margin-bottom: 10px;
        }

        .progress-bar {
            width: 100%;
            height: 8px;
            background-color: #e5e7eb;
            border-radius: 4px;
            overflow: hidden;
            margin: 12px 0;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #4a90e2 0%, #357abd 100%);
            transition: width 0.3s ease;
        }

        .course-actions {
            display: flex;
            gap: 8px;
            margin-top: 12px;
        }

        .btn-continue {
            flex: 1;
            padding: 8px 12px;
            background-color: #4a90e2;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 13px;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s ease;
        }

        .btn-continue:hover {
            background-color: #357abd;
        }

        .btn-drop {
            flex: 1;
            padding: 8px 12px;
            background-color: #f3f4f6;
            color: #ef4444;
            border: 1px solid #ef4444;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 13px;
            transition: all 0.3s ease;
        }

        .btn-drop:hover {
            background-color: #fef2f2;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6b7280;
        }

        .empty-state h2 {
            font-size: 24px;
            margin-bottom: 10px;
            color: #010d1c;
        }

        .empty-state p {
            margin-bottom: 20px;
        }

        .empty-state .btn-primary {
            display: inline-block;
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
                <li><a href="../aboutus.html" class="nav-link">About Us</a></li>
                <li><a href="../Courses.php" class="nav-link">Browse Courses</a></li>
                <li><a href="sdashboard.php" class="nav-link">Dashboard</a></li>
                <li><a href="../logout.php" class="nav-link btn-login">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="my-courses-header">
        <div style="max-width: 1200px; margin: 0 auto;">
            <h1>My Courses</h1>
            <p>Continue learning from where you left off</p>
        </div>
    </div>

    <main class="container">
        <?php if (empty($courses)): ?>
            <div class="empty-state">
                <h2>No Courses Yet</h2>
                <p>You haven't enrolled in any courses yet. Start learning today!</p>
                <a href="../Courses.php" class="btn btn-primary">Explore Courses</a>
            </div>
        <?php else: ?>
            <section class="courses-section">
                <div class="courses-grid">
                    <?php foreach ($courses as $course): ?>
                        <div class="course-card">
                            <div class="course-image-wrapper">
                                <div class="course-img-placeholder" style="background-image: url('<?php echo $course['ImageURL']; ?>'); background-size: cover; background-position: center;"></div>
                                <div class="badge" style="background-color: #4a90e2;">In Progress</div>
                            </div>
                            <div class="card-content">
                                <h3 class="course-title"><?php echo htmlspecialchars($course['CourseName']); ?></h3>
                                <p class="instructor"><?php echo htmlspecialchars($course['Instructor']); ?></p>
                                
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?php echo $course['Progress']; ?>%;"></div>
                                </div>
                                <p style="font-size: 12px; color: #6b7280; margin-top: 4px;">
                                    <?php echo $course['Progress']; ?>% Complete
                                </p>

                                <div class="rating-section" style="margin-top: 12px;">
                                    <div class="rating"><?php echo number_format($course['Rating'], 1); ?> ★★★★★</div>
                                </div>

                                <div class="course-actions">
                                    <a href="course_detail.php?course_id=<?php echo $course['CourseID']; ?>" class="btn-continue">Continue Learning</a>
                                    <form method="POST" action="drop_course.php" style="flex: 1;">
                                        <input type="hidden" name="course_id" value="<?php echo $course['CourseID']; ?>">
                                        <button type="submit" class="btn-drop" onclick="return confirm('Are you sure you want to drop this course?');">Drop Course</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
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
