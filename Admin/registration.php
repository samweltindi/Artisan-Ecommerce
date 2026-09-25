<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include('../database_connection.php');
include('../functions/sharedfunctions.php');

// This page creates admin accounts - it must never be reachable by anyone only registered admin
if (empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "<script>alert('Access denied.')</script>";
    echo "<script>window.location.href='../Artisans/login.php';</script>";
    exit;
}
//csrf token generation
csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer registration</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!--Css file-->
    <link rel="stylesheet" href="../styles.css">
    <style>
    *,*::before, *::after{
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}
body{
    font-family: system-ui, -apple-system, BlinkMacSystemFont,"Segoe UI",Roboto, Arial, Helvetica, sans-serif;
    background: #E8F0F3;
    color: #222;
    display:flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    overflow-x:hidden;

}
.regcontainer{
    background-color:white;
    padding: 40px 30px;
    border-radius: 16px;
    width: 100%;
    max-width:600px;


}
h2{
    text-align:center;
    margin-bottom:30px;
    font-size:28px;
    color:green;
}
.form-group{
    margin-bottom: 20px;
}
label{
    display:block;
    margin-bottom: 8px;
    font-weight:500;
    font-size: 14px;
}
input{
    width:100%;
    padding: 14px 16px;
    border: 2px solid #e1e1e1;
    border-radius: 8px;
    font-size:16px;

}
.submit-btn{
    width:100%;
    padding: 14px;
    color:white;
    background: #0E4D3C;
    cursor: pointer;
    border: none;
    margin-bottom: 20px;
}
    </style>
</head>
<body>
    <div class="regcontainer">
      <h2 class="textcenter">
        CREATE AN ACCOUNT(Admin)
      </h2>
      <div class="registration">
        <div class="">
            <form action="" method="post" enctype="multipart/form-data">
                <?php csrf_field(); ?>
                <div class="form-group">
                    <!--First name field-->
                    <label for="first_name" class="">Full Name</label>
                    <input type="text" id="fname" class="" placeholder="Full Name..." autocomplete="off" required="required" name="fname"/>

                </div>
                
                <div class="form-group">
                    <!--Last name field-->
                    <label for="username" class="">Create username</label>
                    <input type="text" id="username" class="" placeholder="Username..." autocomplete="off" required="required" name="username"/>

                </div>
                <div class="form-group">
                    <!--email field-->
                    <label for="email" class="">Email</label>
                    <input type="email" id="email" class="" name="email" placeholder="Email..." autocomplete="off"/>

                </div>
                <div class="form-group">
                    <!--Phone field-->
                    <label for="email" class="">Phone number</label>
                    <input type="text" id="phone" class="" name="phone" placeholder="Phone number..." autocomplete="off"/>

                </div>
                
                <div class="form-group">
                    <!--Password field-->
                    <label for="password" class="">Password</label>
                    <input type="password" id="password" class="" name="password" placeholder="Password..."/>

                </div>
                <div class="form-group">
                    <!--confirm password field-->
                    <label for="confirm_password" class="">Confirm Password</label>
                    <input type="password" id="confirm_password" class="" name="confirm_password" placeholder="Confirm Password..."/>

                </div>
                
                <div class="submit-login">
                    <input type="submit" value="Create Account" class="submit-btn" name="admin_register"/>
                    <p class="">Already have an account? <a href="../Artisans/login.php" class="">Login</a></p>
                </div>
            </form>

        </div>
      </div>
    </div>
</body>
</html>
<?php
if(isset($_POST['admin_register']))
    {
        //verify csrf token
        csrf_verify();
        $fname = trim($_POST['fname'] ?? '');
        $username= trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone_no = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $hashed_password = password_hash($password,PASSWORD_DEFAULT);
        //all field validation
        if(empty($fname) || empty($username) || empty($email) || empty($phone_no) || empty($password) || empty($confirm_password))
            {
                echo "<script>alert('Fill all fields!')</script>";
                //header("Location: registration.php");
                exit();
            }
        if(strlen($password) < 8) {
            echo "<script>alert('Password must be at least 8 characters')</script>";
            exit();
        }
        //Admin validation-check if email and username already exists
        $validation_query = "select * from admin where email = ? OR username=?";
        //$validation_result = mysqli_query($conn,$validation_query);
        $stmt = mysqli_prepare($conn,$validation_query);
        mysqli_stmt_bind_param($stmt,"ss",$email,$username);
        mysqli_stmt_execute($stmt);
        $validation_result=mysqli_stmt_get_result($stmt);
        $rows_count = mysqli_num_rows($validation_result);
        if($rows_count > 0)
            {
                $email_taken = false;
                $username_taken = false;
                while($row = mysqli_fetch_assoc($validation_result))
                    {
                        if($row['email']=== $email)
                            {
                                $email_taken = true;
                            } 
                        if($row['username']=== $username)
                            {
                                $username_taken = true;
                            }    
                    }
                    if($email_taken && $username_taken)
                        {
                        echo "<script>alert('Both email and username are already taken')</script>";
                        }
                    elseif($email_taken){
                        echo "<script>alert('Email is already taken')</script>";
                    } 
                    else{
                        echo "<script>alert('Username is already taken')</script>";
                    }    
                exit();
            }
            elseif($password != $confirm_password){
                echo "<script>alert('Passwords do not match')</script>";
                exit();

            }else{

        //Query to insert admin into database
        $admin_query = "insert into admin(full_name,username,email,phone_number,password) 
        values(?,?,?,?,?)";
        //$query_execution = mysqli_query($conn,$customer_query);
        $insert_stmt = mysqli_prepare($conn,$admin_query);
        mysqli_stmt_bind_param($insert_stmt,"sssss",$fname,$username,$email,$phone_no,$hashed_password);
        $reg_result= mysqli_stmt_execute($insert_stmt);
        if($reg_result)
            {
                echo "<script>alert('Registrations successful')</script>";
                echo "<script>window.open('../Artisans/login.php','_self')</script>";
            }
            }


    }
?>