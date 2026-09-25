<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// This page creates admin accounts - it must never be reachable by anyone only registered admin
if (empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "<script>alert('Access denied.')</script>";
    echo "<script>window.location.href='../Artisans/login.php';</script>";
    exit;
}
if(empty($_SESSION['csrf_token']))
    {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
$customer_id = (int) $_GET['suspend_customer'];
//check current status so button toggles
$check = mysqli_prepare($conn,"select status from customers where customer_id=?");
mysqli_stmt_bind_param($check,"i",$customer_id);
mysqli_stmt_execute($check);
$res=mysqli_stmt_get_result($check);
$row = mysqli_fetch_assoc($res);
mysqli_stmt_close($check);
$new_status = ($row['status'] ==='active') ? 'suspended':'active';
$stmt = mysqli_prepare($conn, "update customers set status=? where customer_id=?");
mysqli_stmt_bind_param($stmt,"si",$new_status,$customer_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);
echo "<script>window.location.href='./index.php?view_customers';</script>";
exit;
?>