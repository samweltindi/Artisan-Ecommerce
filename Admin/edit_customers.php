<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$edit_id = 0;
if(empty($_SESSION['role']) || $_SESSION['role'] !== 'admin')
            {
                echo "<script>alert('Access denied. ')</script>";
                echo "<script>window.location.href='../Artisans/login.php';</script>";
                exit;
            }
if(empty($_SESSION['csrf_token']))
    {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }            
if(isset($_GET['edit_customers']))
    {
        $edit_id = (int) $_GET['edit_customers'];
        $get_data ="select * from customers where customer_id=?";
        $stmt = mysqli_prepare($conn,$get_data);
        mysqli_stmt_bind_param($stmt,"i",$edit_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        $first_name = $row['first_name'];
        $last_name = $row['last_name'];
        $username = $row['username'];
        $email = $row['email'];
        $phone_number = $row['phone_number'];
        $address = $row['address'];

    }
?>
<style>
.products-card{
    max-width: 600px;
    margin: 20px auto;
    padding: 40px;
    background: #ffffff;
    border-radius: 14px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    border: 1px solid #eef1f4;
}
.products-card h1{
    font-size: 1.5rem;
    font-weight: 700;
    color: #1c2b3a;
    text-align: left;
    border-bottom: 1px solid #eef1f4;
    padding-bottom: 16px;
    margin-bottom: 24px;
}
.form1{
    display: flex;
    flex-direction: column;
    gap: 20px;
}
.form-label{
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
    font-weight: 600;
    color: #4a5568;
    display: inline-block;
}
.form1 input{
    width: 100%;
    padding: 12px 14px;
    border: 1px solid #dfe3e8;
    border-radius: 8px;
    background-color: #fbfbfc;
    color:#2f3b4c;
    font-size: 15px;
}
.form1 input:focus{
    outline: none;
    border-color: #4CAF50;
    box-shadow: 0 0 0 3px rgba(53,199,201, 0.2);
    background-color: #ffffff;
}
.form1 input[type="submit"]{
    background-color: #0cc012;
    color: white;
    border: none;
    cursor: pointer;
    padding: 12px;
    width: 50%;
    align-self: center;
    border-radius: 8px;
    font-weight: 600;
    margin-top: 10px;
}
.form1 input[type="submit"]:hover{
    background-color: #0aa60f;
}
</style>
<div class="products-card">
<h1>Edit Customer</h1>
<form action="" method="post" enctype="multipart/form-data" class="form1">
        <div>
        <label for="first_name" class="form-label">
        First Name
        </label>
        <input type="text" id="first_name" value="<?php echo htmlspecialchars($first_name); ?>" name="first_name" class="form-control" required="required">
        </div>
        <div>
        <label for="last_name" class="form-label">
        Last Name
        </label>
        <input type="text" id="last_name" value="<?php echo htmlspecialchars($last_name); ?>" name="last_name" class="form-control" required="required">
        </div>
        <div>
        <label for="username" class="form-label">
        Username
        </label>
        <input type="text" id="username" value="<?php echo htmlspecialchars($username); ?>" name="username" class="form-control" required="required">
        </div>
        <div>
        <label for="email" class="form-label">
        Email
        </label>
        <input type="email" id="email" value="<?php echo htmlspecialchars($email); ?>" name="email" class="form-control" required="required">
        </div>
        <div>
        <label for="phone_number" class="form-label">
        Phone Number
        </label>
        <input type="text" id="phone_number" value="<?php echo htmlspecialchars($phone_number); ?>" name="phone_number" class="form-control" required="required">
        </div>
        <div>
        <label for="address" class="form-label">
        Address
        </label>
        <input type="text" id="address" value="<?php echo htmlspecialchars($address); ?>" name="address" class="form-control" required="required">
        </div>

    <div>
        <input type="submit" name="edit_customers" value="Update Customer">
    </div>

</form>
</div>
<?php
if(isset($_POST['edit_customers']))
    {
        $first_name = $_POST['first_name'];
        $last_name =$_POST['last_name'];
        $username = $_POST['username'];
        $email = $_POST['email'];
        $phone_number = $_POST['phone_number'];
        $address = $_POST['address'];
        $edit_id = (int) $_GET['edit_customers'];

        //field validation
        if(empty($first_name) or empty($last_name) or empty($username) or empty($email) or empty($phone_number) or empty($address)){
            echo "<script>alert('Please fill all fields')</script>";
        }else{
           $update_customer="update customers set first_name=?, last_name=?,username=?, phone_number=?,email=?,address=? where customer_id=?";
           $stmt = mysqli_prepare($conn,$update_customer);
           mysqli_stmt_bind_param($stmt,"ssssssi",$first_name,$last_name,$username,$phone_number,$email,$address,$edit_id);
           $success=mysqli_stmt_execute($stmt);
           mysqli_stmt_close($stmt);
            if($success)
                {
                    echo "<script>alert('Customer details updated successfully'); window.location.href='./index.php?view_customers'</script>";
                }
                else{
                    echo "<script>alert('Something went wrong. Please try again.')</script>";
                }

        }


    }
?>