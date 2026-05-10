<?php
session_start();
require_once '../DB/db_connect.php';

if (!isset($_SESSION['Role']) || $_SESSION['Role'] !== 'teacher') {
    header("Location: ../login.html"); exit();
}

$course_id = intval($_GET['id'] ?? 0);
$teacher = $_SESSION['Name'];

// Load data
$stmt = $conn->prepare("SELECT * FROM courses WHERE CourseID = ? AND Instructor = ?");
$stmt->bind_param("is", $course_id, $teacher);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();

if (!$course) die("Access Denied.");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $conn->real_escape_string($_POST['title']);
    $price = floatval($_POST['price']);
    $desc = $conn->real_escape_string($_POST['description']);

    $update = $conn->prepare("UPDATE courses SET Title = ?, Price = ?, Description = ? WHERE CourseID = ?");
    $update->bind_param("sdsi", $title, $price, $desc, $course_id);
    
    if ($update->execute()) {
        header("Location: courses.php?success=1");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Course</title>
    <link rel="stylesheet" href="tDashboard.css">
    <style>
        .edit-form { background: white; padding: 30px; border-radius: 12px; max-width: 600px; }
        input, textarea { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 6px; }
        .btn-save { background: #4a90e2; color: white; padding: 12px 25px; border: none; border-radius: 6px; cursor: pointer; }
    </style>
</head>
<body>
    <main class="content" style="margin-left:0; padding:40px;">
        <div class="edit-form">
            <h2>Edit Course Details</h2>
            <form method="POST">
                <label>Title</label>
                <input type="text" name="title" value="<?php echo htmlspecialchars($course['Title']); ?>">
                <label>Price (L.E.)</label>
                <input type="number" step="0.01" name="price" value="<?php echo $course['Price']; ?>">
                <label>Description</label>
                <textarea name="description" rows="5"><?php echo htmlspecialchars($course['Description']); ?></textarea>
                <button type="submit" class="btn-save">Save Changes</button>
            </form>
        </div>
    </main>
</body>
</html>