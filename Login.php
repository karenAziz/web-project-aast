<?php
session_start();
// Make sure this points to your database connection file
require('DB/db_connect.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Grab the exact names from your HTML inputs
    $email = $conn->real_escape_string($_POST['emaill']);
    $password_input = $_POST['passwordl'];

    // 2. Search the database for that email
    $sql = "SELECT * FROM users WHERE Email = '$email'";
    $result = $conn->query($sql);

    // 3. If an account is found
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        // 4. Verify the hashed password
        if (password_verify($password_input, $user['Password'])) {
            
            // 5. Success! Save user data to the session
            $_SESSION['UserID'] = $user['UserID'];
            $_SESSION['Name'] = $user['Name'];
            $_SESSION['Role'] = $user['Role'];

            // 6. Route them to the correct dashboard
            if ($user['Role'] == 'student') {
                header("Location: student/sdashboard.php");
            } elseif ($user['Role'] == 'teacher') {
                header("Location: teacher/tdashboard.php");
            } elseif ($user['Role'] == 'admin') {
                header("Location: admin/adminDashboard.php");
            }
            exit();

        } else {
            // Password incorrect
            echo "<script>alert('Invalid password. Please try again.'); window.location.href='login.html';</script>";
        }
    } else {
        // Email not found
        echo "<script>alert('No account found with that email.'); window.location.href='login.html';</script>";
    }
}
?>