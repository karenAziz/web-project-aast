<?php
session_start();

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "aast_web";
$message = "";

$con = mysqli_connect($host, $user, $pass, $dbname);
if (!$con) {
    die("Connection error: " . mysqli_connect_errno());
}

function ensureUsersTable($con)
{
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255),
        email VARCHAR(255) UNIQUE,
        password VARCHAR(255),
        role VARCHAR(50) DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    mysqli_query($con, $sql);
}

ensureUsersTable($con);

$adminUsername = "Yasser";
$adminPassword = "Yasser@12";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["emails"], $_POST["passwords"], $_POST["confirmpasswords"])) {
        // Signup flow
        $name = trim($_POST["fullname"] ?? "");
        $email = trim($_POST["emails"]);
        $password = $_POST["passwords"];
        $confirmPassword = $_POST["confirmpasswords"];

        if ($password !== $confirmPassword) {
            $message = "Passwords do not match. Please try again.";
        } else {
            $stmt = mysqli_prepare($con, "SELECT id FROM users WHERE email = ?");
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);

            if (mysqli_stmt_num_rows($stmt) > 0) {
                $message = "This email is already registered. Please login instead.";
            } else {
                mysqli_stmt_close($stmt);
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $role = ($email === $adminUsername || $name === $adminUsername) ? "admin" : "user";

                $insert = mysqli_prepare($con, "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
                mysqli_stmt_bind_param($insert, "ssss", $name, $email, $passwordHash, $role);

                if (mysqli_stmt_execute($insert)) {
                    $message = "Registration successful! You can now log in.";
                } else {
                    $message = "Registration failed. Please try again later.";
                }
                mysqli_stmt_close($insert);
            }
            mysqli_stmt_close($stmt);
        }
    } elseif (isset($_POST["emaill"], $_POST["passwordl"])) {
        // Login flow
        $login = trim($_POST["emaill"]);
        $password = $_POST["passwordl"];

        if ($login === $adminUsername && $password === $adminPassword) {
            $_SESSION["username"] = $adminUsername;
            $_SESSION["role"] = "admin";
            header("Location: admin.php");
            exit;
        }

        $stmt = mysqli_prepare($con, "SELECT id, name, email, password, role FROM users WHERE email = ? OR name = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "ss", $login, $login);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $id, $name, $email, $passwordHash, $role);

        if (mysqli_stmt_fetch($stmt)) {
            if (password_verify($password, $passwordHash)) {
                $_SESSION["username"] = $name ?: $email;
                $_SESSION["role"] = $role;
                mysqli_stmt_close($stmt);

                if ($role === "admin") {
                    header("Location: admin.php");
                } else {
                    header("Location: Courses.php");
                }
                exit;
            }
        }

        mysqli_stmt_close($stmt);
        $message = "Invalid username/email or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login / Signup Result</title>
</head>
<body>
    <?php if ($message): ?>
        <p><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
    <p><a href="login.html">Go back to login</a></p>
    <p><a href="signup.html">Go back to signup</a></p>
</body>
</html>
