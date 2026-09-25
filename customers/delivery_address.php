<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include('../database_connection.php');
include('../functions/sharedfunctions.php');

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}
//csrf generated
csrf_token();
$username = $_SESSION['username'];
$customer_stmt = mysqli_prepare($conn, "select customer_id from customers where username = ?");
mysqli_stmt_bind_param($customer_stmt, "s", $username);
mysqli_stmt_execute($customer_stmt);
$row = mysqli_fetch_assoc(mysqli_stmt_get_result($customer_stmt));
mysqli_stmt_close($customer_stmt);
if (!$row) {
    die("Customer not found");
}
$customer_id = $row['customer_id'];
//empty array to collect validation errors
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $full_name = trim($_POST['full_name'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');
    $county    = trim($_POST['county'] ?? '');
    $town      = trim($_POST['town'] ?? '');
    $address   = trim($_POST['address'] ?? '');
    $notes     = trim($_POST['notes'] ?? '');

    if ($full_name === '') {
        $errors[] = "Full name is required.";
    }
    if (!preg_match('/^(?:254|0)(7|1)\d{8}$/', $phone)) {
        $errors[] = "Enter a valid M-Pesa phone number (e.g. 0712345678).";
    }
    if ($county === '') {
        $errors[] = "County is required.";
    }
    if ($address === '') {
        $errors[] = "Delivery address details are required.";
    }
//if no errors proceed.
    if (empty($errors)) {
        //stores submitted data in a session
        $_SESSION['delivery'] = [
            'full_name' => $full_name,
            'phone'     => $phone,
            'county'    => $county,
            'town'      => $town,
            'address'   => $address,
            'notes'     => $notes,
        ];
        header("Location: mpesa_payment.php?customer_id=" . (int) $customer_id);
        exit;
    }
}

$saved = $_SESSION['delivery'] ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Address</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="../style.css">
    <style>
        body{background: #E8F0F3}
        .manage{ display: flex; background: white; align-items: center; justify-content: center; padding: 15px; }
        .row123{ display: flex; padding: 15px; align-items: center; list-style-type: none; gap: 20px; margin-top: 10px; margin-bottom: 10px; background-color: #f8f9fa; }
        .row123 li{ display : inline; }
        .row123 a{ text-decoration: none; color: black; padding: 10px 15px; border-radius: 4px; }
        .row123 a:hover{ background-color: #35c7c9; color:#1c2b3a; font-weight: 600; }
        .btn-artisan{ display: inline-block; padding: 12px 24px; font-size: 16px; font-weight: 600; text-align: center; text-decoration: none; border: none; cursor: pointer; color: white; background-color:#35c7c9; border-radius:4px; }
        .profile_title{ background-color: #35c7c9; }
        .delivery-form{ max-width: 600px; margin: 20px auto; background:white; padding:24px; border-radius:6px; }
        .delivery-form .form-group{ margin-bottom:16px; }
        .delivery-form label{ display:block; font-weight:600; margin-bottom:6px; font-size:0.9rem; }
        .delivery-form input, .delivery-form textarea{ width:100%; padding:10px; border:1px solid #dee2e6; border-radius:4px; font-family:inherit; box-sizing:border-box; }
        .delivery-form .form-row{ display:flex; gap:14px; }
        .delivery-form .form-row .form-group{ flex:1; }
        .error-box{ background:#fdeceb; border:1px solid #f3c6c2; color:#c1443a; padding:12px 16px; border-radius:6px; margin-bottom:16px; font-size:0.88rem; }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div class="logo"><img src="../logo1.svg" alt="logo"/></div>
        </header>

        <!--<div class="row123">
            <li class="nav-item"><a class="nav-link" href="./profile.php">Welcome <?php echo htmlspecialchars($username); ?></a></li>
            <li class="nav-item"><a class="nav-link" href="./logout.php">logout</a></li>
        </div>-->
    </div>

    <div class="container">
        <h2 class="profile_title" style="text-align:center; padding:10px;">Delivery Address</h2>
        <!--shows error messages to the customer-->
        <?php if (!empty($errors)): ?>
            <div class="error-box" style="max-width:480px;margin:16px auto 0;">
                <?php foreach ($errors as $error) echo htmlspecialchars($error) . '<br>'; ?>
            </div>
        <?php endif; ?>

        <form class="delivery-form" method="POST" action="delivery_address.php">
            <?php csrf_field(); ?>
            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($saved['full_name'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="phone">Phone Number (M-Pesa)</label>
                <input type="tel" id="phone" name="phone" placeholder="07XXXXXXXX" value="<?php echo htmlspecialchars($saved['phone'] ?? ''); ?>" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="county">County</label>
                    <input type="text" id="county" name="county" value="<?php echo htmlspecialchars($saved['county'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="town">Town / Estate</label>
                    <input type="text" id="town" name="town" value="<?php echo htmlspecialchars($saved['town'] ?? ''); ?>">
                </div>
            </div>
            <div class="form-group">
                <label for="address">Delivery Address Details</label>
                <textarea id="address" name="address" rows="3" required><?php echo htmlspecialchars($saved['address'] ?? ''); ?></textarea>
            </div>
           
            <button type="submit" class="btn-artisan" style="width:100%;">Continue to Payment</button>
        </form>
    </div>

    <footer>
        <div class="footerdiv">
            <div>
                <h4>Contact us</h4>
                <p>Email: Samweltindi07@gmail.com<br>Phone: 0742086326</p>
            </div>
            <div>
                <h4>Follow us</h4>
                <span><i class="fa-brands fa-square-facebook"></i> | <i class="fa-brands fa-x-twitter"></i> | <i class="fa-brands fa-whatsapp"></i> | <i class="fa-brands fa-instagram"></i></span>
            </div>
        </div>
        <p class="footerP">Copyright &copy; 2026 ArtisanOrders, All rights reserved.</p>
    </footer>
</body>
</html>