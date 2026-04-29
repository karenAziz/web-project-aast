<?php
session_start();
include '../DB/db_connect.php'; 

// SECURITY LOCK: Admin Only
if (!isset($_SESSION['Role']) || $_SESSION['Role'] !== 'admin') {
    header("Location: ../login.html");
    exit();
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $role = $conn->real_escape_string($_POST['role']);

    // Hash the password for security
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (Name, Email, Password, Role) VALUES ('$name', '$email', '$hashed_password', '$role')";

    try {
        $conn->query($sql);
        header("Location: manageUser.php?status=added");
        exit();
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) {
            $error = "That email is already registered in the system.";
        } else {
            $error = "Database Error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add User | AASTMT Admin</title>
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
        <div class="page-header" style="margin-bottom: 30px;">
            <h1 style="color: #010d1c; margin: 0;">Add New User</h1>
            <p style="color: #6b7280; margin: 5px 0 0 0;">Create a new account manually.</p>
        </div>

        <div class="admin-form-card">
            <?php if ($error): ?>
                <div style="background-color: #fee2e2; color: #ef4444; padding: 10px; border-radius: 6px; margin-bottom: 20px;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="admin-form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" required placeholder="e.g. Ahmed Ali">
                </div>
                <div class="admin-form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" required placeholder="user@aast.edu">
                </div>
                <div class="admin-form-group">
                    <label>Temporary Password</label>
                    <input type="password" name="password" required placeholder="Assign a password">
                </div>
                <div class="admin-form-group">
                    <label>Account Role</label>
                    <select name="role" required>
                        <option value="student">Student</option>
                        <option value="teacher">Teacher</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div style="display: flex; gap: 15px; margin-top: 30px;">
                    <button type="submit" class="btn btn-primary" style="border: none; padding: 12px 24px;">Create User</button>
                    <a href="manageUser.php" class="btn btn-secondary" style="padding: 10px 24px; color: #010d1c; border-color: #cbd5e1;">Cancel</a>
                </div>
            </form>
        </div>
    </main>
</div>
</body>
</html>