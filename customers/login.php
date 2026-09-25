<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include('../database_connection.php');
include('../functions/sharedfunctions.php');
//suppresses warnings-even if a session is somehow active
@session_start();

// CSRF token (generate once per session, reused on every page load)
csrf_token();

// Simple login rate limiting (per session) - slows down brute force guessing
if (!isset($_SESSION['login_attempts'])) {
    //if tracking keys aren't in the session, initialize them
    $_SESSION['login_attempts'] = 0;
    $_SESSION['login_lockout_until'] = 0;
}
$login_locked = ($_SESSION['login_lockout_until'] > time());
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Login</title>
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
.login_container{
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
    color: green;
}
h4{
    text-align:center;
    margin-bottom: 30px;
    font-size:20px;
    color:#333;
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
    <div class="login_container">
      <h2 class="textcenter">
        WELCOME
      </h2>
      <h4 style="color:green;">
        Login to your account (customers only)
      </h4>
      <div class="registration">
        <div class="">
            <form action="" method="post" enctype="multipart/form-data">
                <?php csrf_field(); ?>
                <?php if ($login_locked): ?>
                    <p style="color:red;">Too many failed attempts. Please try again in a minute.</p>
                <?php endif; ?>
                <div class="form-group">
                    <!--email field-->
                    <label for="username" class="">Username</label>
                    <input type="text" id="username" class="" name="username" placeholder="Username..." 
                    autocomplete="off" maxlength="50" required/>

                </div>
                <div class="form-group">
                    <!--Password field-->
                    <label for="password" class="">Password</label>
                    <input type="password" id="password" class="" name="password" 
                    placeholder="Password..." required minlength="8" maxlength="255" 
                    title="Password must be between 8 and 255 characters."/>

                </div>
                <div class="submit-login">
                    <input type="submit" value="Login" class="submit-btn" name="customer_login"/>
                    <p class="">Don't have an account? <a href="registration.php" class="">Register</a></p>
                </div>
            </form>

        </div>
      </div>
    </div>
</body>
</html>
<?php
if(isset($_POST['customer_login']))
    {
        // CSRF check - reject if token missing or doesn't match session
       csrf_verify();

        // Rate limit check - block if locked out
        if ($_SESSION['login_lockout_until'] > time()) {
            $wait = $_SESSION['login_lockout_until'] - time();
            echo "<script>alert('Too many failed attempts. Try again in {$wait} seconds.')</script>";
            exit;
        }

        // Trim and validate raw input before anything else
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === '' || $password === '') {
            echo "<script>alert('Please enter both username and password.')</script>";
            exit;
        }
        // Reasonable length caps stop abuse 
        if (strlen($username) > 50 || strlen($password) > 255) {
            echo "<script>alert('Invalid input.')</script>";
            exit;
        }
        // Username allow-list: letters, numbers, underscore, dot, dash only
        if (!preg_match('/^[A-Za-z0-9_.\-]+$/', $username)) {
            echo "<script>alert('Wrong username or password!')</script>";
            exit;
        }

        // Prepared statement - no more string interpolation into SQL
        $select_query = "select * from customers where username = ?";
        $stmt = mysqli_prepare($conn, $select_query);
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row_count = mysqli_num_rows($result);
        $row_data = mysqli_fetch_assoc($result);

        $user_ip = getIPAddress();
        //cart item
        $select_query_cart = "select * from cart_items where ip_address = ?";
        $stmt_cart = mysqli_prepare($conn, $select_query_cart);
        mysqli_stmt_bind_param($stmt_cart, "s", $user_ip);
        mysqli_stmt_execute($stmt_cart);
        $select_cart = mysqli_stmt_get_result($stmt_cart);
        $row_count_cart = mysqli_num_rows($select_cart);

        if($row_count > 0 && password_verify($password, $row_data['password']))
            {
                // a suspended account should never end up "logged in" even
                if($row_data['status'] === 'suspended')
                    {
                        $_SESSION['login_attempts'] = 0;
                        echo "<script>alert('Your account has been suspended. Please contact support.')</script>";
                        echo "<script>window.open('index.php')</script>";
                        exit;
                    }

                // Successful login: reset attempts, regenerate session ID to prevent fixation
                $_SESSION['login_attempts'] = 0;
                session_regenerate_id(true);
                $_SESSION['username'] = $username;
                // Re-issue CSRF token after regenerating the session
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

                if($row_count == 1 and $row_count_cart ==0)
                    {
                        echo "<script>alert('login successfully')</script>";
                        echo "<script>window.open('profile.php','_self')</script>";
                    }else{
                        // M-Pesa only now - send straight into the checkout flo
                        echo "<script>alert('login successfully')</script>";
                        echo "<script>window.open('delivery_address.php','_self')</script>";

                    }

            }else{
                // Track failed attempts; lock out after 5 within a session
                $_SESSION['login_attempts']++;
                if ($_SESSION['login_attempts'] >= 5) {
                    $_SESSION['login_lockout_until'] = time() + 60; // 60 second lockout
                    $_SESSION['login_attempts'] = 0;
                }
                echo "<script>alert('Wrong username or password!')</script>";
        }
    }

?>