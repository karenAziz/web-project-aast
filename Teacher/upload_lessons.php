<?php
session_start();
require_once '../DB/db_connect.php';

if (!isset($_SESSION['Role']) || $_SESSION['Role'] !== 'teacher') {
    header("Location: ../login.php"); exit();
}

$instructor = $_SESSION['Name'];
$message = "";

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = intval($_POST['course_id']);
    $title = $conn->real_escape_string($_POST['title']);
    $video = $conn->real_escape_string($_POST['video_url']);
    $order = intval($_POST['order']);

    $sql = "INSERT INTO lessons (CourseID, Title, VideoURL, LessonOrder) VALUES ($course_id, '$title', '$video', $order)";
    if ($conn->query($sql)) {
        $message = "<div style='color:green;'>Lesson added successfully!</div>";
    } else {
        $message = "<div style='color:red;'>Error: " . $conn->error . "</div>";
    }
}

// Fetch only this teacher's courses
$courses = $conn->query("SELECT CourseID, Title FROM courses WHERE Instructor = '$instructor'");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Upload Lessons</title>
    <link rel="stylesheet" href="tDashboard.css">
    <style>
        .form-box { background: white; padding: 30px; border-radius: 12px; max-width: 600px; margin: 40px auto; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        select, input { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 6px; }
        .btn { background: #4a90e2; color: white; padding: 12px 25px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; width: 100%; }
    </style>
</head>
<body>
    <div class="content">
        <div class="form-box">
            <h2>Add Lesson to Course</h2>
            <?php echo $message; ?>
            <form method="POST">
                <label>Select Course</label>
                <select name="course_id" required>
                    <?php while($c = $courses->fetch_assoc()): ?>
                        <option value="<?php echo $c['CourseID']; ?>"><?php echo htmlspecialchars($c['Title']); ?></option>
                    <?php endwhile; ?>
                </select>
                
                <label>Lesson Title</label>
                <input type="text" name="title" placeholder="e.g. 01. Getting Started" required>
                
                <label>YouTube Embed Link</label>
                <input type="url" name="video_url" placeholder="https://www.youtube.com/embed/XXXXX" required>
                
                <label>Lesson Order</label>
                <input type="number" name="order" value="1" min="1" required>
                
                <button type="submit" class="btn">Upload Lesson</button>
                <p style="text-align:center; margin-top:15px;"><a href="tdashboard.php" style="color:#666;">Back to Dashboard</a></p>
            </form>
        </div>
    </div>
</body>
</html>