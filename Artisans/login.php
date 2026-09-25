<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include('../database_connection.php');
include('../functions/sharedfunctions.php');
@session_start();

// CSRF token
csrf_token();

// Simple login rate limiting (per session) - blocks brute force
if (!isset($_SESSION['login_attempts'])) {
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
    <title>Artisan Login</title>
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
    <div class="login_container">
      <h2 class="textcenter">
        WELCOME
      </h2>
      <h4>
        Login to your seller account
      </h4>
      <div class="registration">
        <div class="">
            <form action="" method="post" enctype="multipart/form-data">
                <?php csrf_field();?>
                <?php if ($login_locked): ?>
                    <p style="color:red;">Too many failed attempts. Please try again in a minute.</p>
                <?php endif; ?>
                <div class="form-group">
                    <!--email field-->
                    <label for="username" class="">Username</label>
                    <input type="text" id="username" class="" name="username" placeholder="Username..." autocomplete="off" maxlength="50"/>

                </div>
                <div class="form-group">
                    <!--Password field-->
                    <label for="password" class="">Password</label>
                    <input type="password" id="password" class="" name="password" placeholder="Password..."/>

                </div>
                <div class="submit-login">
                    <input type="submit" value="Login" class="submit-btn" name="artisan_login"/>
                    <p class="">Don't have an account? <a href="registration.php" class="">Register</a></p>
                </div>
            </form>

        </div>
      </div>
    </div>
</body>
</html>
<?php
    if(isset($_POST['artisan_login']))
        {
            // CSRF check
            csrf_verify();

            // Rate limit check
            if ($_SESSION['login_lockout_until'] > time()) {
                $wait = $_SESSION['login_lockout_until'] - time();
                echo "<script>alert('Too many failed attempts. Try again in {$wait} seconds.')</script>";
                exit();
            }

            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            if ($username === '' || $password === '') {
                echo "<script>alert('Please enter both username and password.')</script>";
                exit();
            }
            if (strlen($username) > 50 || strlen($password) > 255) {
                echo "<script>alert('Invalid input.')</script>";
                exit();
            }
            if (!preg_match('/^[A-Za-z0-9_.\-]+$/', $username)) {
                echo "<script>alert('Wrong username or password!')</script>";
                exit();
            }

            // table => [redirect page, session role to assign]
            $tables = [
                'admin'    => ['redirect' => '../Admin/index.php', 'role' => 'admin'],
                'artisans' => ['redirect' => './index.php',        'role' => 'artisan'],
            ];
            $found = false;

            foreach ($tables as $table => $info) {
                $select_query = "select * from $table where username = ?";
                $stmt = mysqli_prepare($conn, $select_query);
                mysqli_stmt_bind_param($stmt, "s", $username);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $row_count = mysqli_num_rows($result);

                if ($row_count > 0) {
                    $found = true;
                    $row_data = mysqli_fetch_assoc($result);

                    if (password_verify($password, $row_data['password'])) {
                        if ($table === 'artisans') {
                            if ($row_data['status'] === 'suspended') {
                                echo "<script>alert('Your account has been suspended. Please contact support.');
                                window.location.href='../index.php';</script>";
                                mysqli_stmt_close($stmt);
                                exit();
                            }
                            if ($row_data['approved'] != 1) {
                                echo "<script>alert('Your account is pending admin approval. Please check back later');
                                window.location.href='../index.php';</script>";
                                mysqli_stmt_close($stmt);
                                exit();
                            }
                        }

                        // Success: reset attempts, regenerate session, set identity + role
                        $_SESSION['login_attempts'] = 0;
                        session_regenerate_id(true);
                        $_SESSION['username'] = $username;
                        $_SESSION['role'] = $info['role'];
                        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

                        mysqli_stmt_close($stmt);
                        echo "<script>alert('Login successfully');window.open('{$info['redirect']}','_self');</script>";
                        exit();
                    }
                }
                mysqli_stmt_close($stmt);
            }

            // Reached only if no table matched, or password was wrong -
            // same generic message either way so usernames can't be enumerated
            $_SESSION['login_attempts']++;
            if ($_SESSION['login_attempts'] >= 5) {
                $_SESSION['login_lockout_until'] = time() + 60;
                $_SESSION['login_attempts'] = 0;
            }
            echo "<script>alert('Wrong username or password!')</script>";
        }
?>