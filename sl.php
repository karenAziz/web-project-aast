
<?php
$con=mysqli_connect("localhost","root","","aast_web");
// Check connection
if (!$con)
{
die("Connection error: " . mysqli_connect_errno());
}
$semail=$_POST["emails"];
$spass=$_POST["passwords"];
$sconfirmpassword=$_POST["confirmpasswords"];
if($spass== $sconfirmpassword)
    echo "Registration successful!";
else
    echo "Passwords do not match. Please try again.";
$lemail=$_POST["emaill"];
$lpassword=$_POST["passwordl "];
if($lpassword ==$spass && $lemail ==$semail)
    echo "Login successful!";
else
    echo "Invalid email or password. Please try again.";    

?>