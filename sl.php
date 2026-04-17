<html>
    <body>

<?php
$semail=$_POST["emails"];
$spass=$_POST["passwords"];
$sconfirmpassword=$_POST["confirmpasswords"];
if($spass== $sconfirmpassword){
    echo "Registration successful!";
}
else{
    echo "Passwords do not match. Please try again.";
}
?>
</body>
</html>