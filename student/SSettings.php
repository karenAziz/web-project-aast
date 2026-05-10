<?php
session_start();
require_once '../DB/db_connect.php';

// SECURITY LOCK: Student Only
if (!isset($_SESSION['Role']) || $_SESSION['Role'] !== 'student') {
    header("Location: ../login.html");
    exit();
}

$user_id = $_SESSION['UserID'];
$message = '';

// Handle Settings Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $new_pass = $_POST['new_password'];
    
    // If they typed a new password, update everything
    if (!empty($new_pass)) {
        $stmt = $conn->prepare("UPDATE users SET Name=?, Email=?, Password=? WHERE UserID=?");
        $stmt->bind_param("sssi", $name, $email, $new_pass, $user_id);
    } else {
        // Otherwise, only update name and email
        $stmt = $conn->prepare("UPDATE users SET Name=?, Email=? WHERE UserID=?");
        $stmt->bind_param("ssi", $name, $email, $user_id);
    }
    
    if ($stmt->execute()) {
        $_SESSION['Name'] = $name; // Update their name in the dashboard right away
        $message = "<div style='color: green; margin-bottom: 15px;'>Settings updated successfully!</div>";
    } else {
        $message = "<div style='color: red; margin-bottom: 15px;'>Error updating settings.</div>";
    }
}

// Fetch Current User Data to pre-fill the form
$stmt = $conn->prepare("SELECT Name, Email FROM users WHERE UserID=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_data = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>AASTMT Student - Settings</title>
    <link rel="stylesheet" href="SSettings.css">
</head>
<body>

    <nav class="sidebar">
        <div class="sidebar-header">
            <h2>AASTMT Student</h2>
        </div>
        <ul class="nav-links">
            <li><a href="sdashboard.php">Dashboard</a></li>
            <li><a href="my_courses.php">My Courses</a></li>
            <li><a href="Assessment.php">Assessments</a></li>
            <li><a href="SSettings.php" class="active">Settings</a></li>
        </ul>
        <div class="sidebar-footer">
            <a href="../logout.php" class="logout">Logout</a>
        </div>
    </nav>

    <main class="content">
        <header class="page-header">
            <h1>Account Settings</h1>
            <p>Manage your profile information and security preferences.</p>
        </header>

        <div class="settings-container">
            <section class="settings-card">
                <div class="card-header">
                    <h3>Personal Information & Security</h3>
                </div>
                <div class="card-body">
                    <?php echo $message; ?>
                    
                    <form method="POST">
                        <div class="input-group">
                            <label>Full Name</label>
                            <input type="text" name="name" value="<?php echo htmlspecialchars($user_data['Name']); ?>" required>
                        </div>
                        <div class="input-group">
                            <label>Email Address</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($user_data['Email']); ?>" required>
                        </div>
                        <div class="input-group">
                            <label>New Password (Leave blank to keep current)</label>
                            <input type="password" name="new_password" placeholder="Enter new password">
                        </div>
                        <button type="submit" class="btn-primary">Save All Changes</button>
                    </form>
                </div>
            </section>
        </div>
    </main>

</body>
</html>