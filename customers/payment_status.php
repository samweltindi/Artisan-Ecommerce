<?php
session_start();
include('../database_connection.php');

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];
$customer_stmt = mysqli_prepare($conn, "select customer_id from customers where username = ?");
mysqli_stmt_bind_param($customer_stmt, "s", $username);
mysqli_stmt_execute($customer_stmt);
$customer_row = mysqli_fetch_assoc(mysqli_stmt_get_result($customer_stmt));
mysqli_stmt_close($customer_stmt);
if (!$customer_row) {
    header("Location: logout.php");
    exit;
}
$customer_id = $customer_row['customer_id'];

$invoice_number = $_GET['invoice_number'] ?? '';

if ($invoice_number === '' || !ctype_digit((string)$invoice_number)) {
    //true only if invoice_number only contains digit numbers
    header("Location: profile.php");
    exit;
}

// Confirm this invoice actually belongs to the logged-in customer 
$owner_check = mysqli_prepare($conn, "SELECT invoice_number FROM orders WHERE invoice_number = ? AND customer_id = ?");
mysqli_stmt_bind_param($owner_check, "ii", $invoice_number, $customer_id);
mysqli_stmt_execute($owner_check);
$owns_invoice = mysqli_fetch_assoc(mysqli_stmt_get_result($owner_check));
mysqli_stmt_close($owner_check);
if (!$owns_invoice) {
    header("Location: profile.php");
    exit;
}

// This endpoint is called by the page's own JS to poll for status
if (isset($_GET['check'])) {
    //gets the latest transactions
    $stmt = mysqli_prepare($conn, "SELECT status FROM mpesa_transactions WHERE invoice_number = ? ORDER BY id DESC LIMIT 1");
    mysqli_stmt_bind_param($stmt, "i", $invoice_number);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
//tells the browse this is json
    header('Content-Type: application/json');
    //falls back to pending if their is no status
    echo json_encode(['status' => $row['status'] ?? 'pending']);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Status</title>
    <link rel="stylesheet" href="../style.css">
    <style>
         body{background: #E8F0F3}
        .status-box{ max-width:480px; margin:60px auto; background:#f8f9fa; padding:32px; border-radius:6px; text-align:center; }
        .spinner{ width:40px; height:40px; border:4px solid #dee2e6; border-top-color:#35c7c9; border-radius:50%; margin:0 auto 20px; animation:spin 1s linear infinite; }
        @keyframes spin{ to{ transform:rotate(360deg); } }
        .status-ok{ color:#1a7f37; font-weight:700; }
        .status-fail{ color:#c1443a; font-weight:700; }
        .btn-artisan{ display:inline-block; margin-top:20px; padding:12px 24px; font-size:16px; font-weight:600; text-decoration:none; border:none; cursor:pointer; color:white; background-color:#35c7c9; border-radius:4px; }
    </style>
</head>
<body>
    <div class="container">
        <header><div class="logo"><img src="../logo1.svg" alt="logo"/></div></header>
    </div>

    <div class="status-box" id="statusBox">
        <div class="spinner" id="spinner"></div>
        <p id="statusText">Waiting for M-Pesa confirmation… Enter your PIN on your phone.</p>
    </div>

    <script>
        const invoiceNumber = <?php echo json_encode($invoice_number); ?>;
        let attempts = 0;
        const maxAttempts = 30; // ~90s at 3s intervals

        async function poll() {
            attempts++;
            try {
                const res = await fetch('payment_status.php?check=1&invoice_number=' + encodeURIComponent(invoiceNumber));
                const data = await res.json();

                if (data.status === 'completed') {
                    document.getElementById('spinner').style.display = 'none';
                    document.getElementById('statusText').innerHTML =
                        '<span class="status-ok">Payment received!</span><br>Your order has been placed.<br>' +
                        '<a class="btn-artisan" href="profile.php">View my orders</a>';
                    return;
                }
                if (data.status === 'failed') {
                    document.getElementById('spinner').style.display = 'none';
                    document.getElementById('statusText').innerHTML =
                        '<span class="status-fail">Payment was not completed.</span><br>You can try again from your cart.<br>' +
                        '<a class="btn-artisan" href="../cart.php">Back to cart</a>';
                    return;
                }
            } catch (e) {
                // network hiccup — just retry
            }

            if (attempts < maxAttempts) {
                setTimeout(poll, 3000);
            } else {
                document.getElementById('spinner').style.display = 'none';
                document.getElementById('statusText').innerHTML =
                    'Still waiting on confirmation. If you completed payment on your phone, check <a href="profile.php">your orders</a> shortly.';
            }
        }

        poll();
    </script>
</body>
</html>