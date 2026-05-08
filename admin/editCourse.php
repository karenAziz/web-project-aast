<?php
session_start();
include '../DB/db_connect.php'; 

// SECURITY LOCK: Admin Only
if (!isset($_SESSION['Role']) || $_SESSION['Role'] !== 'admin') {
    header("Location: ../login.html");
    exit();
}

$course_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Redirect if no valid ID is provided
if ($course_id == 0) {
    header("Location: ManageCourses.php");
    exit();
}

// Fetch current course data to pre-fill the form
$query = "SELECT * FROM courses WHERE CourseID = $course_id";
$result = $conn->query($query);

if ($result->num_rows == 0) {
    header("Location: ManageCourses.php");
    exit();
}
$course = $result->fetch_assoc();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $conn->real_escape_string($_POST['title']);
    $instructor = $conn->real_escape_string($_POST['instructor']);
    $description = $conn->real_escape_string($_POST['description']);

    $update_sql = "UPDATE courses SET Title='$title', Instructor='$instructor', Description='$description' WHERE CourseID=$course_id";
    
    if ($conn->query($update_sql) === TRUE) {
        header("Location: ManageCourses.php?status=updated");
        exit();
    } else {
        $error = "Error updating course: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Course | AASTMT Admin</title>
    <link rel="stylesheet" href="../style.css" />
    <link rel="stylesheet" href="admin.css" />
</head>
<body>
<div class="admin-container">
    <nav class="sidebar">
        <h2>AASTMT Admin</h2>
        <a href="adminDashboard.php">Dashboard</a>
        <a href="manageUser.php">Manage Users</a>
        <a href="ManageCourses.php" class="active">Manage Courses</a>
        <a href="../logout.php" class="logout-link">Logout</a>
    </nav>

    <main class="main-content">
        <div class="page-header" style="margin-bottom: 30px;">
            <h1 style="color: #010d1c; margin: 0;">Edit Course #<?php echo $course_id; ?></h1>
            <p style="color: #6b7280; margin: 5px 0 0 0;">Modify the title, instructor, or details of this course.</p>
        </div>

        <div class="admin-form-card">
            <?php if (isset($error)): ?>
                <div style="background-color: #fee2e2; color: #ef4444; padding: 10px; border-radius: 6px; margin-bottom: 20px;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="admin-form-group">
                    <label>Course Title</label>
                    <input type="text" name="title" required value="<?php echo htmlspecialchars($course['Title']); ?>">
                </div>
                <div class="admin-form-group">
                    <label>Instructor Name</label>
                    <input type="text" name="instructor" required value="<?php echo htmlspecialchars($course['Instructor']); ?>">
                </div>
                <div class="admin-form-group">
                    <label>Course Description</label>
                    <textarea name="description" required style="width:100%; border:1px solid #cbd5e1; border-radius:6px; padding:12px; height:150px; font-family: inherit;"><?php echo htmlspecialchars($course['Description']); ?></textarea>
                </div>
                
                <div style="display: flex; gap: 15px; margin-top: 30px;">
                    <button type="submit" class="btn btn-primary" style="border: none; padding: 12px 24px;">Save Changes</button>
                    <a href="ManageCourses.php" class="btn btn-secondary" style="padding: 10px 24px; color: #010d1c; border-color: #cbd5e1; text-decoration: none;">Cancel</a>
                </div>
            </form>
        </div>
    </main>
</div>
</body>
</html>