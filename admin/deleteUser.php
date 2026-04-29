<?php
session_start();
include '../DB/db_connect.php'; 

if (!isset($_SESSION['Role']) || $_SESSION['Role'] !== 'admin') {
    header("Location: ../login.html");
    exit();
}

if (isset($_GET['id'])) {
    $user_id = intval($_GET['id']);
    
    $sql = "DELETE FROM users WHERE UserID = $user_id";
    
    if ($conn->query($sql) === TRUE) {
        header("Location: manageUser.php?status=deleted");
        exit();
    } else {
        die("Error deleting record: " . $conn->error);
    }
} else {
    header("Location: manageUser.php");
    exit();
}
?>