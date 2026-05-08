<?php
session_start();
include '../DB/db_connect.php'; 

// SECURITY LOCK: Admin Only
if (!isset($_SESSION['Role']) || $_SESSION['Role'] !== 'admin') {
    header("Location: ../login.html");
    exit();
}

// Fetch all users from the database
$query = "SELECT * FROM users ORDER BY UserID DESC";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users | AASTMT Success Hub</title>
    <link rel="stylesheet" href="../style.css" />
    <link rel="stylesheet" href="admin.css" />
</head>
<body>

<div class="admin-container">
    <nav class="sidebar">
        <h2>AASTMT Admin</h2>
        <a href="adminDashboard.php">Dashboard</a>
        <a href="manageUser.php" class="active">Manage Users</a>
        <a href="ManageCourses.php">Manage Courses</a>
        <a href="Reports.php">Reports</a>
        <a href="Settings.php">Settings</a>
        <a href="../logout.php" class="logout-link">Logout</a>
    </nav>

    <main class="main-content">
        <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <div>
                <h1 style="color: #010d1c; margin: 0;">Manage Users</h1>
                <p style="color: #6b7280; margin: 5px 0 0 0;">View and manage registered accounts.</p>
            </div>
            <a href="addUser.php" class="btn btn-primary" style="padding: 10px 20px; text-decoration: none;">+ Add New User</a>
        </div>

        <div class="table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Email Address</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $row['UserID']; ?></td>
                                <td><strong><?php echo htmlspecialchars($row['Name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($row['Email']); ?></td>
                                <td>
                                    <span class="role-badge <?php echo strtolower($row['Role']); ?>">
                                        <?php echo ucfirst(htmlspecialchars($row['Role'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="editUser.php?id=<?php echo $row['UserID']; ?>" class="action-btn edit" style="text-decoration: none;">Edit</a>
                                    <a href="deleteUser.php?id=<?php echo $row['UserID']; ?>" class="action-btn delete" style="text-decoration: none;" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 30px; color: #6b7280;">No users found in the database.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

</body>
</html>