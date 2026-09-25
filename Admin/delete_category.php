<?php
if(isset($_GET['delete_category'])){
    if(empty($_SESSION['role']) || $_SESSION['role'] !== 'admin')
            {
                echo "<script>alert('Access denied. ')</script>";
                echo "<script>window.location.href='../Artisans/login.php';</script>";
                exit;
            }
    if (!ctype_digit((string) $_GET['delete_category'])) {
        echo "<script>alert('Invalid category.')</script>";
        echo "<script>window.open('./index.php?view_categories','_self')</script>";
        exit;
    }
    $delete_cat = (int) $_GET['delete_category'];
    $delete_stmt = mysqli_prepare($conn, "delete from categories where category_id = ?");
    mysqli_stmt_bind_param($delete_stmt, "i", $delete_cat);
    $result = mysqli_stmt_execute($delete_stmt);
    mysqli_stmt_close($delete_stmt);
    if($result){
        echo "<script>alert('Category deleted successfully')</script>";
        echo "<script>window.open('./index.php?view_categories','_self')</script>";
    } else {
        echo "<script>alert('Failed to delete category')</script>";
        echo "<script>window.open('./index.php?view_categories','_self')</script>";
    }
}
?>