<?php
session_start();
require_once '../DB/db_connect.php'; 

// Ensure the user is an admin
if (!isset($_SESSION['Role']) || $_SESSION['Role'] !== 'admin') {
    header("Location: ../login.html");
    exit();
}

if (isset($_GET['id'])) {
    $user_id = intval($_GET['id']);

    // 1. Delete the user's payment history to prevent foreign key errors
    $stmt_payments = $conn->prepare("DELETE FROM payments WHERE UserID = ?");
    $stmt_payments->bind_param("i", $user_id);
    $stmt_payments->execute();

    // 2. Delete the user's course enrollments
    $stmt_enrollments = $conn->prepare("DELETE FROM enrollments WHERE UserID = ?");
    $stmt_enrollments->bind_param("i", $user_id);
    $stmt_enrollments->execute();

    // 3. Delete the user's quiz grades 
    $stmt_grades = $conn->prepare("DELETE FROM quiz_grades WHERE UserID = ?");
    $stmt_grades->bind_param("i", $user_id);
    $stmt_grades->execute();

    // 4. FINALLY, delete the User account
    $stmt_user = $conn->prepare("DELETE FROM users WHERE UserID = ?");
    $stmt_user->bind_param("i", $user_id);
    
    if ($stmt_user->execute()) {
        // Success! Redirect back to the admin dashboard using the exact case-sensitive path
        header("Location: adminDashboard.php?status=user_deleted");
        exit();
    } else {
        echo "Error deleting user: " . $conn->error;
    }
} else {
    // Exact case-sensitive path
    header("Location: adminDashboard.php");
    exit();
}
?>