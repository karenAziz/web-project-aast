<?php
// Connect to the database (looking in the exact same folder now)
require('DB/db_connect.php');

// Array of all the users with @gmail.com emails
$users_to_add = [
    // 1. The Admin
    ['Name' => 'Admin', 'Email' => 'admin@gmail.com', 'Password' => 'Admin@12', 'Role' => 'admin'],
    
    // 2. The Teachers
    ['Name' => 'Hossam Selim', 'Email' => 'hossam@gmail.com', 'Password' => 'Teacher@123', 'Role' => 'teacher'],
    ['Name' => 'Karen Aziz', 'Email' => 'karen@gmail.com', 'Password' => 'Teacher@123', 'Role' => 'teacher'],
    ['Name' => 'Rita Armani', 'Email' => 'rita@gmail.com', 'Password' => 'Teacher@123', 'Role' => 'teacher'],
    ['Name' => 'Yasser Mohammed', 'Email' => 'yasser@gmail.com', 'Password' => 'Teacher@123', 'Role' => 'teacher'],
    ['Name' => 'Reem Sherbeny', 'Email' => 'reem@gmail.com', 'Password' => 'Teacher@123', 'Role' => 'teacher'],
    ['Name' => 'Jana Adham', 'Email' => 'jana@gmail.com', 'Password' => 'Teacher@123', 'Role' => 'teacher']
];

echo "<h2>Setting up users...</h2>";

foreach ($users_to_add as $u) {
    $name = $conn->real_escape_string($u['Name']);
    $email = $conn->real_escape_string($u['Email']);
    $role = $conn->real_escape_string($u['Role']);
    
    // SECURELY HASH THE PASSWORD
    $hashed_password = password_hash($u['Password'], PASSWORD_DEFAULT);

    // Insert into the database
    $sql = "INSERT INTO users (Name, Email, Password, Role) 
            VALUES ('$name', '$email', '$hashed_password', '$role')";

    if ($conn->query($sql) === TRUE) {
        echo "<p style='color: green;'>Successfully added: <b>$name</b> ($email) as $role</p>";
    } else {
        echo "<p style='color: red;'>Error adding $name (Email might already exist).</p>";
    }
}

echo "<hr><h3>✅ All done! For security, please delete this 'setup_users.php' file now.</h3>";
?>