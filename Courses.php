<?php
session_start();
require_once 'DB/db_connect.php';

// Check if user is logged in
$is_logged_in = isset($_SESSION['UserID']);
$is_student = isset($_SESSION['Role']) && $_SESSION['Role'] === 'student';

// Get filter parameters
$category = isset($_GET['category']) ? $conn->real_escape_string($_GET['category']) : '';
$level = isset($_GET['level']) ? $conn->real_escape_string($_GET['level']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// Build the query
$query = "SELECT * FROM courses WHERE 1=1";

// NOTE: If you haven't added 'Category' and 'Level' columns to your database yet,
// using these filters on the page will cause a database error.
if ($category && $category !== 'all') {
    $query .= " AND Category = '$category'";
}

if ($level && $level !== 'all') {
    $query .= " AND Level = '$level'";
}

// Apply sorting
switch ($sort) {
    case 'popularity':
        // Popularity sort will fail until a 'ReviewCount' column is added to the DB
        $query .= " ORDER BY CourseID DESC"; 
        break;
    case 'price_low':
        // Price sort will fail until a 'Price' column is added to the DB
        $query .= " ORDER BY CourseID DESC";
        break;
    case 'price_high':
        $query .= " ORDER BY CourseID DESC";
        break;
    case 'rating':
        // Rating sort will fail until a 'Rating' column is added to the DB
        $query .= " ORDER BY CourseID DESC";
        break;
    default: // newest
        $query .= " ORDER BY CourseID DESC"; // FIXED sorting
}

// Pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;

$query .= " LIMIT $per_page OFFSET $offset";

$result = $conn->query($query);
$courses = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $courses[] = $row;
    }
}

// Get enrolled course IDs for current user
$enrolled_courses = [];
if ($is_student) {
    $user_id = $_SESSION['UserID'];
    $enrollment_query = "SELECT CourseID FROM enrollments WHERE UserID = $user_id AND Status = 'Active'";
    $enrollment_result = $conn->query($enrollment_query);
    
    if ($enrollment_result && $enrollment_result->num_rows > 0) {
        while ($row = $enrollment_result->fetch_assoc()) {
            $enrolled_courses[] = $row['CourseID'];
        }
    }
}

// Get total courses count for pagination
$count_query = "SELECT COUNT(*) as total FROM courses WHERE 1=1";
if ($category && $category !== 'all') {
    $count_query .= " AND Category = '$category'";
}
if ($level && $level !== 'all') {
    $count_query .= " AND Level = '$level'";
}
$count_result = $conn->query($count_query);
$count_row = $count_result->fetch_assoc();
$total_courses = $count_row['total'];
$total_pages = ceil($total_courses / $per_page);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Courses | AAST Extra Learning</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="Teacher/courses-page.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">
                <h1 class="logo-text">AASTMT</h1>
                <span class="logo-subtitle">Success Hub</span>
            </div>
            <ul class="nav-links">
                <li><a href="index.html" class="nav-link">Home</a></li>
                <li><a href="aboutus.html" class="nav-link">About Us</a></li>
                <li><a href="Courses.php" class="nav-link active">Courses</a></li>
                <?php if ($is_logged_in): ?>
                    <?php if ($is_student): ?>
                        <li><a href="student/sdashboard.php" class="nav-link">Dashboard</a></li>
                        <li><a href="student/my_courses.php" class="nav-link">My Courses</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php" class="nav-link btn-login">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.html" class="nav-link btn-login">Login</a></li>
                    <li><a href="signup.html" class="nav-link btn-signup">Signup</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <header class="courses-header">
        <div class="header-content">
            <h1>Explore Our Courses</h1>
            <p>Discover hundreds of expertly-crafted courses from AASTMT faculty members</p>
            <div class="search-bar">
                <input type="text" placeholder="Search courses..." class="search-input" id="searchInput" />
                <button class="search-btn" onclick="searchCourses()">Search</button>
            </div>
        </div>
    </header>

    <main class="courses-main">
        <aside class="filters-sidebar">
            <form method="GET" id="filterForm">
                <div class="filter-section">
                    <h3 class="filter-title">Category</h3>
                    <div class="filter-options">
                        <label class="filter-label">
                            <input type="radio" name="category" value="all" <?php echo (!$category || $category === 'all') ? 'checked' : ''; ?> onchange="document.getElementById('filterForm').submit();">
                            All Categories
                        </label>
                        <label class="filter-label">
                            <input type="radio" name="category" value="Engineering" <?php echo $category === 'Engineering' ? 'checked' : ''; ?> onchange="document.getElementById('filterForm').submit();">
                            Engineering
                        </label>
                        <label class="filter-label">
                            <input type="radio" name="category" value="Business" <?php echo $category === 'Business' ? 'checked' : ''; ?> onchange="document.getElementById('filterForm').submit();">
                            Business
                        </label>
                        <label class="filter-label">
                            <input type="radio" name="category" value="Technology" <?php echo $category === 'Technology' ? 'checked' : ''; ?> onchange="document.getElementById('filterForm').submit();">
                            Technology
                        </label>
                        <label class="filter-label">
                            <input type="radio" name="category" value="Maritime" <?php echo $category === 'Maritime' ? 'checked' : ''; ?> onchange="document.getElementById('filterForm').submit();">
                            Maritime
                        </label>
                    </div>
                </div>

                <div class="filter-section">
                    <h3 class="filter-title">Level</h3>
                    <div class="filter-options">
                        <label class="filter-label">
                            <input type="radio" name="level" value="all" <?php echo (!$level || $level === 'all') ? 'checked' : ''; ?> onchange="document.getElementById('filterForm').submit();">
                            All Levels
                        </label>
                        <label class="filter-label">
                            <input type="radio" name="level" value="Beginner" <?php echo $level === 'Beginner' ? 'checked' : ''; ?> onchange="document.getElementById('filterForm').submit();">
                            Beginner
                        </label>
                        <label class="filter-label">
                            <input type="radio" name="level" value="Intermediate" <?php echo $level === 'Intermediate' ? 'checked' : ''; ?> onchange="document.getElementById('filterForm').submit();">
                            Intermediate
                        </label>
                        <label class="filter-label">
                            <input type="radio" name="level" value="Advanced" <?php echo $level === 'Advanced' ? 'checked' : ''; ?> onchange="document.getElementById('filterForm').submit();">
                            Advanced
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 20px;">Apply Filters</button>
            </form>
        </aside>

        <section class="courses-content">
            <div class="courses-header-bar">
                <p class="course-count">Showing <?php echo count($courses); ?> of <?php echo $total_courses; ?> courses</p>
                <form method="GET" style="display: inline;">
                    <select class="sort-select" name="sort" onchange="this.form.submit();">
                        <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Sort by: Newest</option>
                        <option value="popularity" <?php echo $sort === 'popularity' ? 'selected' : ''; ?>>Popularity</option>
                        <option value="price_low" <?php echo $sort === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                        <option value="price_high" <?php echo $sort === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                        <option value="rating" <?php echo $sort === 'rating' ? 'selected' : ''; ?>>Rating</option>
                    </select>
                    <?php if ($category && $category !== 'all'): ?>
                        <input type="hidden" name="category" value="<?php echo htmlspecialchars($category); ?>">
                    <?php endif; ?>
                    <?php if ($level && $level !== 'all'): ?>
                        <input type="hidden" name="level" value="<?php echo htmlspecialchars($level); ?>">
                    <?php endif; ?>
                </form>
            </div>

            <div class="courses-grid">
                <?php if (!empty($courses)): ?>
                    <?php foreach ($courses as $course): 
                        $is_enrolled = in_array($course['CourseID'], $enrolled_courses);
                    ?>
                        <div class="course-card">
                            <div class="course-image-wrapper">
                                <div class="course-img-placeholder" style="background-color: #1e293b; min-height: 160px;"></div>
                            </div>
                            <div class="card-content">
                                <h3 class="course-title"><?php echo htmlspecialchars($course['Title']); ?></h3>
                                <p class="instructor"><?php echo htmlspecialchars($course['Instructor']); ?></p>
                                
                                <p class="course-description" style="font-size: 0.9em; color: #94a3b8; margin: 10px 0 15px 0; line-height: 1.4;">
                                    <?php 
                                        $desc = htmlspecialchars($course['Description']);
                                        echo strlen($desc) > 70 ? substr($desc, 0, 70) . '...' : $desc; 
                                    ?>
                                </p>

                                <div class="price-section" style="justify-content: flex-end; margin-top: auto;">
                                    <?php if ($is_student): ?>
                                        <?php if ($is_enrolled): ?>
                                            <a href="student/course_detail.php?course_id=<?php echo $course['CourseID']; ?>" class="btn-enroll" style="background-color: #10b981;">Go to Course</a>
                                        <?php else: ?>
                                            <form method="POST" action="enroll.php" style="display: inline;">
                                                <input type="hidden" name="course_id" value="<?php echo $course['CourseID']; ?>">
                                                <button type="submit" class="btn-enroll">Enroll Now</button>
                                            </form>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <a href="login.html" class="btn-enroll">Login to Enroll</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="grid-column: 1/-1; text-align: center; padding: 40px; color: #6b7280;">No courses found matching your criteria.</p>
                <?php endif; ?>
            </div>

            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="Courses.php?page=<?php echo $page - 1; ?>&category=<?php echo $category; ?>&level=<?php echo $level; ?>&sort=<?php echo $sort; ?>" class="pagination-btn">← Previous</a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <?php if ($i == $page): ?>
                            <button class="pagination-btn active"><?php echo $i; ?></button>
                        <?php else: ?>
                            <a href="Courses.php?page=<?php echo $i; ?>&category=<?php echo $category; ?>&level=<?php echo $level; ?>&sort=<?php echo $sort; ?>" class="pagination-btn"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="Courses.php?page=<?php echo $page + 1; ?>&category=<?php echo $category; ?>&level=<?php echo $level; ?>&sort=<?php echo $sort; ?>" class="pagination-btn">Next →</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </section>
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

    <script>
        function searchCourses() {
            const searchTerm = document.getElementById('searchInput').value;
            if (searchTerm) {
                window.location.href = 'Courses.php?search=' + encodeURIComponent(searchTerm);
            }
        }
    </script>
</body>
</html>