<?php
session_start();
include '../DB/db_connect.php'; 

if (!isset($_SESSION['Role']) || $_SESSION['Role'] !== 'admin') {
    header("Location: ../login.html");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $conn->real_escape_string($_POST['title']);
    $instructor = $conn->real_escape_string($_POST['instructor']);
    $description = $conn->real_escape_string($_POST['description']);

    $sql = "INSERT INTO courses (Title, Instructor, Description) VALUES ('$title', '$instructor', '$description')";
    
    if ($conn->query($sql) === TRUE) {
        header("Location: ManageCourses.php?status=added");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Course | AASTMT Admin</title>
    <link rel="stylesheet" href="../style.css" />
    <link rel="stylesheet" href="admin.css" />
</head>
<body>
<div class="admin-container">
    <main class="main-content">
        <h1 style="color: #010d1c;">Add New Course</h1>
        <div class="admin-form-card">
            <form method="POST">
                <div class="admin-form-group">
                    <label>Course Title</label>
                    <input type="text" name="title" required>
                </div>
                <div class="admin-form-group">
                    <label>Instructor Name</label>
                    <input type="text" name="instructor" required>
                </div>
                <div class="admin-form-group">
                    <label>Course Description</label>
                    <textarea name="description" required style="width:100%; border:1px solid #cbd5e1; border-radius:6px; padding:12px; height:100px;"></textarea>
                </div>
                <div style="display: flex; gap: 15px; margin-top: 20px;">
                    <button type="submit" class="btn btn-primary" style="border:none; padding:12px 24px;">Create Course</button>
                    <a href="ManageCourses.php" class="btn btn-secondary" style="padding:10px 24px; text-decoration:none;">Cancel</a>
                </div>
            </form>
        </div>
    </main>
</div>
</body>
</html>