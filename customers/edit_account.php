<?php

if(isset($_GET['edit_account']))
    {
        // Must be logged in - block access otherwise
        if (empty($_SESSION['username'])) {
            echo "<script>alert('Please log in first.')</script>";
            echo "<script>window.open('login.php','_self')</script>";
            exit;
        }
        //csrf token genrated
        csrf_token();
        $customer_session = $_SESSION['username'];
        $select_query = "select * from customers where username = ?";
        $stmt = mysqli_prepare($conn, $select_query);
        mysqli_stmt_bind_param($stmt, "s", $customer_session);
        mysqli_stmt_execute($stmt);
        $result_query = mysqli_stmt_get_result($stmt);
        $row_fetch = mysqli_fetch_assoc($result_query);

        // Bail out if the session username no longer matches a real account
        if (!$row_fetch) {
            echo "<script>alert('Account not found.')</script>";
            echo "<script>window.open('logout.php','_self')</script>";
            exit;
        }

        $customer_id = $row_fetch['customer_id'];
        $first_name= $row_fetch['first_name'];
        $last_name = $row_fetch['last_name'];
        $user_name = $row_fetch['username'];
        $user_email = $row_fetch['email'];
        $address = $row_fetch['address'];
        $phone = $row_fetch['phone_number'];

        // CSRF token - generate once per session
        csrf_token();

        if(isset($_POST['user_update'])){
            // CSRF check
          csrf_verify();
            //assigning new names to the variable.
            $update_id = $customer_id; // from the session-verified row, never from client input
            $new_first_name = trim($_POST['first_name'] ?? '');
            $new_last_name = trim($_POST['last_name'] ?? '');
            $new_username = trim($_POST['username'] ?? '');
            $new_email = trim($_POST['email'] ?? '');
            $new_address = trim($_POST['address'] ?? '');
            $new_phone = trim($_POST['phone_number'] ?? '');

            $errors = [];

            if ($new_first_name === '' || $new_last_name === '' || $new_username === '' || $new_email === '' || $new_address === '' || $new_phone === '') {
                $errors[] = 'All fields are required.';
            }
            if (strlen($new_first_name) > 50 || strlen($new_last_name) > 50 || strlen($new_username) > 50
                || strlen($new_email) > 100 || strlen($new_address) > 255) {
                $errors[] = 'One or more fields exceed the allowed length.';
            }
            if (!preg_match("/^[A-Za-z\s'\-]+$/", $new_first_name) || !preg_match("/^[A-Za-z\s'\-]+$/", $new_last_name)) {
                $errors[] = 'Names may only contain letters, spaces, and hyphens.';
            }
            if (!preg_match('/^[A-Za-z0-9_.\-]{3,50}$/', $new_username)) {
                $errors[] = 'Username must be 3-50 characters: letters, numbers, underscore, dot, or dash only.';
            }
            $new_email = filter_var($new_email, FILTER_SANITIZE_EMAIL);
            if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Please enter a valid email address.';
            }
            $phone_digits = preg_replace('/\D/', '', $new_phone);
            if (preg_match('/^0(7|1)\d{8}$/', $phone_digits)) {
                $new_phone = '254' . substr($phone_digits, 1);
            } elseif (preg_match('/^254(7|1)\d{8}$/', $phone_digits)) {
                $new_phone = $phone_digits;
            } else {
                $errors[] = 'Enter a valid Kenyan phone number, e.g. 0712345678.';
            }
                //Shows all errors in one alert
            if (!empty($errors)) {
                echo "<script>alert('" . addslashes(implode(' ', $errors)) . "')</script>";
                // keep the values the user typed so the form isn't wiped
                $first_name = $new_first_name;
                $last_name = $new_last_name;
                $user_name = $new_username;
                $user_email = $new_email;
                $address = $new_address;
                $phone = $new_phone;
            } else {
                // Check username/email aren't already taken by a different account
                $check_query = "select customer_id from customers where (email = ? or username = ?) and customer_id != ?";
                $check_stmt = mysqli_prepare($conn, $check_query);
                mysqli_stmt_bind_param($check_stmt, "ssi", $new_email, $new_username, $update_id);
                mysqli_stmt_execute($check_stmt);
                $check_result = mysqli_stmt_get_result($check_stmt);

                if (mysqli_num_rows($check_result) > 0) {
                    echo "<script>alert('That username or email is already in use.')</script>";
                    $first_name = $new_first_name;
                    $last_name = $new_last_name;
                    $user_name = $new_username;
                    $user_email = $new_email;
                    $address = $new_address;
                    $phone = $new_phone;
                } else {
                    $update_data = "update customers set first_name=?, last_name=?, username=?, email=?, address=?, phone_number=? where customer_id=?";
                    $update_stmt = mysqli_prepare($conn, $update_data);
                    mysqli_stmt_bind_param($update_stmt, "ssssssi", $new_first_name, $new_last_name, $new_username, $new_email, $new_address, $new_phone, $update_id);
                    $result_query_update = mysqli_stmt_execute($update_stmt);

                    if($result_query_update)
                        {
                            // username may have changed - keep session in sync
                            $_SESSION['username'] = $new_username;
                            echo "<script>alert('Account updated successfully')</script>";
                            echo "<script>window.open('profile.php','_self')</script>";
                        }
                }
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit account</title>
    <style>
    
    .div1{
        max-width: 700px;
        margin: 20px auto;
        padding: 40px;
        background: #ffffff;
        border-radius: 8px;
        border: 1px solid #eef1f4;
        }
    .div1 h3{
        font-size: 1.5rem;
        font-weight: 700;
        color: #1c2b3a;
        border-bottom: 1px solid #eef1f4;
        padding-bottom: 16px;
        margin-bottom: 24px !important;
    }
    .form1{
        display: flex;
        flex-direction: column;
        gap: 20px;
        
    }
    .form1 input{
        width: 100%;
        padding: 12px 14px;
        border: 1px solid #dfe3e8;
        border-radius: 8px;
        background-color: #fbfbfc;
        color:#2f3b4c;
        font-size: 15px;
        transition: border-color 0.3s ease, box-shadow 0.3s ease;
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
        transition: background-color 0.3s ease;
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
  
    .form-label{
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
        font-weight: 600;
        color: #4a5568;
        line-height: 1.5;
        display: inline-block;
    }
        </style>
</head>
<body>
    <div class="div1">
    <h3>Edit Account</h3>
    <form action="" method="post" enctype="multipart/form-data"  class="form1">
        <?php csrf_field() ?>
        <div>
            <label for="product_price" class="form-label">
             First Name
            </label>
            <input type="text" value="<?php echo htmlspecialchars($first_name);?>" name="first_name" maxlength="50">
        </div>
        <div>
            <label for="product_price" class="form-label">
             Last Name
            </label>
            <input type="text" value="<?php echo htmlspecialchars($last_name);?>" name="last_name" maxlength="50">
        </div>
        <!--<div class="form-outline mb-4">
            <input type="file" name="user_image" class="form-control w-50 m-auto">
        <img src="./user_image/" alt="">
        </div>-->
        <div>
            <label for="product_price" class="form-label">
             Username
            </label>
            <input type="text" value="<?php echo htmlspecialchars($user_name);?>" name="username" maxlength="50">
        </div>
        <div>
            <label for="product_price" class="form-label">
             Email
            </label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($user_email);?>" maxlength="100">
        </div>
        <div>
            <label for="product_price" class="form-label">
             Phone number
            </label>
            <input type="tel" name="phone_number" value="<?php echo htmlspecialchars($phone);?>">

        </div>
        <div>
            <label for="product_price" class="form-label">
             Address
            </label>
            <input type="text" name="address" value="<?php echo htmlspecialchars($address);?>" maxlength="255">

        </div>
        <input type="submit" value="Update" name="user_update">
    </form>
    </div>
</body>
</html>