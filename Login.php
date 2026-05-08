<?php
session_start();
require('DB/db_connect.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
   
    $email = $conn->real_escape_string($_POST['emaill']);
    $password_input = $_POST['passwordl'];

   
    $sql = "SELECT * FROM users WHERE Email = '$email'";
    $result = $conn->query($sql);

   
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        // 4. Verify the hashed password
        if (password_verify($password_input, $user['Password'])) {
            
            $_SESSION['UserID'] = $user['UserID'];
            $_SESSION['Name'] = $user['Name'];
            $_SESSION['Role'] = $user['Role'];

         
            if ($user['Role'] == 'student') {
                header("Location: student/sdashboard.php");
            } elseif ($user['Role'] == 'teacher') {
                header("Location: teacher/tdashboard.php");
            } elseif ($user['Role'] == 'admin') {
                header("Location: admin/adminDashboard.php");
            }
            exit();

        } else {
          
            echo "<script>alert('Invalid password. Please try again.'); window.location.href='login.html';</script>";
        }
    } else {
       
        echo "<script>alert('No account found with that email.'); window.location.href='login.html';</script>";
    }
}
?>