<?php
session_start();
include '../DB/db_connect.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Grab inputs
    $name = $conn->real_escape_string($_POST['fullname']);
    $email = $conn->real_escape_string($_POST['emails']);
    $password_input = $_POST['passwords'];
    $confirm_password_input = $_POST['confirmpasswords'];

    // 2. Validate passwords match
    if ($password_input !== $confirm_password_input) {
        header("Location: ../signup.html?error=password_mismatch");
        exit();
    }

    // 3. Hash password
    $hashed_password = password_hash($password_input, PASSWORD_DEFAULT);
    $role = 'student';

    // 4. Prepare the query
    $sql = "INSERT INTO users (Name, Email, Password, Role) 
            VALUES ('$name', '$email', '$hashed_password', '$role')";

    // 5. Try to insert, and CATCH the error if it fails
    try {
        $conn->query($sql);
        
        // If the code reaches this line, the query was successful!
        header("Location: ../login.html?status=success");
        exit();
        
    } catch (mysqli_sql_exception $e) {
        // MySQL Error Code 1062 means "Duplicate Entry"
        if ($e->getCode() == 1062) {
            header("Location: ../signup.html?error=email_exists");
            exit();
        } else {
            // If the database threw a different error, stop the page and show it so you can debug
            die("Database Error: " . $e->getMessage());
        }
    }
}
?>