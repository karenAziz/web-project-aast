<?php
session_start();

// Destroy session
session_destroy();

// Redirect to login
header("Location: login.html?logged_out=true");
exit();
?>
