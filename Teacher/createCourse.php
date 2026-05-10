<?php
session_start();
require_once '../DB/db_connect.php';

if (!isset($_SESSION['Role']) || $_SESSION['Role'] !== 'teacher') {
    header("Location: ../login.html"); exit();
}

$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $conn->real_escape_string($_POST['title']);
    $price = floatval($_POST['price']);
    $cat = $conn->real_escape_string($_POST['category']);
    $lvl = $conn->real_escape_string($_POST['level']);
    $desc = $conn->real_escape_string($_POST['description']);
    $instructor = $_SESSION['Name'];

    $sql = "INSERT INTO courses (Title, Price, Category, Level, Description, Instructor) 
            VALUES ('$title', $price, '$cat', '$lvl', '$desc', '$instructor')";
    
    if ($conn->query($sql)) {
        $message = "Course published successfully!";
    } else {
        $message = "Error: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Course | Success Hub</title>
    <link rel="stylesheet" href="tDashboard.css">
    <style>
        .form-card { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); max-width: 700px; }
        .grid-inputs { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        input, select, textarea { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; }
        .btn { background: #4a90e2; color: white; padding: 15px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; width: 100%; margin-top: 10px; }
    </style>
</head>
<body>
    <aside class="sidebar">
        <h2>Teacher Portal</h2>
        <a href="tdashboard.php">Dashboard</a>
        <a href="createCourse.php" class="active">Create Course</a>
        <a href="uploadLessons.php">Upload Lessons</a>
        <a href="../logout.php" class="logout">Logout</a>
    </aside>

    <main class="content">
        <div class="page-header"><h1>Add New Course</h1></div>
        <div class="form-card">
            <?php if($message) echo "<p style='color:#166534; margin-bottom:15px;'>$message</p>"; ?>
            <form method="POST">
                <label>Course Title</label>
                <input type="text" name="title" placeholder="e.g. Advanced Web Development" required>
                
                <div class="grid-inputs">
                    <div>
                        <label>Price (L.E.)</label>
                        <input type="number" name="price" step="0.01" placeholder="400.00" required>
                    </div>
                    <div>
                        <label>Category</label>
                        <select name="category">
                            <option value="Technology">Technology</option>
                            <option value="Business">Business</option>
                            <option value="Engineering">Engineering</option>
                            <option value="Design">Design</option>
                        </select>
                    </div>
                </div>

                <label>Difficulty Level</label>
                <select name="level">
                    <option value="Beginner">Beginner</option>
                    <option value="Intermediate">Intermediate</option>
                    <option value="Advanced">Advanced</option>
                </select>

                <label>Description</label>
                <textarea name="description" rows="4" placeholder="What will students learn?" required></textarea>
                
                <button type="submit" class="btn">Create and Publish</button>
            </form>
        </div>
    </main>
</body>
</html>