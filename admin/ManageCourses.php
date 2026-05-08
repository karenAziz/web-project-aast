<?php
session_start();
include '../DB/db_connect.php'; 

// SECURITY LOCK: Admin Only
if (!isset($_SESSION['Role']) || $_SESSION['Role'] !== 'admin') {
    header("Location: ../login.html");
    exit();
}

// Fetch all courses from the database
// We use CourseID, Title, Instructor, and Description as confirmed by the schema
$query = "SELECT * FROM courses ORDER BY CourseID DESC";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Courses | AASTMT Success Hub</title>
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
        <a href="Reports.php">Reports</a>
        <a href="Settings.php">Settings</a>
        <a href="../logout.php" class="logout-link">Logout</a>
    </nav>

    <main class="main-content">
        <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <div>
                <h1 style="color: #010d1c; margin: 0;">Manage Courses</h1>
                <p style="color: #6b7280; margin: 5px 0 0 0;">Create, edit, or remove courses from the Success Hub catalog.</p>
            </div>
            <a href="addCourse.php" class="btn btn-primary" style="padding: 10px 20px; text-decoration: none;">+ Add New Course</a>
        </div>

        <?php if (isset($_GET['status'])): ?>
            <div style="background-color: #dcfce7; color: #166534; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-weight: 500;">
                <?php 
                    if ($_GET['status'] == 'added') echo "Course successfully created!";
                    if ($_GET['status'] == 'updated') echo "Course details updated!";
                    if ($_GET['status'] == 'deleted') echo "Course has been removed.";
                ?>
            </div>
        <?php endif; ?>

        <div class="table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Course Title</th>
                        <th>Instructor</th>
                        <th>Description</th>
                        <th style="width: 180px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $row['CourseID']; ?></td>
                                <td><strong><?php echo htmlspecialchars($row['Title']); ?></strong></td>
                                <td><?php echo htmlspecialchars($row['Instructor']); ?></td>
                                <td style="max-width: 300px;">
                                    <?php 
                                        $desc = htmlspecialchars($row['Description']);
                                        echo strlen($desc) > 80 ? substr($desc, 0, 80) . '...' : $desc; 
                                    ?>
                                </td>
                                <td>
                                    <a href="editCourse.php?id=<?php echo $row['CourseID']; ?>" class="action-btn edit" style="text-decoration: none;">Edit</a>
                                    <a href="deleteCourse.php?id=<?php echo $row['CourseID']; ?>" class="action-btn delete" style="text-decoration: none;" onclick="return confirm('Are you sure you want to delete this course? This action cannot be undone.');">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 40px; color: #6b7280;">
                                No courses currently available in the database.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

</body>
</html>