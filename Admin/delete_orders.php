<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if(empty($_SESSION['role']) || $_SESSION['role'] !== 'admin')
            {
                echo "<script>alert('Access denied. ')</script>";
                echo "<script>window.location.href='../Artisans/login.php';</script>";
                exit;
            }
if(isset($_GET['delete_orders'])){
    if (!ctype_digit((string) $_GET['delete_orders'])) {
        echo "<script>alert('Invalid order.')</script>";
        echo "<script>window.open('./index.php?orders','_self')</script>";
        exit;
    }
    $delete_id = (int) $_GET['delete_orders'];
    $delete_stmt = mysqli_prepare($conn, "delete from orders where order_id = ?");
    mysqli_stmt_bind_param($delete_stmt, "i", $delete_id);
    $result = mysqli_stmt_execute($delete_stmt);
    mysqli_stmt_close($delete_stmt);
    if($result)
        {
            echo "<script>alert('Order Deleted successfully')</script>";
            echo "<script>window.open('./index.php?orders','_self')</script>";
        }
        else{
            echo "<script>alert('Failed to delete order')</script>";
            echo "<script>window.open('./index.php?orders','_self')</script>";
        }
}
?>