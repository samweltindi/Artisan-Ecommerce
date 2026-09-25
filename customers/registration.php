<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include('../database_connection.php');
include('../functions/sharedfunctions.php');
@session_start();

// CSRF token (generate once per session, reused on every page load)
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
        CREATE AN ACCOUNT(customers only)
      </h2>
      <div class="registration">
        <div class="">
            <form action="" method="post" enctype="multipart/form-data">
               <?php csrf_field();?>
                <div class="form-group">
                    <!--First name field-->
                    <label for="first_name" class="">First Name</label>
                    <input type="text" id="first_name" class="" placeholder="First Name..." autocomplete="off" required="required" name="firstname" maxlength="50"/>

                </div>
                <div class="form-group">
                    <!--Last name field-->
                    <label for="last_name" class="">Last Name</label>
                    <input type="text" id="last_name" class="" placeholder="Last Name..." autocomplete="off" required="required" name="lastname"/>

                </div>
                <div class="form-group">
                    <!--Last name field-->
                    <label for="username" class="">Create username</label>
                    <input type="text" id="username" class="" placeholder="Username..." autocomplete="off" required="required" name="username"/>

                </div>
                <div class="form-group">
                    <!--email field-->
                    <label for="email" class="">Email</label>
                    <input type="email" id="email" class="" name="email" placeholder="Email..." autocomplete="off" required="required"/>

                </div>
                <div class="form-group">
                    <!--Phone field-->
                    <label for="email" class="">Phone number</label>
                    <input type="text" id="phone" class="" name="phone" placeholder="Phone number..." autocomplete="off" required="required"/>

                </div>
                <div class="form-group">
                    <!--address field-->
                    <label for="address" class="">Address</label>
                    <input type="text" id="address" class="" name="address" placeholder="Address..." autocomplete="off" required="required"/>

                </div>
                <div class="form-group">
                    <!--Password field-->
                    <label for="password" class="">Password</label>
                    <input type="password" id="password" class="" name="password" placeholder="Password..." required="required"/>

                </div>
                <div class="form-group">
                    <!--confirm password field-->
                    <label for="confirm_password" class="">Confirm Password</label>
                    <input type="password" id="confirm_password" class="" name="confirm_password" placeholder="Confirm Password..." required="required"/>

                </div>
                
                <div class="submit-login">
                    <input type="submit" value="Create Account" class="submit-btn" name="customer_register"/>
                    <p class="">Already have an account? <a href="login.php" class="">Login</a></p>
                </div>
            </form>

        </div>
      </div>
    </div>
</body>
</html>
<?php
if(isset($_POST['customer_register']))
    {
        // CSRF check
        if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            echo "<script>alert('Invalid session. Please refresh the page and try again.')</script>";
            exit();
        }

        // Trim all text input first
        $fname = trim($_POST['firstname'] ?? '');
        $lname = trim($_POST['lastname'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone_no = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $user_ip = getIPAddress();

        //all field validation - required
        if(empty($fname) || empty($lname) || empty($username) || empty($email) || empty($phone_no) || empty($address) || empty($password) || empty($confirm_password))
            {
                echo "<script>alert('Fill all fields!')</script>";
                header("Location: registration.php");
                exit();
            }

        // Length caps - stop oversized/abusive input before it reaches the DB
        if (strlen($fname) > 50 || strlen($lname) > 50 || strlen($username) > 50
            || strlen($email) > 100 || strlen($address) > 255) {
            echo "<script>alert('One or more fields exceed the allowed length.')</script>";
            exit();
        }

        // Name fields: letters, spaces, hyphens, apostrophes only
        if (!preg_match("/^[A-Za-z\s'\-]+$/", $fname) || !preg_match("/^[A-Za-z\s'\-]+$/", $lname)) {
            echo "<script>alert('Names may only contain letters, spaces, and hyphens.')</script>";
            exit();
        }

        // Username: letters, numbers, underscore, dot, dash only (no spaces/special chars)
        if (!preg_match('/^[A-Za-z0-9_.\-]{3,50}$/', $username)) {
            echo "<script>alert('Username must be 3-50 characters: letters, numbers, underscore, dot, or dash only.')</script>";
            exit();
        }

        // Email: proper format via filter_var, not just non-empty
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo "<script>alert('Please enter a valid email address.')</script>";
            exit();
        }

        // Phone: normalize to Kenyan 2547XXXXXXXX / 2541XXXXXXXX format used by M-Pesa
        $phone_digits = preg_replace('/\D/', '', $phone_no); // strip spaces, +, dashes etc.
        if (preg_match('/^0(7|1)\d{8}$/', $phone_digits)) {
            $phone_no = '254' . substr($phone_digits, 1);
        } elseif (preg_match('/^254(7|1)\d{8}$/', $phone_digits)) {
            $phone_no = $phone_digits;
        } else {
            echo "<script>alert('Enter a valid Kenyan phone number, e.g. 0712345678.')</script>";
            exit();
        }

        // Password strength: at least 8 characters, one letter, one number
        if (strlen($password) < 8 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) {
            echo "<script>alert('Password must be at least 8 characters and include a letter and a number.')</script>";
            exit();
        }
        if ($password !== $confirm_password) {
            echo "<script>alert('Passwords do not match')</script>";
            exit();
        }

        $hashed_password = password_hash($password,PASSWORD_DEFAULT);

        //customer validation-check if email and username already exists
        $validation_query = "select * from customers where email = ? OR username=?";
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
            else{

        //Query to insert customer into database
        $customer_query = "insert into customers(first_name,last_name,username,email,phone_number,address,user_ip,password) 
        values(?,?,?,?,?,?,?,?)";
        //$query_execution = mysqli_query($conn,$customer_query);
        $insert_stmt = mysqli_prepare($conn,$customer_query);
        mysqli_stmt_bind_param($insert_stmt,"ssssssss",$fname,$lname,$username,$email,$phone_no,$address,$user_ip,$hashed_password);
        $success_creation = mysqli_stmt_execute($insert_stmt);
        if($success_creation){
            echo "<script>alert('Account created successfully.');</script>";
        }
            }
            //selecting cart items
        $select_cart_items="select * from cart_items where ip_address= ? ";
        //$result_cart = mysqli_query($conn,$select_cart_items);
        $stmt = mysqli_prepare($conn,$select_cart_items);
        mysqli_stmt_bind_param($stmt,"s",$user_ip);
        mysqli_stmt_execute($stmt);
        $result_cart = mysqli_stmt_get_result($stmt);
        
        //counting the number of rows
        $rows_count = mysqli_num_rows($result_cart); 
        if($rows_count>0)
            {
                $username = $_SESSION['username'];
                echo "<script>alert('You have items in your cart')</script>";
                //opening checkout.php in the same window
                echo "<script>window.open('checkout.php','_self')</script>";
            }
            else{
                echo "<script>window.open('../index.php','_self')</script>";
            }


    }
?>