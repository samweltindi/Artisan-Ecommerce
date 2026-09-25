<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require('../database_connection.php');
if(isset($_GET['delete_products']))
    {
        $delete_id=$_GET['delete_products'];
        //Deletes product from the database
        $delete_product = "delete from products where product_id=$delete_id";
        $result_product = mysqli_query($conn,$delete_product);
        if($result_product)
            {
                echo "<script>alert('Deleted successfully')</script>";
                echo "<script>window.open('./index.php?view_products','_self')</script>";
            }
            else{
                echo "<script>alert('Failed to delete')</script>";
                echo "<script>window.open('./index.php?view_products','_self')</script>";
            }
    }
?>