<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if(isset($_GET['delete_customer']))
    {
        if(empty($_SESSION['role']) || $_SESSION['role'] !== 'admin')
            {
                echo "<script>alert('Access denied. ')</script>";
                echo "<script>window.location.href='../Artisans/login.php';</script>";
                exit;
            }
        $customer_id = (int) $_GET['delete_customer'];
        $delete = "delete from customers where customer_id=?";
        $stmt = mysqli_prepare($conn,$delete);
        mysqli_stmt_bind_param($stmt,"i",$customer_id);
        $success = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        if($success)
                {
                    echo "<script>alert('Customer deleted successfully'); window.location.href='./index.php?view_customers'</script>";
                    
                }
                else{
                    echo "<script>alert('Something went wrong. Please try again.')</script>";
                }



    }
?>