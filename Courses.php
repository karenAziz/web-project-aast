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
$per_page = 9; // 3x3 grid
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
    if ($enroll_res) {
        while ($row = $enroll_res->fetch_assoc()) { $enrolled_courses[] = $row['CourseID']; }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Explore Courses | Success Hub</title>
    <link rel="stylesheet" href="style.css">
    
    <style>
        body { background-color: #f8fafc; font-family: 'Segoe UI', sans-serif; margin: 0; }
        
        /* Banner & Search Area */
        .search-banner { 
            background: linear-gradient(135deg, #010d1c 0%, #1a3a52 100%); 
            padding: 50px 20px; 
            text-align: center; 
            margin-bottom: 40px; 
        }
        .search-banner h1 { color: white; font-size: 2.2rem; margin-bottom: 25px; }
        .search-form { display: flex; justify-content: center; max-width: 600px; margin: 0 auto 25px auto; }
        .search-input { 
            padding: 15px 20px; 
            width: 100%; 
            border-radius: 8px 0 0 8px; 
            border: none; 
            outline: none; 
            font-size: 15px; 
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .search-btn { 
            padding: 15px 30px; 
            background: #4a90e2; 
            color: white; 
            border: none; 
            border-radius: 0 8px 8px 0; 
            cursor: pointer; 
            font-weight: 700; 
            font-size: 15px; 
            transition: background 0.3s; 
        }
        .search-btn:hover { background: #357abd; }

        /* Filter Buttons */
        .filter-btn { 
            padding: 10px 22px; 
            background: rgba(255,255,255,0.1); 
            color: white; 
            border-radius: 25px; 
            text-decoration: none; 
            font-size: 14px; 
            font-weight: 500;
            margin: 0 6px; 
            border: 1px solid rgba(255,255,255,0.2);
            transition: all 0.3s ease; 
            display: inline-block;
            margin-bottom: 10px;
        }
        .filter-btn:hover, .filter-btn.active { 
            background: #4a90e2; 
            border-color: #4a90e2; 
            box-shadow: 0 4px 10px rgba(74, 144, 226, 0.3);
        }

        /* Course Cards */
        .courses-main { max-width: 1200px; margin: 0 auto; padding: 0 20px 60px 20px; }
        .courses-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); 
            gap: 35px; 
        }
        .course-card { 
            background: white; 
            border-radius: 14px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.04); 
            overflow: hidden; 
            display: flex; 
            flex-direction: column; 
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid #f1f5f9;
        }
        .course-card:hover { 
            transform: translateY(-8px); 
            box-shadow: 0 12px 25px rgba(0,0,0,0.1); 
        }
        .card-body { padding: 30px 25px; flex-grow: 1; }
        .card-footer { 
            padding: 20px 25px; 
            background: #fcfcfc; 
            border-top: 1px solid #f1f5f9; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
        }
        
        /* Badges & Typography */
        .level-badge { 
            background: #e0f2fe; 
            color: #0369a1; 
            padding: 5px 12px; 
            border-radius: 6px; 
            font-size: 12px; 
            font-weight: 700; 
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .course-title { margin: 18px 0 8px 0; color: #0f172a; font-size: 1.25rem; font-weight: 700; }
        .course-instructor { font-size: 13px; color: #64748b; margin-bottom: 15px; font-weight: 500; }
        .course-desc { font-size: 14px; color: #475569; line-height: 1.6; }
        
        .price-tag { font-weight: 800; color: #0f172a; font-size: 1.2rem; }
        .btn-action { 
            background: #4a90e2; 
            color: white; 
            padding: 10px 20px; 
            border-radius: 8px; 
            text-decoration: none; 
            font-weight: 600; 
            font-size: 14px; 
            transition: background 0.3s;
        }
        .btn-action:hover { background: #357abd; }
        .btn-enrolled { background: #10b981; }
        .btn-enrolled:hover { background: #059669; }

        /* Pagination */
        .pagination { display: flex; justify-content: center; gap: 8px; margin-top: 50px; }
        .page-link { 
            padding: 10px 16px; 
            background: white; 
            border: 1px solid #e2e8f0; 
            text-decoration: none; 
            color: #475569; 
            border-radius: 8px; 
            font-weight: 600; 
            transition: 0.2s; 
        }
        .page-link:hover, .page-link.active { 
            background: #4a90e2; 
            color: white; 
            border-color: #4a90e2; 
        }
    </style>
</head>
<body>
    
    <nav class="navbar" style="background: #010d1c; padding: 15px 0;">
        <div class="nav-container" style="display: flex; justify-content: space-between; align-items: center; max-width: 1200px; margin: 0 auto; padding: 0 20px;">
            <div class="logo">
                <h1 style="color: white; font-size: 24px; margin: 0;">AASTMT <span style="font-weight: 400; font-size: 14px; opacity: 0.7;">Success Hub</span></h1>
            </div>
            <ul class="nav-links" style="display: flex; gap: 25px; list-style: none; margin: 0;">
                <li><a href="index.html" style="color: #d1d5db; text-decoration: none; font-size: 14px; font-weight: 500;">Home</a></li>
                <li><a href="Courses.php" style="color: #4a90e2; text-decoration: none; font-size: 14px; font-weight: 600;">Courses</a></li>
                <?php if ($is_logged_in): ?>
                    <li><a href="<?php echo ($is_student ? 'student/sdashboard.php' : 'Teacher/tdashboard.php'); ?>" style="color: #d1d5db; text-decoration: none; font-size: 14px; font-weight: 500;">Dashboard</a></li>
                    <li><a href="logout.php" style="color: #ff6b6b; text-decoration: none; font-weight: 600; border: 1px solid #ff6b6b; padding: 6px 16px; border-radius: 6px;">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.html" style="background: #4a90e2; color: white; padding: 8px 20px; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 14px;">Login</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <div class="search-banner">
        <h1>Expand Your Knowledge</h1>
        <form method="GET" action="Courses.php" class="search-form">
            <input type="text" name="search" class="search-input" placeholder="Search by course title, instructor, or topic..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="search-btn">Search</button>
        </form>
        <div>
            <a href="Courses.php?category=all" class="filter-btn <?php echo $category == 'all' ? 'active' : ''; ?>">All Courses</a>
            <a href="Courses.php?category=Technology" class="filter-btn <?php echo $category == 'Technology' ? 'active' : ''; ?>">Technology</a>
            <a href="Courses.php?category=Business" class="filter-btn <?php echo $category == 'Business' ? 'active' : ''; ?>">Business</a>
            <a href="Courses.php?category=Design" class="filter-btn <?php echo $category == 'Design' ? 'active' : ''; ?>">Design</a>
        </div>
    </div>

    <main class="courses-main">
        <div class="courses-grid">
            <?php if (count($courses) > 0): ?>
                <?php foreach ($courses as $course): 
                    $is_enrolled = in_array($course['CourseID'], $enrolled_courses);
                ?>
                    <div class="course-card">
                        <div class="card-body">
                            <span class="level-badge"><?php echo htmlspecialchars($course['Level'] ?? 'Beginner'); ?></span>
                            <h3 class="course-title"><?php echo htmlspecialchars($course['Title']); ?></h3>
                            <div class="course-instructor">Instructor: <?php echo htmlspecialchars($course['Instructor']); ?></div>
                            <p class="course-desc">
                                <?php echo htmlspecialchars(substr($course['Description'], 0, 100)) . (strlen($course['Description']) > 100 ? '...' : ''); ?>
                            </p>
                        </div>
                        <div class="card-footer">
                            <span class="price-tag"><?php echo number_format($course['Price'], 2); ?> L.E.</span>
                            
                            <?php if ($is_student): ?>
                                <a href="<?php echo ($is_enrolled ? 'student/course_detail.php' : 'student/checkout.php'); ?>?course_id=<?php echo $course['CourseID']; ?>" 
                                   class="btn-action <?php echo $is_enrolled ? 'btn-enrolled' : ''; ?>">
                                    <?php echo ($is_enrolled ? 'Continue Learning' : 'Enroll Now'); ?>
                                </a>
                            <?php else: ?>
                                <a href="login.html" class="btn-action">Login to Enroll</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 80px 0; background: white; border-radius: 12px; border: 2px dashed #e2e8f0;">
                    <h2 style="color: #475569; margin-bottom: 10px;">No courses found</h2>
                    <p style="color: #64748b; margin-bottom: 20px;">Try adjusting your search or filter criteria.</p>
                    <a href="Courses.php" style="color: #4a90e2; text-decoration: none; font-weight: 600;">Clear all filters →</a>
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