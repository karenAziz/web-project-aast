<?php
session_start();
// Ensure this path correctly points to where you saved db_connect.php
include 'includes/db_connect.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Grab the exact 'name' attributes from your HTML form inputs
    $name = $conn->real_escape_string($_POST['fullname']);
    $email = $conn->real_escape_string($_POST['emails']);
    $password_input = $_POST['passwords'];
    $confirm_password_input = $_POST['confirmpasswords'];

    // 2. Validation: Check if the passwords match
    if ($password_input !== $confirm_password_input) {
        // Passwords don't match, send them back to signup
        header("Location: signup.html?error=password_mismatch");
        exit();
    }

    // 3. Hash the password for security
    $hashed_password = password_hash($password_input, PASSWORD_DEFAULT);

    // 4. Hardcode the role for this specific form
    $role = 'student';

    // 5. Insert the new user into the database
    $sql = "INSERT INTO users (Name, Email, Password, Role) 
            VALUES ('$name', '$email', '$hashed_password', '$role')";

    if ($conn->query($sql) === TRUE) {
        // Success! Redirect to the login page
        header("Location: login.html?status=success");
        exit();
    } else {
        // Fails if the email is already registered (due to the UNIQUE constraint in the DB)
        header("Location: signup.html?error=email_exists");
        exit();
    }
}
?>