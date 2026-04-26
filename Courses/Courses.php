<?php
$con=mysqli_connect("localhost","root","","aast_web");
// Check connection
if (!$con)
{
die("Connection error: " . mysqli_connect_errno());
}
?>