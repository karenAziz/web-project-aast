<?php
session_start();
require_once 'DB/db_connect.php'; // Ensures connection to aast_web

// 1. Authentication Check
$is_logged_in = isset($_SESSION['UserID']);
$is_student = isset($_SESSION['Role']) && $_SESSION['Role'] === 'student';

// 2. Filter and Search Parameters
$category = isset($_GET['category']) ? $conn->real_escape_string($_GET['category']) : 'all';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

// 3. Build Dynamic SQL Query
$query = "SELECT CourseID, Title, Instructor, Description, Price, Level, Category FROM courses WHERE 1=1";

// Apply Search Filter
if (!empty($search)) {
    $query .= " AND (Title LIKE '%$search%' OR Description LIKE '%$search%' OR Instructor LIKE '%$search%')";
}

// Apply Category Filter
if ($category !== 'all') {
    $query .= " AND Category = '$category'";
}

// 4. Pagination Setup
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$per_page = 9; // Changed to 9 for a cleaner 3x3 grid
$offset = ($page - 1) * $per_page;

// Count total for pagination
$total_res = $conn->query(str_replace("CourseID, Title, Instructor, Description, Price, Level, Category", "COUNT(*)", $query));
$total_rows = $total_res->fetch_row()[0];
$total_pages = ceil($total_rows / $per_page);

// 5. Apply Sorting and Limits
if ($sort === 'price_low') {
    $query .= " ORDER BY Price ASC";
} elseif ($sort === 'price_high') {
    $query .= " ORDER BY Price DESC";
} else {
    $query .= " ORDER BY CourseID DESC";
}

$query .= " LIMIT $per_page OFFSET $offset";

$result = $conn->query($query);
$courses = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) { $courses[] = $row; }
}

// 6. Check Enrollments for current student
$enrolled_courses = [];
if ($is_student) {
    $user_id = $_SESSION['UserID'];
    $enroll_res = $conn->query("SELECT CourseID FROM enrollments WHERE UserID = $user_id");
    while ($row = $enroll_res->fetch_assoc()) { $enrolled_courses[] = $row['CourseID']; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Explore Courses | Success Hub</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="Teacher/courses-page.css">
    <style>
        .search-bar-container { background: #010d1c; padding: 20px; text-align: center; margin-bottom: 30px; }
        .search-input { padding: 12px; width: 300px; border-radius: 6px; border: none; }
        .filter-btn { padding: 10px 20px; background: #1e293b; color: white; border-radius: 20px; text-decoration: none; font-size: 13px; margin: 0 5px; }
        .filter-btn.active { background: #4a90e2; }
        .pagination { display: flex; justify-content: center; gap: 10px; margin: 40px 0; }
        .page-link { padding: 10px 15px; background: white; border: 1px solid #ddd; text-decoration: none; color: #1e293b; border-radius: 4px; }
        .page-link.active { background: #4a90e2; color: white; border-color: #4a90e2; }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo"><h1 class="logo-text">AASTMT</h1><span class="logo-subtitle">Success Hub</span></div>
            <ul class="nav-links">
                <li><a href="index.html" class="nav-link">Home</a></li>
                <li><a href="Courses.php" class="nav-link active">Courses</a></li>
                <?php if ($is_logged_in): ?>
                    <li><a href="<?php echo ($is_student ? 'student/sdashboard.php' : 'Teacher/tdashboard.php'); ?>" class="nav-link">Dashboard</a></li>
                    <li><a href="logout.php" class="nav-link" style="color: #ff6b6b;">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.html" class="nav-link btn-login">Login</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <div class="search-bar-container">
        <form method="GET" action="Courses.php">
            <input type="text" name="search" class="search-input" placeholder="Search courses or instructors..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" style="padding: 12px 20px; background: #4a90e2; color: white; border: none; border-radius: 6px; cursor: pointer;">Search</button>
        </form>
        <div style="margin-top: 20px;">
            <a href="Courses.php?category=all" class="filter-btn <?php echo $category == 'all' ? 'active' : ''; ?>">All</a>
            <a href="Courses.php?category=Technology" class="filter-btn <?php echo $category == 'Technology' ? 'active' : ''; ?>">Technology</a>
            <a href="Courses.php?category=Business" class="filter-btn <?php echo $category == 'Business' ? 'active' : ''; ?>">Business</a>
            <a href="Courses.php?category=Design" class="filter-btn <?php echo $category == 'Design' ? 'active' : ''; ?>">Design</a>
        </div>
    </div>

    <main class="courses-main">
        <div class="courses-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 30px;">
            <?php if (count($courses) > 0): ?>
                <?php foreach ($courses as $course): 
                    $is_enrolled = in_array($course['CourseID'], $enrolled_courses);
                ?>
                    <div class="course-card" style="background: white; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); overflow: hidden; display: flex; flex-direction: column;">
                        <div style="padding: 25px; flex-grow: 1;">
                            <span class="level-badge" style="background: #e0f2fe; color: #0369a1; padding: 4px 10px; border-radius: 4px; font-size: 11px; font-weight: 700;">
                                <?php echo htmlspecialchars($course['Level'] ?? 'Beginner'); ?>
                            </span>
                            <h3 style="margin: 15px 0 5px 0; color: #1e293b;"><?php echo htmlspecialchars($course['Title']); ?></h3>
                            <p style="font-size: 13px; color: #64748b; margin-bottom: 15px;">By <?php echo htmlspecialchars($course['Instructor']); ?></p>
                            <p style="font-size: 14px; color: #475569; line-height: 1.6;">
                                <?php echo htmlspecialchars(substr($course['Description'], 0, 90)) . '...'; ?>
                            </p>
                        </div>
                        <div style="padding: 20px 25px; border-top: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-weight: 800; color: #1e293b; font-size: 18px;"><?php echo number_format($course['Price'], 2); ?> L.E.</span>
                            
                            <?php if ($is_student): ?>
                                <a href="<?php echo ($is_enrolled ? 'student/course_detail.php' : 'student/checkout.php'); ?>?course_id=<?php echo $course['CourseID']; ?>" 
                                   style="background: #4a90e2; color: white; padding: 10px 18px; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 13px;">
                                    <?php echo ($is_enrolled ? 'Go to Course' : 'Enroll Now'); ?>
                                </a>
                            <?php else: ?>
                                <a href="login.html" style="background: #4a90e2; color: white; padding: 10px 18px; border-radius: 6px; text-decoration: none; font-size: 13px;">Login to Enroll</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 100px 0;">
                    <h2 style="color: #64748b;">No courses found matching your criteria.</h2>
                    <a href="Courses.php" style="color: #4a90e2; text-decoration: underline;">Clear all filters</a>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="Courses.php?page=<?php echo $i; ?>&category=<?php echo $category; ?>&search=<?php echo $search; ?>&sort=<?php echo $sort; ?>" 
                       class="page-link <?php echo ($i == $page) ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>