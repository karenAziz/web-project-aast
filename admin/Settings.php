<?php
session_start();
include '../DB/db_connect.php'; 

// SECURITY LOCK: Admin Only
if (!isset($_SESSION['Role']) || $_SESSION['Role'] !== 'admin') {
    header("Location: ../login.html");
    exit();
}

$admin_id = $_SESSION['UserID'];
$message = '';
$error = '';

// 1. Fetch current Admin details
$query = "SELECT Name, Email FROM users WHERE UserID = $admin_id";
$result = $conn->query($query);
$admin_data = $result->fetch_assoc();

// 2. Handle Profile Update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $new_name = $conn->real_escape_string($_POST['admin_name']);
    $new_email = $conn->real_escape_string($_POST['admin_email']);

    $update_sql = "UPDATE users SET Name = '$new_name', Email = '$new_email' WHERE UserID = $admin_id";
    
    try {
        if ($conn->query($update_sql)) {
            $_SESSION['Name'] = $new_name; // Update session name for the sidebar
            $message = "Profile updated successfully!";
            // Refresh local data
            $admin_data['Name'] = $new_name;
            $admin_data['Email'] = $new_email;
        }
    } catch (mysqli_sql_exception $e) {
        $error = "Error: Email might already be in use.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Settings | AASTMT Success Hub</title>
    <link rel="stylesheet" href="../style.css" />
    <link rel="stylesheet" href="admin.css" />
</head>
<body>

<div class="admin-container">
    <nav class="sidebar">
        <h2>AASTMT Admin</h2>
        <a href="adminDashboard.php">Dashboard</a>
        <a href="manageUser.php">Manage Users</a>
        <a href="ManageCourses.php">Manage Courses</a>
        <a href="Reports.php">Reports</a>
        <a href="Settings.php" class="active">Settings</a>
        <a href="../logout.php" class="logout-link">Logout</a>
    </nav>

    <main class="main-content">
        <div class="page-header" style="margin-bottom: 30px;">
            <h1 style="color: #010d1c; margin: 0;">Settings</h1>
            <p style="color: #6b7280; margin: 5px 0 0 0;">Manage your account and platform preferences.</p>
        </div>

        <?php if ($message): ?>
            <div style="background-color: #dcfce7; color: #166534; padding: 12px; border-radius: 8px; margin-bottom: 20px;">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="dashboard-grid">
            <div class="admin-form-card">
                <h3 style="margin-bottom: 20px; color: #010d1c;">Admin Profile</h3>
                <form method="POST">
                    <div class="admin-form-group">
                        <label>Display Name</label>
                        <input type="text" name="admin_name" value="<?php echo htmlspecialchars($admin_data['Name']); ?>" required>
                    </div>
                    <div class="admin-form-group">
                        <label>Admin Email</label>
                        <input type="email" name="admin_email" value="<?php echo htmlspecialchars($admin_data['Email']); ?>" required>
                    </div>
                    <button type="submit" name="update_profile" class="btn btn-primary" style="border: none; padding: 12px 24px;">Update Profile</button>
                </form>
            </div>

            <div class="admin-form-card">
                <h3 style="margin-bottom: 20px; color: #010d1c;">Platform Configuration</h3>
                <div class="admin-form-group">
                    <label>Maintenance Mode</label>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="color: #10b981; font-weight: 600;">OFF</span>
                        <p style="font-size: 13px; color: #6b7280;">(When ON, students cannot access courses)</p>
                    </div>
                </div>
                <div class="admin-form-group">
                    <label>Default User Role</label>
                    <input type="text" value="Student" disabled style="background-color: #f1f5f9; cursor: not-allowed;">
                </div>
                <button class="btn btn-secondary" style="padding: 10px 24px; cursor: not-allowed;" disabled>Save Site Settings</button>
            </div>
        </div>
    </main>
</div>

</body>
</html>