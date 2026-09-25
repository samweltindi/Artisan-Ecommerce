<?php
//session_start();
//include('../database_connection.php');
//include('../functions/sharedfunctions.php');

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}
//csrf generates token
csrf_token();
//obtains customer_id
$username = $_SESSION['username'];
$stmt = mysqli_prepare($conn, "SELECT customer_id FROM customers WHERE username = ?");
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$row) {
    die("Customer not found.");
}
$customer_id = $row['customer_id'];
//gets invoice number
$invoice_number = isset($_GET['invoice_number']) ? (int) $_GET['invoice_number'] : 0;
if (!$invoice_number) {
    header("Location: profile.php?my_orders");
    exit;
}

// Only the customer who placed this order can confirm it, and only once
// it's actually been shipped — an order can't be "delivered" before that.
$check = mysqli_prepare($conn, "SELECT order_status, delivery_confirmed_at FROM orders WHERE invoice_number = ? AND customer_id = ?");
mysqli_stmt_bind_param($check, "ii", $invoice_number, $customer_id);
mysqli_stmt_execute($check);
$order = mysqli_fetch_assoc(mysqli_stmt_get_result($check));
mysqli_stmt_close($check);

if (!$order) {
    die("Order not found.");
}

if ($order['order_status'] !== 'complete') {
    echo "<script>alert('This order has not been paid yet.')</script>";
    echo "<script>window.open('profile.php?my_orders','_self')</script>";
    exit;
}

if (!empty($order['delivery_confirmed_at'])) {
    echo "<script>alert('Delivery already confirmed for this order.')</script>";
    echo "<script>window.open('profile.php?my_orders','_self')</script>";
    exit;
}



//if delivery confirmed by customer it triggers payment to artisan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])) {
    //checks and verifies token
   csrf_verify();
    $update = mysqli_prepare($conn, "UPDATE orders SET delivery_confirmed_at = NOW()
                                      WHERE invoice_number = ? AND customer_id = ?");
    mysqli_stmt_bind_param($update, "ii", $invoice_number, $customer_id);
    mysqli_stmt_execute($update);
    mysqli_stmt_close($update);

    echo "<script>alert('Thanks! Delivery confirmed.')</script>";
    echo "<script>window.open('profile.php?my_orders','_self')</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Delivery</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .confirm-box{ max-width:480px; margin:60px auto; background:#f8f9fa; padding:32px; border-radius:6px; text-align:center; }
        .btn-artisan{ display:inline-block; margin-top:20px; padding:12px 24px; font-size:16px; font-weight:600; text-decoration:none; border:none; cursor:pointer; color:white; background-color:#35c7c9; border-radius:4px; }
    </style>
</head>
<body>
    <div class="container">
        <header><div class="logo"><img src="../logo1.svg" alt="logo"/></div></header>
    </div>

    <div class="confirm-box">
        <p>Have you received order #<?php echo (int) $invoice_number; ?>?</p>
        <p style="font-size:0.85rem;color:#5a6b82;">Confirming releases payment to the artisan. Only confirm once the items are actually in hand.</p>
        <form method="POST">
            <?php csrf_field();?>
            <button type="submit" name="confirm" class="btn-artisan">Yes, confirm delivery</button>
        </form>
    </div>
</body>
</html>