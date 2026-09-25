<?php
//blocks unauthorized access
if (empty($_SESSION['username'])) {
    echo "<script>alert('Please log in first.')</script>";
    echo "<script>window.open('login.php','_self')</script>";
    exit;
}
//generating csrf if non-exists
csrf_token();
$username = $_SESSION['username'];

if(isset($_POST['delete']))
    {
       //csrf check
       csrf_verify();
        $delete_stmt = mysqli_prepare($conn, "delete from customers where username = ?");
        mysqli_stmt_bind_param($delete_stmt, "s", $username);
        $result = mysqli_stmt_execute($delete_stmt);
        mysqli_stmt_close($delete_stmt);
        if($result)
            {
                session_destroy();
                echo "<script>alert('Account Deleted successfully')</script>";
                echo "<script>window.open('../index.php','_self')</script>";
                exit;
            }
    }
if(isset($_POST['dont_delete']))
    {
        echo "<script>window.open('./profile.php','_self')</script>";
        exit;
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deleting account</title>
    <style>
    .div1{
        max-width: 700px;
        margin: 30px auto;
        padding: 20px;
        }
    .form1{
        display: flex;
        flex-direction: column;
        gap: 20px;
        
    }
        .form1 input[type="submit"]{
        background-color: #73d477;
        color: white;
        border: none;
        cursor: pointer;
        transition: background-color 0.3s ease;
        padding: 10px;
        width: 100%;
        align-self: center;
        padding: 10px;
    }
    .form1 input[type="submit"]:hover{
        background-color: #0cc015;
    }
    </style>
</head>
<body>
    <div class="div1">
    <h3 style="color: red; text-align: center; margin-top: 20px;">
        Delete Account
    </h3>
    <form action="" method="post" class="form1">
        <?php csrf_field();?>
        <div>
            <input type="submit" name="delete" value="Delete Account">
        </div>
        <div>
            <input type="submit" name="dont_delete" value="Don't Delete Account">
        </div>
    </form>
    </div>
</body>
</html>