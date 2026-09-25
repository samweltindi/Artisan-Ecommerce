<?php
$conn = mysqli_connect("localhost","root","","School_Project");
if(!$conn){
    //show connection error shoud only be used when debuging locally
    die(mysqli_error($conn));
}


?>