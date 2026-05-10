<?php
session_start();
require_once 'DB/db_connect.php'; // Uses: $servername, $username, $password, $dbname

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emailOrName = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($emailOrName === '' || $password === '') {
        header("Location: login.html?error=missing_fields");
        exit();
    }

    $stmt = $conn->prepare("SELECT UserID, Name, Email, Password, Role FROM users WHERE Email = ? OR Name = ? LIMIT 1");
    $stmt->bind_param('ss', $emailOrName, $emailOrName);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['Password']) || $password === $user['Password']) {
            $_SESSION['UserID'] = $user['UserID'];
            $_SESSION['Name'] = $user['Name'];
            $_SESSION['Role'] = $user['Role'];

            if ($user['Role'] === 'admin') {
                header("Location: admin/adminDashboard.php");
            } elseif ($user['Role'] === 'teacher') {
                header("Location: Teacher/tdashboard.php");
            } else {
                header("Location: student/sdashboard.php");
            }
            exit();
        }

        header("Location: login.html?error=invalid_pass");
        exit();
    }

    header("Location: login.html?error=no_user");
    exit();
}
?>