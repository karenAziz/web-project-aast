<?php
session_start();
echo "<div style='background: red; color: white; padding: 10px; z-index: 9999; position: relative;'>";
echo "Current Session Role: " . ($_SESSION['Role'] ?? 'NONE') . "<br>";
echo "Current Session UserID: " . ($_SESSION['UserID'] ?? 'NONE');
echo "</div>";
require_once '../DB/db_connect.php'; 

// SECURITY LOCK: Student Only
if (!isset($_SESSION['Role']) || $_SESSION['Role'] !== 'student') {
    header("Location: ../login.html");
    exit();
}

$user_id = $_SESSION['UserID'];

// Get dashboard stats
$stats_query = "SELECT 
    COUNT(*) as total_enrolled
FROM enrollments e
WHERE e.UserID = $user_id";

$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();

// Get recent courses
$recent_query = "SELECT c.*
FROM enrollments e
JOIN courses c ON e.CourseID = c.CourseID
WHERE e.UserID = $user_id
LIMIT 3";

$recent_result = $conn->query($recent_query);
$recent_courses = [];

if ($recent_result && $recent_result->num_rows > 0) {
    while ($row = $recent_result->fetch_assoc()) {
        $recent_courses[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - AAST Extra Learning</title>
    <link rel="stylesheet" href="../style.css" />
    <style>
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .dashboard-header {
            margin-bottom: 40px;
        }

        .dashboard-header h1 {
            font-size: 32px;
            font-weight: 800;
            color: #010d1c;
            margin-bottom: 5px;
        }

        .dashboard-header p {
            color: #6b7280;
            font-size: 16px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            text-align: center;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
            transform: translateY(-4px);
        }

        .stat-number {
            font-size: 36px;
            font-weight: 800;
            color: #4a90e2;
            margin-bottom: 8px;
        }

        .stat-label {
            font-size: 14px;
            color: #6b7280;
            font-weight: 500;
        }

        .section-title {
            font-size: 24px;
            font-weight: 800;
            color: #010d1c;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .view-all-link {
            font-size: 14px;
            color: #4a90e2;
            text-decoration: none;
            font-weight: 500;
        }

        .courses-preview {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .course-preview-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }

        .course-preview-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
        }

        .course-preview-image {
            width: 100%;
            height: 160px;
            background-color: #f0f0f0;
            background-size: cover;
            background-position: center;
        }

        .course-preview-content {
            padding: 16px;
        }

        .course-preview-title {
            font-size: 16px;
            font-weight: 700;
            color: #010d1c;
            margin-bottom: 8px;
        }

        .course-preview-instructor {
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 12px;
        }

        .progress-bar {
            width: 100%;
            height: 6px;
            background-color: #e5e7eb;
            border-radius: 3px;
            overflow: hidden;
            margin-bottom: 6px;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #4a90e2 0%, #357abd 100%);
        }

        .progress-text {
            font-size: 12px;
            color: #6b7280;
        }

        .action-buttons {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-top: 20px;
        }

        .btn {
            padding: 12px 16px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s ease;
            font-size: 14px;
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

        .empty-state {
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 12px;
        }

        .empty-state h3 {
            color: #010d1c;
            margin-bottom: 10px;
        }

        .empty-state p {
            color: #6b7280;
            margin-bottom: 20px;
        }

        /* --- New Footer Styling --- */
        .footer {
            background-color: white;
            border-top: 1px solid #e5e7eb;
            padding: 24px 20px;
            margin-top: 60px;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 16px;
        }

        @media (min-width: 768px) {
            .footer-content {
                flex-direction: row;
                justify-content: space-between;
            }
        }

        .footer p {
            color: #6b7280;
            font-size: 14px;
            margin: 0;
        }

        .footer-links {
            display: flex;
            gap: 24px;
        }

        .footer-links a {
            color: #6b7280;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: color 0.2s ease;
        }

        .footer-links a:hover {
            color: #4a90e2;
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
                <li><a href="Assessment.php" class="nav-link">Assessments</a></li>
                <li><a href="sdashboard.php" class="nav-link active">Dashboard</a></li>
                <li><a href="../logout.php" class="nav-link btn-login">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1>Welcome back, <?php echo htmlspecialchars($_SESSION['Name'] ?? 'Student'); ?>! 👋</h1>
            <p>Here's your learning progress and recommendations</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['total_enrolled'] ?? 0; ?></div>
                <div class="stat-label">Courses Enrolled</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">N/A</div>
                <div class="stat-label">Completed</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">N/A</div>
                <div class="stat-label">Average Progress</div>
            </div>
        </div>

        <div>
            <div class="section-title">
                Your Learning Journey
                <a href="my_courses.php" class="view-all-link">View All →</a>
            </div>

            <?php if (empty($recent_courses)): ?>
                <div class="empty-state">
                    <h3>No Courses Yet</h3>
                    <p>Start your learning journey by exploring our courses</p>
                    <a href="../Courses.php" class="btn btn-primary">Explore Courses</a>
                </div>
            <?php else: ?>
                <div class="courses-preview">
                    <?php foreach ($recent_courses as $course): ?>
                        <div class="course-preview-card">
                            <div class="course-preview-image" style="background-image: url('<?php echo htmlspecialchars($course['ImageURL'] ?? ''); ?>');"></div>
                            <div class="course-preview-content">
                                <div class="course-preview-title"><?php echo htmlspecialchars($course['Title']); ?></div>
                                <div class="course-preview-instructor"><?php echo htmlspecialchars($course['Instructor']); ?></div>
                                
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: 0%;"></div>
                                </div>
                                <div class="progress-text">Just Started</div>

                                <div class="action-buttons">
                                    <a href="course_detail.php?course_id=<?php echo $course['CourseID']; ?>" class="btn btn-primary">Continue</a>
                                    <a href="../Courses.php" class="btn btn-secondary">Browse More</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

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