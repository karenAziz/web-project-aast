<?php
session_start();
require_once '../DB/db_connect.php';

if (!isset($_SESSION['Role']) || $_SESSION['Role'] !== 'teacher') {
    header("Location: ../login.html"); exit();
}

$teacher_name = $_SESSION['Name'];
$course_id = intval($_GET['id'] ?? 0);

// Fetch the current course data to pre-fill the form
$stmt = $conn->prepare("SELECT * FROM courses WHERE CourseID = ? AND Instructor = ?");
$stmt->bind_param("is", $course_id, $teacher_name);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();

// If the course doesn't exist or doesn't belong to this teacher, stop here
if (!$course) {
    die("Course not found or you do not have permission to edit it.");
}

$message = "";

// Handle the form submission to update the database
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $conn->real_escape_string($_POST['title']);
    $price = floatval($_POST['price']);
    $category = $conn->real_escape_string($_POST['category']);
    $level = $conn->real_escape_string($_POST['level']);
    $desc = $conn->real_escape_string($_POST['description']);

    $update_stmt = $conn->prepare("UPDATE courses SET Title = ?, Price = ?, Category = ?, Level = ?, Description = ? WHERE CourseID = ? AND Instructor = ?");
    $update_stmt->bind_param("sdsssss", $title, $price, $category, $level, $desc, $course_id, $teacher_name);
    
    if ($update_stmt->execute()) {
        header("Location: courses.php?status=updated");
        exit();
    } else {
        $message = "Error updating course: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Course | Success Hub</title>
    <link rel="stylesheet" href="tDashboard.css">
    <style>
        .form-card { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); max-width: 700px; }
        input, select, textarea { width: 100%; padding: 12px; margin: 10px 0 20px 0; border: 1px solid #ddd; border-radius: 6px; }
        .grid-inputs { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .btn-save { background: #4a90e2; color: white; padding: 15px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; width: 100%; }
    </style>
</head>
<body style="display: flex; background: #f1f4f9;">

    <aside class="sidebar">
        <h2 style="padding: 20px; color: #4a90e2; font-size: 1.4rem;">Teacher Portal</h2>
        <a href="tdashboard.php">Dashboard</a>
        <a href="createCourse.php">Create Course</a>
        <a href="/web-project-aast/Courses.php">Browse Courses</a>
        <a href="/web-project-aast/Teacher/upload_lessons.php">Upload Lessons</a>
        <a href="manage_quizzes.php" class="active" style="border-left: 3px solid #f59e0b;">📝 Manage Quizzes</a>
        <a href="../logout.php" class="logout" style="margin-top: auto; color: #ff6b6b; padding: 20px;">Logout</a>
    </aside>

    <main class="content" style="margin-left: 260px; padding: 40px; width: 100%;">
        <div class="page-header">
            <h1>Edit Course: <?php echo htmlspecialchars($course['Title']); ?></h1>
        </div>

        <div class="form-card">
            <?php if($message) echo "<p style='color: #dc2626; margin-bottom: 15px;'>$message</p>"; ?>
            
            <form method="POST">
                <label style="font-weight: 600;">Course Title</label>
                <input type="text" name="title" value="<?php echo htmlspecialchars($course['Title']); ?>" required>
                
                <div class="grid-inputs">
                    <div>
                        <label style="font-weight: 600;">Price (L.E.)</label>
                        <input type="number" step="0.01" name="price" value="<?php echo htmlspecialchars($course['Price']); ?>" required>
                    </div>
                    <div>
                        <label style="font-weight: 600;">Category</label>
                        <select name="category" required>
                            <option value="Technology" <?php if($course['Category'] == 'Technology') echo 'selected'; ?>>Technology</option>
                            <option value="Business" <?php if($course['Category'] == 'Business') echo 'selected'; ?>>Business</option>
                            <option value="Design" <?php if($course['Category'] == 'Design') echo 'selected'; ?>>Design</option>
                        </select>
                    </div>
                </div>

                <label style="font-weight: 600;">Difficulty Level</label>
                <select name="level" required>
                    <option value="Beginner" <?php if($course['Level'] == 'Beginner') echo 'selected'; ?>>Beginner</option>
                    <option value="Intermediate" <?php if($course['Level'] == 'Intermediate') echo 'selected'; ?>>Intermediate</option>
                    <option value="Advanced" <?php if($course['Level'] == 'Advanced') echo 'selected'; ?>>Advanced</option>
                </select>

                <label style="font-weight: 600;">Description</label>
                <textarea name="description" rows="5" required><?php echo htmlspecialchars($course['Description']); ?></textarea>
                
                <button type="submit" class="btn-save">Save Changes</button>
                <div style="text-align: center; margin-top: 15px;">
                    <a href="courses.php" style="color: #64748b; text-decoration: none;">Cancel and Go Back</a>
                </div>
            </form>
        </div>
    </main>
</body>
</html>