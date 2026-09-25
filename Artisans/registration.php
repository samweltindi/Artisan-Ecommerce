<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include('../database_connection.php');
include('../functions/sharedfunctions.php');
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
h4{
    margin-bottom: 10px;
}

    </style>
</head>
<body>
    <div class="regcontainer">
      <h2 class="textcenter">
        REGISTER YOUR SHOP(Artisans)
      </h2>
      <div class="registration">
        <div class="">
            <form action="" method="post" enctype="multipart/form-data">
                <input type="hidden" name="MAX_FILE_SIZE" value="2097152">
                <h4>Personal Information</h4>
                <div class="form-group">
                    <!--First name field-->
                    <label for="full_name" class="">Full Name</label>
                    <input type="text" id="full_name" class="" placeholder="Full Name..." autocomplete="off" required="required" name="fullname"/>

                </div>
                <div class="form-group">
                    <!--username field-->
                    <label for="last_name" class="">Username</label>
                    <input type="text" id="username" class="" placeholder="Username..." autocomplete="off" required="required" name="username"/>

                </div>
                <div class="form-group">
                    <!--email field-->
                    <label for="email" class="">Email</label>
                    <input type="email" id="email" class="" name="email" placeholder="Email..." autocomplete="off" required="required"/>

                </div>
                <div class="form-group">
                    <!--Phone field-->
                    <label for="phone" class="">Phone number</label>
                    <input type="text" id="phone" class="" name="phone" placeholder="Phone number..." autocomplete="off" required="required"/>

                </div>
                <div class="form-group">
                    <!--Password field-->
                    <label for="password" class="">Password</label>
                    <input type="password" id="password" class="" name="password" placeholder="password..." minlength="8" required="required"/>

                </div>
                <div class="form-group">
                    <!--confirm password field-->
                    <label for="confirm_password" class="">Confirm Password</label>
                    <input type="password" id="confirm_password" class="" name="confirm_password" placeholder="confirm password..." minlength="8" required="required"/>

                </div>
                <h4>Basic Information</h4>
                <div class="form-group">
                    <!--Location field-->
                    <label for="location" class="">Location</label>
                    <input type="text" id="locaction" class="" name="location" placeholder="Location..." autocomplete="off" required="required"/>

                </div>
                <div class="form-group">
                    <!--ID field-->
                    <label for="id_no" class="">National ID or Passport Number</label>
                    <input type="text" id="id_no" class="" name="id_no" placeholder="ID number..." autocomplete="off" required="required"/>

                </div>
                <div class="form-group">
                    <!--KRA pin field-->
                    <label for="kra" class="">KRA pin number</label>
                    <input type="text" id="kra" class="" name="kra" placeholder="KRA pin number..." autocomplete="off" required="required"/>
                </div>
                <h4>Business Registration Details</h4>
                <div class="form-group">
                    <!--Business name field-->
                    <label for="business_name" class="">Business name/ Shop name</label>
                    <input type="text" id="business_name" class="" name="business_name" placeholder="Shop name..." required="required"/>
                </div>
                <div class="form-group">
                    <!--Business registration field-->
                    <label for="business_certicate" class="">Upload Business Registration Certificate (image, max 2MB)</label>
                    <input type="file" id="business_certicate" class="" name="business_certificate" accept="image/jpeg,image/png,image/webp" required="required"/>
                </div>
                <div class="form-group">
                    <!--Business permit field-->
                    <label for="business_permit" class="">Upload Business Permit or Single Business Permit (image, max 2MB)</label>
                    <input type="file" id="business_permit" class="" name="business_permit" accept="image/jpeg,image/png,image/webp" required="required"/>
                </div>
                <div class="form-group">
                    <!--KEBS certificate field-->
                    <label for="business_kebs" class="">Upload KEBS certificate (image, max 2MB)</label>
                    <input type="file" id="business_kebs" class="" name="business_kebs" accept="image/jpeg,image/png,image/webp" required="required"/>
                </div>
                <h4>Payment Details</h4>
                <div class="form-group">
                    <!-- Payment details field-->
                    <label for="bank" class="">Enter Your Bank account Number</label>
                    <input type="text" id="bank" class="" name="bank" placeholder="Bank number..." required="required"/>
                </div>
                <div class="submit-login">
                    <input type="submit" value="Register Your Shop" class="submit-btn" name="artisans_register"/>
                    <p class="">Already have an account? <a href="login.php" class="">Login</a></p>
                </div>
                

            </form>

        </div>
      </div>
    </div>
</body>
</html>
<?php
if(isset($_POST['artisans_register']))
    {
        // ---- Collect input ----
        $fname          = trim($_POST['fullname'] ?? '');
        $email          = trim($_POST['email'] ?? '');
        $username       = trim($_POST['username'] ?? '');
        $phone_no       = trim($_POST['phone'] ?? '');
        $location       = trim($_POST['location'] ?? '');
        $password       = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $bname          = trim($_POST['business_name'] ?? '');
        $id_number      = trim($_POST['id_no'] ?? '');
        $kra_no         = trim($_POST['kra'] ?? '');
        $bank_no        = trim($_POST['bank'] ?? '');

        // ---- File upload check helper values (size and type only) ----
        $max_file_size = 2 * 1024 * 1024; // 2MB
        $allowed_mimes = ['image/jpeg', 'image/png', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);

        $cert_mime   = isset($_FILES['business_certificate']) ? finfo_file($finfo, $_FILES['business_certificate']['tmp_name']) : null;
        $permit_mime = isset($_FILES['business_permit']) ? finfo_file($finfo, $_FILES['business_permit']['tmp_name']) : null;
        $kebs_mime   = isset($_FILES['business_kebs']) ? finfo_file($finfo, $_FILES['business_kebs']['tmp_name']) : null;
        finfo_close($finfo);

        //customer validation
        $validation_query = "select * from artisans where username = ?";
        $validation_stmt = mysqli_prepare($conn, $validation_query);
        mysqli_stmt_bind_param($validation_stmt, "s", $username);
        mysqli_stmt_execute($validation_stmt);
        mysqli_stmt_store_result($validation_stmt);
        $rows_count = mysqli_stmt_num_rows($validation_stmt);
        mysqli_stmt_close($validation_stmt);

        // duplicate artisan check (same ID number or KRA pin already registered)
        $dup_query = "select * from artisans where id_number = ? or kra = ?";
        $dup_stmt = mysqli_prepare($conn, $dup_query);
        mysqli_stmt_bind_param($dup_stmt, "ss", $id_number, $kra_no);
        mysqli_stmt_execute($dup_stmt);
        mysqli_stmt_store_result($dup_stmt);
        $dup_rows_count = mysqli_stmt_num_rows($dup_stmt);
        mysqli_stmt_close($dup_stmt);

        if ($fname === '') {
            echo "<script>alert('Full name is required')</script>";
        } elseif ($username === '' || strlen($username) < 3) {
            echo "<script>alert('Username must be at least 3 characters')</script>";
        } elseif ($rows_count > 0) {
            echo "<script>alert('Artisan already exists')</script>";
        } elseif ($dup_rows_count > 0) {
            echo "<script>alert('An artisan with this ID number or KRA pin is already registered')</script>";
        } elseif ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo "<script>alert('A valid email address is required')</script>";
        } elseif ($phone_no === '') {
            echo "<script>alert('Phone number is required')</script>";
        } elseif ($location === '') {
            echo "<script>alert('Location is required')</script>";
        } elseif ($id_number === '') {
            echo "<script>alert('National ID or Passport number is required')</script>";
        } elseif ($kra_no === '') {
            echo "<script>alert('KRA pin number is required')</script>";
        } elseif ($bname === '') {
            echo "<script>alert('Business/shop name is required')</script>";
        } elseif ($bank_no === '') {
            echo "<script>alert('Bank account number is required')</script>";
        } elseif (strlen($password) < 8) {
            echo "<script>alert('Password must be at least 8 characters')</script>";
        } elseif ($password != $confirm_password) {
            echo "<script>alert('Password do not match')</script>";
        } elseif (!isset($_FILES['business_certificate']) || $_FILES['business_certificate']['error'] !== UPLOAD_ERR_OK) {
            echo "<script>alert('Business Registration Certificate is required')</script>";
        } elseif ($_FILES['business_certificate']['size'] > $max_file_size) {
            echo "<script>alert('Business Registration Certificate must be 2MB or smaller')</script>";
        } elseif (!in_array($cert_mime, $allowed_mimes)) {
            echo "<script>alert('Business Registration Certificate must be a JPG, PNG, or WEBP image')</script>";
        } elseif (!isset($_FILES['business_permit']) || $_FILES['business_permit']['error'] !== UPLOAD_ERR_OK) {
            echo "<script>alert('Business Permit is required')</script>";
        } elseif ($_FILES['business_permit']['size'] > $max_file_size) {
            echo "<script>alert('Business Permit must be 2MB or smaller')</script>";
        } elseif (!in_array($permit_mime, $allowed_mimes)) {
            echo "<script>alert('Business Permit must be a JPG, PNG, or WEBP image')</script>";
        } elseif (!isset($_FILES['business_kebs']) || $_FILES['business_kebs']['error'] !== UPLOAD_ERR_OK) {
            echo "<script>alert('KEBS Certificate is required')</script>";
        } elseif ($_FILES['business_kebs']['size'] > $max_file_size) {
            echo "<script>alert('KEBS Certificate must be 2MB or smaller')</script>";
        } elseif (!in_array($kebs_mime, $allowed_mimes)) {
            echo "<script>alert('KEBS Certificate must be a JPG, PNG, or WEBP image')</script>";
        } else {
            // ---- Move files, keeping original filenames (sanitized) ----
            $business_certificate = preg_replace('/[^A-Za-z0-9._-]/', '_', basename($_FILES['business_certificate']['name']));
            $business_permit      = preg_replace('/[^A-Za-z0-9._-]/', '_', basename($_FILES['business_permit']['name']));
            $business_kebs        = preg_replace('/[^A-Za-z0-9._-]/', '_', basename($_FILES['business_kebs']['name']));

            move_uploaded_file($_FILES['business_certificate']['tmp_name'], "./images/$business_certificate");
            move_uploaded_file($_FILES['business_permit']['tmp_name'], "./images/$business_permit");
            move_uploaded_file($_FILES['business_kebs']['tmp_name'], "./images/$business_kebs");

            $artisan_ip = getIPAddress();
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            //Query to insert artisan into database
            $artisan_query = "insert into artisans(full_name,username,email,phone_number,password,location,business_name,artisan_ip,id_number,kra,business_certificate,business_permit,kebs,bank_no)
            values(?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
            $artisan_stmt = mysqli_prepare($conn, $artisan_query);
            mysqli_stmt_bind_param(
                $artisan_stmt,
                "ssssssssssssss",
                $fname, $username, $email, $phone_no, $hashed_password, $location, $bname,
                $artisan_ip, $id_number, $kra_no, $business_certificate, $business_permit, $business_kebs, $bank_no
            );

            if (mysqli_stmt_execute($artisan_stmt)) {
                echo "<script>alert('Registration submitted successfully.Login later, must approve your account!'); window.location.href='../index.php';</script>";
            } else {
                echo "<script>alert('Registration failed. Please try again.')</script>";
            }
            mysqli_stmt_close($artisan_stmt);
        }
    }
?>