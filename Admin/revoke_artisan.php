<?php
if(empty($_SESSION['role']) || $_SESSION['role'] !== 'admin')
    {
        header("Location: ../Artisans/login.php");
        exit;
    }
if(empty($_SESSION['csrf_token']))
    {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    } 
$artisan_id = (int) $_GET['revoke_artisan'];
$stmt = mysqli_prepare($conn,"update artisans set approved=0 where artisan_id=?");
mysqli_stmt_bind_param($stmt,"i",$artisan_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);
echo "<script>window.location.href='./index.php?view_artisans';</script>";
?>